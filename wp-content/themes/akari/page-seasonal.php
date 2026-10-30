<?php
/**
 * 季節のおすすめ（2026-10-31 店主依頼で新設）。
 * 今月の月替わり（投稿タイプ「月替わり」）と、掲載期間中の季節限定（投稿タイプ「季節限定」）を出す。
 * 月替わりが0件なら「準備中」、季節限定が0件ならそのセクションは出さない。
 */

defined( 'ABSPATH' ) || exit;

get_header();

$akari_monthly  = akari_get_monthly();
$akari_seasonal = akari_get_seasonal();
$akari_menu     = akari_page_url( 'menu' );

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-header' );
	?>
	<div class="container page-body">
		<div class="entry-content lead container--narrow">
			<?php the_content(); ?>
		</div>

		<section class="menu-section" aria-labelledby="monthly">
			<h2 class="section-title" id="monthly" tabindex="-1">今月の月替わり</h2>
			<?php
			get_template_part(
				'template-parts/monthly-list',
				null,
				array(
					'mode'  => 'menu',
					'items' => $akari_monthly,
				)
			);
			?>
		</section>

		<?php if ( $akari_seasonal ) : ?>
			<section class="menu-section" aria-labelledby="seasonal">
				<h2 class="section-title" id="seasonal" tabindex="-1">季節限定</h2>
				<?php get_template_part( 'template-parts/seasonal-list', null, array( 'items' => $akari_seasonal ) ); ?>
			</section>
		<?php endif; ?>

		<?php if ( $akari_menu ) : ?>
			<aside class="closing-box">
				<p>いつもの蕎麦・つまみ・お酒は、お品書きをご覧ください。</p>
				<?php echo akari_more_link( $akari_menu, 'お品書きを見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</aside>
		<?php endif; ?>
	</div>
	<?php
endwhile;

get_footer();
