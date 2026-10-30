<?php
/**
 * お品書きの区分ごとの表（U-09）。品名と税込価格は文字で出し、画像にしない。
 * 引数 cat：soba／otsumami／sake、items：その区分の品（取得済み）
 */

defined( 'ABSPATH' ) || exit;

$akari_cat   = $args['cat'] ?? '';
$akari_items = $args['items'] ?? array();
$akari_label = akari_choices( 'menu_cat' )[ $akari_cat ] ?? '';
if ( ! $akari_items || ! $akari_label ) {
	return;
}

// 区分の頭に、写真のある主な品を1枚だけ出す。
$akari_photo = 0;
foreach ( $akari_items as $akari_item ) {
	if ( has_post_thumbnail( $akari_item ) ) {
		$akari_photo = (int) get_post_thumbnail_id( $akari_item );
		break;
	}
}
?>
<section class="menu-section" aria-labelledby="<?php echo esc_attr( $akari_cat ); ?>">
	<h2 class="section-title" id="<?php echo esc_attr( $akari_cat ); ?>" tabindex="-1"><?php echo esc_html( $akari_label ); ?></h2>
	<?php if ( $akari_photo ) : ?>
		<div class="menu-section__photo ratio-4-3">
			<?php echo akari_img( $akari_photo, '(min-width: 1024px) 400px, (min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	<?php endif; ?>
	<?php if ( 'sake' === $akari_cat && akari_field( 'akari_sake_note' ) ) : ?>
		<p class="note"><?php echo esc_html( akari_field( 'akari_sake_note' ) ); ?></p>
	<?php endif; ?>
	<table class="menu-table">
		<caption class="screen-reader-text"><?php echo esc_html( $akari_label ); ?></caption>
		<tbody>
			<?php foreach ( $akari_items as $akari_item ) : ?>
				<tr>
					<th scope="row">
						<span class="menu-table__name"><?php echo esc_html( get_the_title( $akari_item ) ); ?></span>
						<?php echo akari_serve_label( $akari_item->ID ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php if ( get_post_meta( $akari_item->ID, 'akari_desc', true ) ) : ?>
							<span class="menu-table__desc"><?php echo esc_html( get_post_meta( $akari_item->ID, 'akari_desc', true ) ); ?></span>
						<?php endif; ?>
					</th>
					<td class="price"><?php echo esc_html( number_format( (int) get_post_meta( $akari_item->ID, 'akari_price', true ) ) ); ?><span class="price__unit">円</span></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php if ( 'otsumami' === $akari_cat && akari_page_url( 'night' ) ) : ?>
		<p class="section-more"><?php echo akari_more_link( akari_page_url( 'night' ), '夜のコースと楽しみ方を見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
	<?php endif; ?>
</section>
