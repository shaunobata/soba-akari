<?php
/**
 * 内部アクセス判定用のCookie（詳細設計書7.5）。
 * 店主とKOKUBOの端末で /?internal=1 を1回開くと、サーバー側で akari_internal=1（1年）を書き込む。
 * JSで書くCookieはiPhoneのSafariで7日で消えるため、サーバー側で書く。GTMの変数 ck_internal が読む。
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'template_redirect',
	function () {
		if ( ! isset( $_GET['internal'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$on = '1' === $_GET['internal']; // phpcs:ignore WordPress.Security.NonceVerification
		setcookie(
			'akari_internal',
			$on ? '1' : '',
			array(
				'expires'  => $on ? time() + YEAR_IN_SECONDS : time() - HOUR_IN_SECONDS,
				'path'     => '/',
				'secure'   => is_ssl(),
				'httponly' => false, // GTMから読むため
				'samesite' => 'Lax',
			)
		);
		nocache_headers();
		wp_safe_redirect( remove_query_arg( 'internal' ), 302 );
		exit;
	},
	1
);
