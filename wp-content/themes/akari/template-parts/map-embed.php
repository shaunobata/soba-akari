<?php
/**
 * 地図（F-07、詳細設計書9.7）。店舗情報ページだけに置く。
 * 比率を固定した枠に住所と「Googleマップで開く」を先に出し、画面に近づいたら main.js が iframe を差し込む。
 * JSが動かない環境ではリンクだけが働く。
 */

defined( 'ABSPATH' ) || exit;

$akari_embed = (string) akari_store( 'map_embed' );
if ( ! $akari_embed && ! akari_store( 'map_url' ) ) {
	return;
}
?>
<div class="map">
	<div class="map__frame ratio-map"<?php echo $akari_embed ? ' data-map="' . esc_url( $akari_embed ) . '"' : ''; ?>>
		<div class="map__placeholder">
			<p><?php echo esc_html( akari_address( false ) ); ?></p>
			<p class="map__error" hidden>地図を読み込めませんでした。</p>
			<?php echo akari_route_link( 'body', 'Googleマップで開く' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
	<?php echo akari_route_link( 'body' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
