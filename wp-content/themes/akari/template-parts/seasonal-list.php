<?php
/**
 * 季節限定の一覧（季節のおすすめページ）。月替わりと同じカードの体裁で、掲載期間の終わりがあれば「〇月まで」と出す。
 * 引数 items：取得済みの品（省略時は掲載期間中のもの）
 * 写真がなければ文字だけの体裁にする（仮の画像は使わない）。
 */

defined( 'ABSPATH' ) || exit;

$akari_items = $args['items'] ?? akari_get_seasonal();
if ( ! $akari_items ) {
	return;
}
?>
<ul class="monthly-list monthly-list--menu">
	<?php foreach ( $akari_items as $akari_item ) : ?>
		<?php
		$akari_thumb = (int) get_post_thumbnail_id( $akari_item );
		$akari_to    = (string) get_post_meta( $akari_item->ID, 'akari_season_to', true );
		$akari_label = '季節限定' . ( $akari_to ? '（' . (int) substr( $akari_to, 5 ) . '月まで）' : '' );
		?>
		<li class="monthly-card<?php echo $akari_thumb ? '' : ' is-text-only'; ?>">
			<?php if ( $akari_thumb ) : ?>
				<div class="monthly-card__img ratio-4-3">
					<?php echo akari_img( $akari_thumb, '(min-width: 1024px) 520px, (min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			<?php endif; ?>
			<div class="monthly-card__body">
				<p class="monthly-card__label"><?php echo esc_html( $akari_label ); ?> <?php echo akari_serve_label( $akari_item->ID ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<h3 class="monthly-card__name"><?php echo esc_html( get_the_title( $akari_item ) ); ?></h3>
				<p class="monthly-card__price price"><?php echo esc_html( akari_price_text( get_post_meta( $akari_item->ID, 'akari_price', true ) ) ); ?></p>
				<?php if ( get_post_meta( $akari_item->ID, 'akari_desc', true ) ) : ?>
					<p class="monthly-card__desc"><?php echo nl2br( esc_html( get_post_meta( $akari_item->ID, 'akari_desc', true ) ) ); ?></p>
				<?php endif; ?>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
