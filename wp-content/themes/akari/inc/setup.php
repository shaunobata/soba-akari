<?php
/**
 * テーマの基本設定、CSS・JSの読み込み、表側の不要な出力の停止、使わないページの転送（詳細設計書2.2・9.6）。
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'script', 'style', 'navigation-widgets' ) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/editor.css' );
		remove_theme_support( 'core-block-patterns' );
	}
);

/** ファイルの更新日時をバージョンにする（ブラウザに1年キャッシュさせるため、変更したら名前が変わるように）。 */
function akari_asset_version( string $path ): string {
	$file = get_template_directory() . '/' . $path;
	return file_exists( $file ) ? (string) filemtime( $file ) : '1';
}

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'akari', get_template_directory_uri() . '/assets/css/main.css', array(), akari_asset_version( 'assets/css/main.css' ) );
		wp_enqueue_script(
			'akari',
			get_template_directory_uri() . '/assets/js/main.js',
			array(),
			akari_asset_version( 'assets/js/main.js' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		// 使わないブロックのCSS・グローバルスタイルを外す（固定ページで使ったブロックの分だけは本体が読み込む）。
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
		wp_dequeue_style( 'wp-block-library-theme' );
	},
	20
);
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

// 絵文字のスクリプト、oEmbed、REST・RSSの案内など、表側に出す不要なものを止める。
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'emoji_svg_url', '__return_false' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );

/**
 * 使わないページ（サイト内検索・日付・タグ・添付ファイル）はトップへ301で転送する。
 * 作成者アーカイブは akari-core/security.php で転送している。
 */
add_action(
	'template_redirect',
	function () {
		if ( is_search() || is_date() || is_tag() || is_attachment() || is_feed() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
		// 廃止した宴会・貸切のページ（/party/）は、トップの宴会セクションへ
		if ( is_404() && 'party' === trim( (string) wp_parse_url( (string) $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			wp_safe_redirect( home_url( '/#party' ), 301 );
			exit;
		}
	}
);

// /review/ では固定バーを出さない。body に目印を付け、CSSで固定バー分の余白も外す。
// /night/ はページ全体を夜の地にする目印を付ける。
add_filter(
	'body_class',
	function ( array $classes ) {
		if ( akari_has_fixed_bar() ) {
			$classes[] = 'has-fixed-bar';
		}
		if ( is_page( 'night' ) ) {
			$classes[] = 'is-night-page';
		}
		return $classes;
	}
);

function akari_has_fixed_bar(): bool {
	return ! is_page( 'review' ) && ( akari_store( 'tel' ) || akari_store( 'reserve_url' ) );
}

/**
 * head に出すもの：見出しのWebフォント（A案のファイルがあるときだけ）、メインビジュアルの先読み。
 */
add_action(
	'wp_head',
	function () {
		$font = 'assets/fonts/heading-subset.woff2';
		if ( file_exists( get_template_directory() . '/' . $font ) ) {
			$url = get_template_directory_uri() . '/' . $font . '?ver=' . akari_asset_version( $font );
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $url ) );
			printf(
				'<style>@font-face{font-family:"Akari Heading";src:url("%s") format("woff2");font-weight:600;font-display:swap}</style>' . "\n",
				esc_url( $url )
			);
		}

		if ( is_front_page() ) {
			$mv = akari_main_visual_ids();
			if ( $mv['sp'] ) {
				printf(
					'<link rel="preload" as="image" media="(max-width: 767px)" imagesrcset="%s" imagesizes="100vw" fetchpriority="high">' . "\n",
					esc_attr( (string) wp_get_attachment_image_srcset( $mv['sp'], 'full' ) )
				);
			}
			if ( $mv['pc'] ) {
				printf(
					'<link rel="preload" as="image" media="(min-width: 768px)" imagesrcset="%s" imagesizes="100vw" fetchpriority="high">' . "\n",
					esc_attr( (string) wp_get_attachment_image_srcset( $mv['pc'], 'full' ) )
				);
			}
		}
	},
	2
);
