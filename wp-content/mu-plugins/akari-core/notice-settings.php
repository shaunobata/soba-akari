<?php
/**
 * 臨時休業の帯（F-12、詳細設計書3.5・3.7）。
 * オプション akari_notice に入れ、店主にも与える専用の権限 edit_akari_notice で編集する。
 * 表示するかはページを表示するたびに日本時間の日付で判定する（WP-Cronは使わない）。
 */

defined( 'ABSPATH' ) || exit;

function akari_notice(): array {
	return wp_parse_args(
		(array) get_option( 'akari_notice', array() ),
		array(
			'enabled'  => 0,
			'text'     => '',
			'end_date' => '',
			'post_id'  => 0,
		)
	);
}

/** 帯を出すか。オンで、終了日の当日までは出す。 */
function akari_notice_visible(): bool {
	$n = akari_notice();
	if ( empty( $n['enabled'] ) || empty( $n['end_date'] ) || '' === $n['text'] ) {
		return false;
	}
	return wp_date( 'Y-m-d' ) <= $n['end_date'];
}

add_action(
	'admin_menu',
	function () {
		add_menu_page( '臨時休業のお知らせ', '臨時休業のお知らせ', 'edit_akari_notice', 'akari-notice', 'akari_notice_page', 'dashicons-megaphone', 6 );
	}
);

function akari_notice_page(): void {
	if ( ! current_user_can( 'edit_akari_notice' ) ) {
		return;
	}
	$n       = akari_notice();
	$errors  = get_transient( 'akari_notice_errors_' . get_current_user_id() );
	$posts   = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
		)
	);
	if ( $errors ) {
		delete_transient( 'akari_notice_errors_' . get_current_user_id() );
		$n = wp_parse_args( $errors['values'], $n );
	}
	?>
	<div class="wrap akari-admin akari-notice-admin">
		<h1>臨時休業のお知らせ</h1>
		<p>オンにすると、すべてのページの上に帯が出ます。終了日を過ぎると自動で消えます。</p>
		<?php if ( $errors ) : ?>
			<div class="notice notice-error"><p><strong>保存できませんでした。</strong></p><ul style="list-style:disc;padding-left:1.5em">
				<?php foreach ( $errors['messages'] as $message ) : ?>
					<li><?php echo esc_html( $message ); ?></li>
				<?php endforeach; ?>
			</ul></div>
		<?php elseif ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success"><p><?php echo akari_notice_visible() ? '保存しました。帯を表示しています。' : '保存しました。帯は表示していません。'; ?></p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="akari-fields">
			<input type="hidden" name="action" value="akari_save_notice">
			<?php wp_nonce_field( 'akari_save_notice' ); ?>
			<div class="akari-field">
				<label class="akari-choice akari-toggle"><input type="checkbox" name="enabled" value="1" <?php checked( ! empty( $n['enabled'] ) ); ?>> 帯を表示する</label>
			</div>
			<div class="akari-field">
				<label class="akari-field__label" for="akari-notice-text">文言 <span class="akari-required">オンのとき必須</span></label>
				<input type="text" id="akari-notice-text" name="text" value="<?php echo esc_attr( $n['text'] ); ?>" class="large-text" data-max="40" placeholder="○月○日（○）は臨時休業いたします">
			</div>
			<div class="akari-field">
				<label class="akari-field__label" for="akari-notice-end">終了日 <span class="akari-required">オンのとき必須</span></label>
				<input type="date" id="akari-notice-end" name="end_date" value="<?php echo esc_attr( $n['end_date'] ? $n['end_date'] : wp_date( 'Y-m-d' ) ); ?>" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
				<p class="description">この日まで表示します。</p>
			</div>
			<div class="akari-field">
				<label class="akari-field__label" for="akari-notice-post">詳しいお知らせ（任意）</label>
				<select id="akari-notice-post" name="post_id">
					<option value="0">つなげない</option>
					<?php foreach ( $posts as $p ) : ?>
						<option value="<?php echo (int) $p->ID; ?>" <?php selected( (int) $n['post_id'], $p->ID ); ?>><?php echo esc_html( get_the_date( 'n/j', $p ) . ' ' . $p->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description">カテゴリ「休業」でお知らせを書いておくと、ここで選べます。</p>
			</div>
			<?php submit_button( '保存する', 'primary large' ); ?>
		</form>
	</div>
	<?php
}

add_action(
	'admin_post_akari_save_notice',
	function () {
		if ( ! current_user_can( 'edit_akari_notice' ) ) {
			wp_die( '権限がありません。' );
		}
		check_admin_referer( 'akari_save_notice' );

		$values = array(
			'enabled'  => empty( $_POST['enabled'] ) ? 0 : 1,
			'text'     => trim( sanitize_text_field( wp_unslash( $_POST['text'] ?? '' ) ) ),
			'end_date' => preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) ( $_POST['end_date'] ?? '' ) ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '',
			'post_id'  => absint( $_POST['post_id'] ?? 0 ),
		);
		if ( $values['post_id'] && 'publish' !== get_post_status( $values['post_id'] ) ) {
			$values['post_id'] = 0;
		}

		$messages = array();
		if ( $values['enabled'] ) {
			if ( '' === $values['text'] ) {
				$messages[] = '文言を入れてください。';
			} elseif ( mb_strlen( $values['text'] ) > 40 ) {
				$messages[] = sprintf( '文言は40字以内で入れてください（今は%d字）。', mb_strlen( $values['text'] ) );
			}
			if ( '' === $values['end_date'] ) {
				$messages[] = '終了日を入れてください。';
			} elseif ( $values['end_date'] < wp_date( 'Y-m-d' ) ) {
				$messages[] = '終了日は今日以降の日付にしてください。';
			}
		}

		if ( $messages ) {
			set_transient(
				'akari_notice_errors_' . get_current_user_id(),
				array(
					'messages' => $messages,
					'values'   => $values,
				),
				5 * MINUTE_IN_SECONDS
			);
			wp_safe_redirect( admin_url( 'admin.php?page=akari-notice' ) );
			exit;
		}

		update_option( 'akari_notice', $values );
		wp_safe_redirect( add_query_arg( 'updated', '1', admin_url( 'admin.php?page=akari-notice' ) ) );
		exit;
	}
);
