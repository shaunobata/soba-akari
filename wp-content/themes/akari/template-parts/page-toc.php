<?php
/**
 * お品書きのページ内目次（U-16、F-15）。品のない区分へのリンクは出さない。
 * 引数 sections：[ id => 表示名 ]
 */

defined( 'ABSPATH' ) || exit;

$akari_sections = $args['sections'] ?? array();
if ( ! $akari_sections ) {
	return;
}
?>
<nav class="page-toc" aria-label="お品書きの目次">
	<ul>
		<?php foreach ( $akari_sections as $akari_id => $akari_label ) : ?>
			<li><a href="#<?php echo esc_attr( $akari_id ); ?>"><?php echo esc_html( $akari_label ); ?></a></li>
		<?php endforeach; ?>
	</ul>
</nav>
