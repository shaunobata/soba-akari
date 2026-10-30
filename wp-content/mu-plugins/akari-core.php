<?php
/**
 * Plugin Name: akari-core
 * Description: 手打そば 燈り 公式サイトのデータ定義（投稿タイプ・入力欄・権限・共通設定・構造化データ）。テーマを戻しても月替わり・設定値・権限が消えないよう、テーマから分けている。
 * Version: 1.0.0
 * Author: KOKUBO DIGITAL
 */

defined( 'ABSPATH' ) || exit;

const AKARI_CORE_VERSION = '1.0.0';

define( 'AKARI_CORE_DIR', __DIR__ . '/akari-core/' );
define( 'AKARI_CORE_URL', WPMU_PLUGIN_URL . '/akari-core/' );

foreach (
	array(
		'store-settings',   // 共通設定「店舗情報」（F-06）。他のファイルが akari_store() を使うので最初に読む
		'post-types',       // 月替わり・お品書き・コース、投稿→「お知らせ」
		'fields',           // メタボックス、入力検証
		'notice-settings',  // 臨時休業の帯（F-12）
		'role',             // 店主ロール
		'admin-ui',         // 店主の管理画面、かんたん更新
		'seo',              // title・description・OGP・noindex・サイトマップ（F-11）
		'schema',           // Restaurant・BreadcrumbList（F-13）
		'images',           // 縮小・WebP化・alt（F-14）
		'internal-traffic', // 内部アクセス判定用Cookie
		'security',         // XML-RPC・ユーザー列挙・コメント停止・ヘッダー
		'setup',            // 初回の固定ページ・設定値の作成
	) as $akari_file
) {
	require AKARI_CORE_DIR . $akari_file . '.php';
}
unset( $akari_file );
