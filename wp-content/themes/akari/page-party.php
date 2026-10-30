<?php
/**
 * 宴会・貸切・会食（P-04、基本設計書3-5）。フォームは置かない（D2）。
 * 「個室」という語は見出し・本文・FAQのどこにも使わない。
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-header' );
	?>
	<div class="container container--narrow page-body">
		<div class="entry-content lead">
			<?php the_content(); ?>
		</div>
		<?php get_template_part( 'template-parts/party-summary' ); ?>
	</div>

	<?php
	$akari_layout_img = (int) akari_field( 'akari_layout_img' );
	$akari_seat_img   = (int) akari_field( 'akari_seat_img' );
	$akari_guide      = akari_rows( 'akari_seat_guide', 'people' );
	?>
	<section class="section" aria-labelledby="party-seats">
		<div class="container">
			<h2 class="section-title" id="party-seats">席の配置と人数別のおすすめ</h2>
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
			<?php if ( akari_store( 'seats_layout' ) ) : ?>
				<p>全<?php echo esc_html( (string) akari_store( 'seats_total' ) ); ?>席：<?php echo esc_html( akari_store( 'seats_layout' ) ); ?></p>
			<?php endif; ?>
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
		</div>
	</section>

	<section class="section section--night" aria-labelledby="party-course">
		<div class="container">
			<h2 class="section-title" id="party-course">宴会コース</h2>
			<?php get_template_part( 'template-parts/course-cards', null, array( 'type' => 'party' ) ); ?>
			<?php if ( akari_page_url( 'menu' ) ) : ?>
				<p class="section-more"><?php echo akari_more_link( akari_page_url( 'menu' ), '単品はお品書きへ', 'more-link--on-night' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
	</section>

	<?php $akari_private = (string) akari_field( 'akari_private_text' ); ?>
	<section class="section" aria-labelledby="party-private">
		<div class="container container--narrow">
			<h2 class="section-title" id="party-private">貸切について</h2>
			<?php echo $akari_private ? akari_text( $akari_private ) : '<p>' . esc_html( akari_store( 'private_hire' ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php
			get_template_part(
				'template-parts/cta-buttons',
				null,
				array(
					'tel_label' => '電話で相談する',
					'reserve'   => false,
				)
			);
			?>
		</div>
	</section>

	<section class="section section--surface" aria-labelledby="party-flow">
		<div class="container">
			<h2 class="section-title" id="party-flow">ご予約・ご相談の流れ</h2>
			<ol class="flow">
				<li class="flow__step">
					<h3 class="flow__title">お電話でご相談</h3>
					<p>ご希望の日程・人数・ご予算をお聞かせください。</p>
				</li>
				<li class="flow__step">
					<h3 class="flow__title">コース・席のご案内</h3>
					<p>人数に合わせて、コースと席の組み合わせをご案内します。</p>
				</li>
				<li class="flow__step">
					<h3 class="flow__title">人数の確定</h3>
					<p>ご来店の前に、人数を確定してお知らせください。</p>
				</li>
			</ol>
			<?php
			get_template_part(
				'template-parts/cta-buttons',
				null,
				array(
					'tel_label' => '電話で相談する',
					'reserve'   => false,
				)
			);
			?>
		</div>
	</section>

	<?php $akari_faq = array_filter( akari_rows( 'akari_faq', 'q' ), fn( $row ) => ! empty( $row['a'] ) ); ?>
	<?php if ( $akari_faq ) : ?>
		<section class="section" aria-labelledby="party-faq">
			<div class="container container--narrow">
				<h2 class="section-title" id="party-faq">よくある質問</h2>
				<div class="faq-list">
					<?php foreach ( $akari_faq as $akari_row ) : ?>
						<details class="faq" open data-faq>
							<summary><h3 class="faq__q"><?php echo esc_html( $akari_row['q'] ); ?></h3></summary>
							<div class="faq__a"><?php echo akari_text( $akari_row['a'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<div class="section">
		<div class="container container--narrow">
			<?php get_template_part( 'template-parts/reserve-guide', null, array( 'tel_label' => '電話で相談する' ) ); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
