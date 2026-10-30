<?php
/**
 * 店舗情報・アクセス（P-05、基本設計書3-6）。地図を埋め込むのはこのページだけ（F-07）。
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-header' );
	?>
	<div class="container page-body">
		<div class="entry-content lead container--narrow">
			<?php the_content(); ?>
		</div>
		<div class="access-grid">
			<section aria-labelledby="access-info">
				<h2 class="section-title" id="access-info">店舗情報</h2>
				<?php get_template_part( 'template-parts/store-table' ); ?>
			</section>
			<section aria-labelledby="access-map">
				<h2 class="section-title" id="access-map">地図</h2>
				<?php get_template_part( 'template-parts/map-embed' ); ?>
			</section>
		</div>
	</div>

	<?php $akari_route = array_filter( (array) akari_field( 'akari_route' ), fn( $row ) => ! empty( $row['text'] ) || ! empty( $row['image'] ) ); ?>
	<?php if ( $akari_route ) : ?>
		<section class="section section--surface" aria-labelledby="route">
			<div class="container">
				<h2 class="section-title" id="route" tabindex="-1">三鷹駅北口からの道順</h2>
				<ol class="route-steps">
					<?php foreach ( $akari_route as $akari_row ) : ?>
						<li class="route-step">
							<?php if ( ! empty( $akari_row['image'] ) ) : ?>
								<div class="route-step__img ratio-4-3">
									<?php echo akari_img( (int) $akari_row['image'], '(min-width: 768px) 33vw, 100vw', array( 'size' => 'akari-w800' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
							<?php endif; ?>
							<?php echo akari_text( (string) ( $akari_row['text'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</li>
					<?php endforeach; ?>
				</ol>
				<?php echo akari_route_link( 'body' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</section>
	<?php endif; ?>

	<div class="section">
		<div class="container container--narrow">
			<?php get_template_part( 'template-parts/reserve-guide' ); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
