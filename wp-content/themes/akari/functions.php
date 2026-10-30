<?php
/**
 * akari テーマ。
 * 表示だけを受け持ち、データの定義（投稿タイプ・項目・権限・共通設定・構造化データ）は mu-plugin「akari-core」にある。
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'akari_store' ) ) {
	// mu-plugin がない状態では表示できないため、管理画面に知らせて止める。
	add_action(
		'admin_notices',
		fn() => print '<div class="notice notice-error"><p>akari テーマには mu-plugin「akari-core」が必要です。</p></div>'
	);
	return;
}

require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/analytics.php';
