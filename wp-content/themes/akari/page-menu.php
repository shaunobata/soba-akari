<?php
/**
 * お品書き（P-02、基本設計書3-3）。
 * 品目は投稿タイプ「お品書き」から区分ごとに出し、今月の月替わりを自動で差し込む。
 * 更新日はお品書き・月替わりの最終更新日から自動で出す。
 */

defined( 'ABSPATH' ) || exit;

get_header();

$akari_sections = array();
$akari_items    = array();
foreach ( akari_choices( 'menu_cat' ) as $akari_cat => $akari_label ) {
	$akari_items[ $akari_cat ] = akari_get_menu_items( $akari_cat );
	if ( $akari_items[ $akari_cat ] ) {
		$akari_sections[ $akari_cat ] = $akari_label;
	}
}
$akari_sections['monthly'] = '今月の月替わり';

// 更新日：お品書き・月替わり・このページのうち、いちばん新しい更新
$akari_updated = get_post_modified_time( 'U', true );
foreach ( array( 'akari_menu', 'akari_monthly' ) as $akari_pt ) {
	$akari_latest = get_posts(
		array(
			'post_type'      => $akari_pt,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);
	if ( $akari_latest ) {
		$akari_updated = max( $akari_updated, (int) get_post_modified_time( 'U', true, $akari_latest[0] ) );
	}
}

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-header' );
	?>
	<div class="container container--narrow page-body">
		<div class="entry-content lead">
			<?php the_content(); ?>
		</div>
		<div class="menu-notes">
			<p class="menu-notes__tax">価格はすべて税込です</p>
			<?php if ( akari_store( 'otoshi' ) ) : ?>
				<p>お通し・席料：<?php echo esc_html( akari_store( 'otoshi' ) ); ?></p>
			<?php endif; ?>
			<p class="menu-notes__updated"><time datetime="<?php echo esc_attr( wp_date( 'Y-m-d', $akari_updated ) ); ?>"><?php echo esc_html( wp_date( 'Y年n月j日', $akari_updated ) ); ?></time>更新</p>
		</div>

		<?php get_template_part( 'template-parts/page-toc', null, array( 'sections' => $akari_sections ) ); ?>

		<?php foreach ( $akari_items as $akari_cat => $akari_cat_items ) : ?>
			<?php
			get_template_part(
				'template-parts/menu-table',
				null,
				array(
					'cat'   => $akari_cat,
					'items' => $akari_cat_items,
				)
			);
			?>
		<?php endforeach; ?>

		<section class="menu-section" aria-labelledby="monthly">
			<h2 class="section-title" id="monthly" tabindex="-1">今月の月替わり</h2>
			<?php get_template_part( 'template-parts/monthly-list', null, array( 'mode' => 'menu' ) ); ?>
			<?php if ( akari_page_url( 'seasonal' ) ) : ?>
				<p class="section-more"><?php echo akari_more_link( akari_page_url( 'seasonal' ), '季節限定の品も、季節のおすすめで見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</section>

		<?php if ( akari_page_url( 'night' ) ) : ?>
			<aside class="closing-box">
				<p>夜は、品目を組み合わせたコースもご用意しています。</p>
				<?php echo akari_more_link( akari_page_url( 'night' ), '夜の楽しみ方とコースを見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</aside>
		<?php endif; ?>
	</div>
	<?php
endwhile;

get_footer();
