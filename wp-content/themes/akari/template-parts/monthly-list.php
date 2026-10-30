<?php
/**
 * 今月の月替わり（U-10、F-04）。表示するかは日本時間の当月で判定する（3.7）。
 * 引数 mode：top（最新1件。0件なら何も出さない）／menu（当月の全件。0件なら「準備中」）、items：取得済みの品（省略可）
 * 写真がなければ文字だけの体裁にする（仮の画像は使わない）。
 */

defined( 'ABSPATH' ) || exit;

$akari_mode  = ( $args['mode'] ?? 'menu' ) === 'top' ? 'top' : 'menu';
$akari_items = $args['items'] ?? akari_get_monthly( 'top' === $akari_mode ? 1 : -1 );

if ( ! $akari_items ) {
	if ( 'menu' === $akari_mode ) {
		echo '<p class="empty-text">今月の月替わりは準備中です。</p>';
	}
	return;
}
$akari_month_label = (int) wp_date( 'n' ) . '月の月替わり';
?>
<ul class="monthly-list monthly-list--<?php echo esc_attr( $akari_mode ); ?>">
	<?php foreach ( $akari_items as $akari_item ) : ?>
		<?php $akari_thumb = (int) get_post_thumbnail_id( $akari_item ); ?>
		<li class="monthly-card<?php echo $akari_thumb ? '' : ' is-text-only'; ?>">
			<?php if ( $akari_thumb ) : ?>
				<div class="monthly-card__img ratio-4-3">
					<?php echo akari_img( $akari_thumb, '(min-width: 1024px) 520px, (min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			<?php endif; ?>
			<div class="monthly-card__body">
				<p class="monthly-card__label"><?php echo esc_html( $akari_month_label ); ?> <?php echo akari_serve_label( $akari_item->ID ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<h3 class="monthly-card__name"><?php echo esc_html( get_the_title( $akari_item ) ); ?></h3>
				<p class="monthly-card__price price"><?php echo esc_html( akari_price_text( get_post_meta( $akari_item->ID, 'akari_price', true ) ) ); ?></p>
				<?php if ( get_post_meta( $akari_item->ID, 'akari_desc', true ) ) : ?>
					<p class="monthly-card__desc"><?php echo nl2br( esc_html( get_post_meta( $akari_item->ID, 'akari_desc', true ) ) ); ?></p>
				<?php endif; ?>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
