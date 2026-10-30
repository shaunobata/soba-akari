<?php
/**
 * お知らせ一覧（P-06、基本設計書3-7）。1ページ10件。カテゴリ別一覧（category.php）もこの形で出す。
 */

defined( 'ABSPATH' ) || exit;

get_header();

$akari_news_id = (int) get_option( 'page_for_posts' );
$akari_current = is_category() ? get_queried_object_id() : 0;
$akari_cats    = get_terms(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => false,
		'orderby'    => 'term_id',
	)
);

get_template_part(
	'template-parts/page-header',
	null,
	array(
		'title' => is_category() ? single_cat_title( '', false ) . 'のお知らせ' : get_the_title( $akari_news_id ),
		'image' => 0,
	)
);
?>
<div class="container container--narrow page-body">
	<nav class="cat-tabs" aria-label="カテゴリの切り替え">
		<ul>
			<li><a href="<?php echo esc_url( get_permalink( $akari_news_id ) ); ?>"<?php echo $akari_current ? '' : ' aria-current="page"'; ?>>すべて</a></li>
			<?php foreach ( $akari_cats as $akari_cat ) : ?>
				<li><a href="<?php echo esc_url( get_category_link( $akari_cat ) ); ?>"<?php echo $akari_current === $akari_cat->term_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $akari_cat->name ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<?php if ( have_posts() ) : ?>
		<?php
		global $wp_query;
		get_template_part(
			'template-parts/news-list',
			null,
			array(
				'posts' => $wp_query->posts,
				'thumb' => true,
			)
		);
		the_posts_pagination(
			array(
				'prev_text'          => '前へ',
				'next_text'          => '次へ',
				'screen_reader_text' => 'ページ送り',
				'aria_label'         => 'お知らせのページ送り',
				'mid_size'           => 1,
			)
		);
		?>
	<?php else : ?>
		<p class="empty-text">現在お知らせはありません。</p>
	<?php endif; ?>
</div>
<?php
get_footer();
