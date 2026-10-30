<?php
/**
 * 夜の楽しみ方・コース（P-03、基本設計書3-4）。
 * コースは投稿タイプ「コース」（種別：夜）から出す。コース一覧の枠（#course-list）の表示を course_view として計測する。
 */

defined( 'ABSPATH' ) || exit;

get_header();

$akari_menu = akari_page_url( 'menu' );

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-header' );
	?>
	<div class="container container--narrow page-body">
		<div class="entry-content lead">
			<?php the_content(); ?>
		</div>
	</div>

	<section class="section section--night" aria-labelledby="night-course">
		<div class="container">
			<h2 class="section-title" id="night-course">夜のコース</h2>
			<?php get_template_part( 'template-parts/course-cards', null, array( 'type' => 'night' ) ); ?>
			<?php if ( $akari_menu ) : ?>
				<p class="section-more"><?php echo akari_more_link( $akari_menu . '#otsumami', '単品はお品書きへ', 'more-link--on-night' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="section" aria-labelledby="night-style">
		<div class="container">
			<h2 class="section-title" id="night-style">楽しみ方の例</h2>
			<div class="style-list">
				<?php
				$akari_styles = array(
					'akari_solo'   => 'お一人で呑むなら',
					'akari_couple' => 'ご夫婦で楽しむなら',
				);
				?>
				<?php foreach ( $akari_styles as $akari_key => $akari_heading ) : ?>
					<article class="style-card">
						<h3 class="style-card__title"><?php echo esc_html( $akari_heading ); ?></h3>
						<?php echo akari_text( (string) akari_field( $akari_key ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php if ( $akari_menu ) : ?>
							<p><?php echo akari_more_link( $akari_menu, 'お品書きを見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php
	$akari_sake_text = (string) akari_field( 'akari_sake_text' );
	$akari_sake_img  = (int) akari_field( 'akari_sake_img' );
	?>
	<?php if ( $akari_sake_text || $akari_sake_img ) : ?>
		<section class="section section--surface" aria-labelledby="night-sake">
			<div class="container split">
				<?php if ( $akari_sake_img ) : ?>
					<div class="split__media ratio-4-3">
						<?php echo akari_img( $akari_sake_img, '(min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				<?php endif; ?>
				<div class="split__body">
					<h2 class="section-title" id="night-sake">日本酒の選び方</h2>
					<?php echo akari_text( $akari_sake_text ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php if ( $akari_menu ) : ?>
						<p class="section-more"><?php echo akari_more_link( $akari_menu . '#sake', '日本酒・蕎麦焼酎のお品書きを見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="section" aria-labelledby="night-hours">
		<div class="container container--narrow">
			<h2 class="section-title" id="night-hours">ラストオーダー・営業時間</h2>
			<dl class="info-list">
				<?php if ( akari_hours( 'dinner' ) ) : ?>
					<div><dt>夜の営業</dt><dd class="num"><?php echo esc_html( akari_hours( 'dinner' ) ); ?></dd></div>
				<?php endif; ?>
				<?php if ( akari_last_order() ) : ?>
					<div><dt>ラストオーダー</dt><dd class="num"><?php echo esc_html( akari_last_order() ); ?></dd></div>
				<?php endif; ?>
				<?php if ( akari_closed_label() ) : ?>
					<div><dt>定休日</dt><dd><?php echo esc_html( akari_closed_label() . '日' ); ?></dd></div>
				<?php endif; ?>
			</dl>
			<?php get_template_part( 'template-parts/cta-buttons', null, array( 'reserve' => false ) ); ?>
		</div>
	</section>

	<?php if ( akari_page_url( 'access' ) ) : ?>
		<section class="section section--surface" aria-labelledby="night-party">
			<div class="container container--narrow">
				<h2 class="section-title" id="night-party">会食・宴会のご案内</h2>
				<?php echo akari_text( (string) akari_field( 'akari_party_lead' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<p class="section-more"><?php echo akari_more_link( akari_page_url( 'access' ) . '#party', '宴会・貸切のご相談を見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
