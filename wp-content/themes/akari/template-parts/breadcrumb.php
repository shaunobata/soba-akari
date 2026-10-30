<?php
/**
 * パンくず（U-15）。構造化データ（BreadcrumbList）は akari-core/schema.php が同じ階層データから出す。
 * 今のページはリンクにしない。区切り記号はCSSで描く。
 */

defined( 'ABSPATH' ) || exit;

$akari_items = akari_breadcrumb_items();
if ( count( $akari_items ) < 2 ) {
	return;
}
$akari_last = count( $akari_items ) - 1;
?>
<nav class="breadcrumb container" aria-label="現在の位置">
	<ol>
		<?php foreach ( $akari_items as $akari_i => $akari_item ) : ?>
			<li>
				<?php if ( $akari_i === $akari_last ) : ?>
					<span aria-current="page"><?php echo esc_html( $akari_item['name'] ); ?></span>
				<?php else : ?>
					<a href="<?php echo esc_url( $akari_item['url'] ); ?>"><?php echo esc_html( $akari_item['name'] ); ?></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
