<?php
/**
 * こだわり（P-07、任意）。素材がそろわなければ下書きのままにし、ナビ・トップのリンクも出さない。
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-header' );
	?>
	<div class="container container--narrow page-body">
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
		<aside class="closing-box">
			<p>呑んだあとは、手打ちの蕎麦で締めを。</p>
			<?php echo akari_more_link( akari_page_url( 'menu' ), 'お品書きを見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo akari_more_link( akari_page_url( 'night' ), '夜の楽しみ方を見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</aside>
	</div>
	<?php
endwhile;

get_footer();
