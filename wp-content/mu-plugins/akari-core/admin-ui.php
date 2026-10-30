<?php
/**
 * 店主の管理画面（詳細設計書4.2・4.4）。
 * 入口を「かんたん更新」の1つにし、左メニューは
 * かんたん更新・月替わり・お知らせ・臨時休業のお知らせ・プロフィールの5つだけにする。
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	function () {
		add_menu_page( 'かんたん更新', 'かんたん更新', 'edit_akari_notice', 'akari-home', 'akari_home_page', 'dashicons-admin-home', 2 );

		if ( ! akari_is_owner() ) {
			return;
		}
		foreach ( array( 'index.php', 'upload.php', 'edit-comments.php', 'tools.php' ) as $page ) {
			remove_menu_page( $page );
		}
	},
	99
);

function akari_home_page(): void {
	$month_count = count(
		get_posts(
			array(
				'post_type'      => 'akari_monthly',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'key'   => 'akari_month',
						'value' => wp_date( 'Y-m' ),
					),
				),
			)
		)
	);
	$notice_on = akari_notice_visible();
	$notice    = akari_notice();
	?>
	<div class="wrap akari-admin akari-home">
		<h1>かんたん更新</h1>
		<div class="akari-home__buttons">
			<a class="akari-home__button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=akari_monthly' ) ); ?>">
				<span class="dashicons dashicons-carrot" aria-hidden="true"></span>今月の月替わりを追加する
			</a>
			<a class="akari-home__button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=akari_seasonal' ) ); ?>">
				<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>季節限定を追加する
			</a>
			<a class="akari-home__button" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">
				<span class="dashicons dashicons-edit" aria-hidden="true"></span>お知らせを書く
			</a>
			<a class="akari-home__button" href="<?php echo esc_url( admin_url( 'admin.php?page=akari-notice' ) ); ?>">
				<span class="dashicons dashicons-megaphone" aria-hidden="true"></span>臨時休業のお知らせ
				<span class="akari-home__state <?php echo $notice_on ? 'is-on' : ''; ?>"><?php echo $notice_on ? 'オン' : 'オフ'; ?></span>
			</a>
		</div>
		<p class="akari-home__status">
			<?php echo esc_html( sprintf( '%d月の月替わり：%d件掲載中', (int) wp_date( 'n' ), $month_count ) ); ?>
			<br><?php echo esc_html( sprintf( '季節限定：%d件掲載中', count( akari_get_seasonal() ) ) ); ?>
			<?php if ( $notice_on ) : ?>
				<br><?php echo esc_html( sprintf( '臨時休業の帯：%sまで表示', wp_date( 'n月j日', strtotime( $notice['end_date'] ) ) ) ); ?>
			<?php endif; ?>
		</p>
		<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=akari_monthly' ) ); ?>">月替わりの一覧</a>　<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=akari_seasonal' ) ); ?>">季節限定の一覧</a>　<a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">お知らせの一覧</a>　<a href="<?php echo esc_url( home_url( '/' ) ); ?>">サイトを見る</a></p>
	</div>
	<?php
}

// ログインした後の行き先：店主は「かんたん更新」へ。
add_filter(
	'login_redirect',
	function ( $redirect_to, $requested, $user ) {
		if ( $user instanceof WP_User && in_array( 'akari_owner', (array) $user->roles, true ) ) {
			return admin_url( 'admin.php?page=akari-home' );
		}
		return $redirect_to;
	},
	10,
	3
);

// 店主がログアウトした後はサイトのトップへ。
add_filter(
	'logout_redirect',
	function ( $redirect_to, $requested, $user ) {
		if ( $user instanceof WP_User && in_array( 'akari_owner', (array) $user->roles, true ) ) {
			return home_url( '/' );
		}
		return $redirect_to;
	},
	10,
	3
);

// 店主がダッシュボード・メディアの一覧を開いたら「かんたん更新」へ。
add_action(
	'admin_init',
	function () {
		if ( ! akari_is_owner() || wp_doing_ajax() ) {
			return;
		}
		global $pagenow;
		if ( in_array( $pagenow, array( 'index.php', 'upload.php', 'edit-comments.php', 'tools.php' ), true ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=akari-home' ) );
			exit;
		}

		// 2段階認証を設定するまで、プロフィール以外の画面に進めない（Two Factor が有効なときだけ）。
		if ( class_exists( 'Two_Factor_Core' ) && ! Two_Factor_Core::is_user_using_two_factor( get_current_user_id() ) && 'profile.php' !== $pagenow ) {
			wp_safe_redirect( admin_url( 'profile.php?akari_2fa=1#two-factor-options' ) );
			exit;
		}
	}
);

add_action(
	'admin_notices',
	function () {
		if ( akari_is_owner() && isset( $_GET['akari_2fa'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-warning"><p>はじめに、下の「2段階認証」を設定してください。設定すると、ほかの画面に進めます。</p></div>';
		}
	}
);

// 店主のメディアの一覧は、自分が上げた写真だけにする。
add_filter(
	'ajax_query_attachments_args',
	function ( array $query ) {
		if ( akari_is_owner() ) {
			$query['author'] = get_current_user_id();
		}
		return $query;
	}
);

// 編集画面の不要な枠を外し、入力欄だけにする。
add_action(
	'add_meta_boxes',
	function () {
		if ( ! akari_is_owner() ) {
			return;
		}
		foreach ( array( 'post', 'akari_monthly', 'akari_seasonal' ) as $post_type ) {
			foreach ( array( 'postexcerpt', 'slugdiv', 'authordiv', 'commentsdiv', 'commentstatusdiv', 'trackbacksdiv', 'postcustom', 'tagsdiv-post_tag', 'formatdiv', 'revisionsdiv' ) as $box ) {
				remove_meta_box( $box, $post_type, 'normal' );
				remove_meta_box( $box, $post_type, 'side' );
				remove_meta_box( $box, $post_type, 'advanced' );
			}
		}
	},
	99
);

add_filter( 'screen_options_show_screen', fn( $show ) => akari_is_owner() ? false : $show );
add_action(
	'admin_head',
	function () {
		if ( akari_is_owner() ) {
			get_current_screen()?->remove_help_tabs();
		}
	}
);

// 管理バーの迷う入口を外す。
add_action(
	'admin_bar_menu',
	function ( WP_Admin_Bar $bar ) {
		if ( ! akari_is_owner() ) {
			return;
		}
		foreach ( array( 'wp-logo', 'comments', 'new-page', 'new-media', 'new-akari_menu', 'new-akari_course', 'customize', 'updates', 'search' ) as $node ) {
			$bar->remove_node( $node );
		}
	},
	999
);

// 更新のお知らせ・各種通知は店主には出さない（更新はKOKUBOが行う）。
add_action(
	'admin_head',
	function () {
		if ( akari_is_owner() ) {
			remove_all_actions( 'admin_notices_update' );
			remove_action( 'admin_notices', 'update_nag', 3 );
			remove_action( 'admin_notices', 'maintenance_nag', 10 );
		}
	},
	1
);

// 店主の画面の文言を分かりやすくする。
add_filter(
	'enter_title_here',
	function ( string $text, WP_Post $post ) {
		$map = array(
			'akari_monthly'  => '品名（例：牡蠣の天ぷら）',
			'akari_seasonal' => '品名（例：鴨せいろ）',
			'akari_menu'    => '品名',
			'akari_course'  => 'コース名',
			'post'          => 'タイトル（40字以内）',
		);
		return $map[ $post->post_type ] ?? $text;
	},
	10,
	2
);
