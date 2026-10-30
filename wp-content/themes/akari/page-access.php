<?php
/**
 * 店舗情報・アクセス（P-05、基本設計書3-6）。地図を埋め込むのはこのページだけ（F-07）。
 * 宴会・貸切のページは廃止し（2026-10-31 店主依頼）、席・貸切・よくある質問はこのページの #party に出す。
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

	<?php
	// 宴会・貸切のご相談（旧 宴会・貸切ページの内容。トップの「席・貸切の詳しいご案内」の行き先）
	$akari_layout_img = (int) akari_field( 'akari_layout_img' );
	$akari_seat_img   = (int) akari_field( 'akari_seat_img' );
	$akari_guide      = akari_rows( 'akari_seat_guide', 'people' );
	$akari_private    = (string) akari_field( 'akari_private_text' );
	$akari_faq        = array_filter( akari_rows( 'akari_faq', 'q' ), fn( $row ) => ! empty( $row['a'] ) );
	?>
	<section class="section party-info" aria-labelledby="party">
		<div class="container">
			<h2 class="section-title" id="party" tabindex="-1">宴会・貸切のご相談</h2>
			<div class="container--narrow">
				<?php get_template_part( 'template-parts/party-summary' ); ?>
			</div>

			<?php if ( $akari_layout_img || $akari_seat_img ) : ?>
				<div class="seat-media">
					<?php if ( $akari_layout_img ) : ?>
						<figure class="seat-media__map">
							<?php echo akari_img( $akari_layout_img, '(min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<figcaption>席の配置</figcaption>
						</figure>
					<?php endif; ?>
					<?php if ( $akari_seat_img ) : ?>
						<figure class="seat-media__photo ratio-4-3">
							<?php echo akari_img( $akari_seat_img, '(min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</figure>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="container--narrow">
				<h3 class="party-sub">人数別のおすすめ席</h3>
				<?php if ( $akari_guide ) : ?>
					<table class="simple-table">
						<caption class="screen-reader-text">人数別のおすすめ席</caption>
						<thead><tr><th scope="col">人数</th><th scope="col">ご用意する席</th></tr></thead>
						<tbody>
							<?php foreach ( $akari_guide as $akari_row ) : ?>
								<tr><th scope="row"><?php echo esc_html( $akari_row['people'] ); ?></th><td><?php echo esc_html( $akari_row['seats'] ); ?></td></tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p>人数に合わせた席の組み合わせは、お電話でご相談ください。</p>
				<?php endif; ?>

				<h3 class="party-sub">貸切について</h3>
				<?php echo $akari_private ? akari_text( $akari_private ) : '<p>' . esc_html( akari_store( 'private_hire' ) ? akari_store( 'private_hire' ) : 'お電話でご相談ください。' ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput ?>

				<?php if ( $akari_faq ) : ?>
					<h3 class="party-sub">よくある質問</h3>
					<div class="faq-list">
						<?php foreach ( $akari_faq as $akari_row ) : ?>
							<details class="faq" open data-faq>
								<summary><span class="faq__q"><?php echo esc_html( $akari_row['q'] ); ?></span></summary>
								<div class="faq__a"><?php echo akari_text( $akari_row['a'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
							</details>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<div class="section section--surface">
		<div class="container container--narrow">
			<?php get_template_part( 'template-parts/reserve-guide' ); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
