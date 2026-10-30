<?php
/**
 * こだわり（P-07、基本設計書3-8）。
 * 見出し（蕎麦を打つ手元の写真）→ 蕎麦粉 → 昼の蕎麦・夜の締めの蕎麦 → 店主の想い → 締め。
 * 店主の写真は顔出しの可否（D9）で決める。決まるまでは手元の写真。文の入っていないセクションは出さない。
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-header' );

	$akari_flour_text = (string) akari_field( 'akari_flour_text' );
	$akari_flour_img  = (int) akari_field( 'akari_flour_img' );
	$akari_daynight   = akari_rows( 'akari_daynight', 'title' );
	$akari_owner_text = (string) akari_field( 'akari_owner_text' );
	$akari_owner_img  = (int) akari_field( 'akari_owner_img' );
	?>
	<div class="container container--narrow page-body">
		<div class="entry-content lead">
			<?php the_content(); ?>
		</div>
	</div>

	<?php if ( $akari_flour_text || $akari_flour_img ) : ?>
		<section class="section section--surface" aria-labelledby="about-flour">
			<div class="container split">
				<?php if ( $akari_flour_img ) : ?>
					<div class="split__media ratio-4-3">
						<?php echo akari_img( $akari_flour_img, '(min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				<?php endif; ?>
				<div class="split__body">
					<h2 class="section-title" id="about-flour">常陸秋そばを、石臼で挽いて</h2>
					<?php echo akari_text( $akari_flour_text ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $akari_daynight ) : ?>
		<section class="section" aria-labelledby="about-daynight">
			<div class="container">
				<h2 class="section-title" id="about-daynight">昼の一枚、夜の締めの一枚</h2>
				<div class="style-list">
					<?php foreach ( $akari_daynight as $akari_row ) : ?>
						<article class="style-card">
							<?php if ( ! empty( $akari_row['image'] ) ) : ?>
								<div class="style-card__img ratio-4-3">
									<?php echo akari_img( (int) $akari_row['image'], '(min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
							<?php endif; ?>
							<h3 class="style-card__title"><?php echo esc_html( $akari_row['title'] ); ?></h3>
							<?php echo akari_text( (string) ( $akari_row['text'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $akari_owner_text ) : ?>
		<section class="section section--night" aria-labelledby="about-owner">
			<div class="container split split--reverse">
				<?php if ( $akari_owner_img ) : ?>
					<div class="split__media ratio-4-3">
						<?php echo akari_img( $akari_owner_img, '(min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				<?php endif; ?>
				<div class="split__body">
					<h2 class="section-title" id="about-owner">店主の想い</h2>
					<?php echo akari_text( $akari_owner_text ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<div class="section">
		<div class="container container--narrow">
			<aside class="closing-box">
				<p>昼は蕎麦を一枚。夜は呑んだあとの締めに。どちらの時間も、手打ちの蕎麦をご用意してお待ちしています。</p>
				<?php echo akari_more_link( akari_page_url( 'menu' ), 'お品書きを見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php echo akari_more_link( akari_page_url( 'night' ), '夜の楽しみ方を見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</aside>
		</div>
	</div>
	<?php
endwhile;

get_footer();
