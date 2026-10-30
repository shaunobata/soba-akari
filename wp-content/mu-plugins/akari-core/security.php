<?php
/**
 * セキュリティの設定（詳細設計書10.1）。
 * XML-RPC・アプリケーションパスワードの停止、ユーザー名の露出防止、コメントの停止、
 * バージョン表示の削除、HTTPのセキュリティヘッダー。
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_is_application_passwords_available', '__return_false' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );

// バージョン表示を出さない。
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// 作成者アーカイブ（?author=）はトップへ転送する。
add_action(
	'template_redirect',
	function () {
		if ( is_author() || isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	2
);

// 未ログインには REST の /wp/v2/users を返さない。
add_filter(
	'rest_endpoints',
	function ( array $endpoints ) {
		if ( ! is_user_logged_in() ) {
			unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}
		return $endpoints;
	}
);

// oEmbed の作成者情報も出さない。
add_filter(
	'oembed_response_data',
	function ( array $data ) {
		unset( $data['author_name'], $data['author_url'] );
		return $data;
	}
);

/* コメント・ピンバックを全体で止める */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 20 );
add_filter(
	'xmlrpc_methods',
	function ( array $methods ) {
		unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
		return $methods;
	}
);
add_action(
	'init',
	function () {
		foreach ( get_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	},
	100
);
add_action(
	'admin_menu',
	function () {
		remove_menu_page( 'edit-comments.php' );
		remove_submenu_page( 'options-general.php', 'options-discussion.php' );
	}
);
add_action(
	'admin_bar_menu',
	fn( WP_Admin_Bar $bar ) => $bar->remove_node( 'comments' ),
	999
);

// HTTPのセキュリティヘッダー。HSTSは公開後1週間、転送が正しく動くことを確かめてからサーバー側で足す。
add_action(
	'send_headers',
	function () {
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	}
);
