<?php
/**
 * お知らせ詳細（P-06、基本設計書3-7）。
 * 店主が入力するタイトルにはWebフォントを使わない（.title-dynamic）。
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$akari_cat = get_the_category();
	get_template_part( 'template-parts/breadcrumb' );
	?>
	<article class="container container--narrow page-body post-article">
		<header class="post-article__header">
			<p class="news-item__meta">
				<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'Y.n.j' ) ); ?></time>
				<?php if ( $akari_cat ) : ?>
					<a class="cat-label" href="<?php echo esc_url( get_category_link( $akari_cat[0] ) ); ?>"><?php echo esc_html( $akari_cat[0]->name ); ?></a>
				<?php endif; ?>
			</p>
			<h1 class="page-title title-dynamic"><?php the_title(); ?></h1>
		</header>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="post-article__img">
				<?php echo akari_img( (int) get_post_thumbnail_id(), '(min-width: 768px) 680px, 100vw', array( 'lazy' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		<?php endif; ?>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>

		<nav class="post-nav" aria-label="前後のお知らせ">
			<?php
			$akari_prev = get_previous_post();
			$akari_next = get_next_post();
			?>
			<?php if ( $akari_prev ) : ?>
				<a class="post-nav__prev" href="<?php echo esc_url( get_permalink( $akari_prev ) ); ?>"><span>前のお知らせ</span><?php echo esc_html( get_the_title( $akari_prev ) ); ?></a>
			<?php endif; ?>
			<?php if ( $akari_next ) : ?>
				<a class="post-nav__next" href="<?php echo esc_url( get_permalink( $akari_next ) ); ?>"><span>次のお知らせ</span><?php echo esc_html( get_the_title( $akari_next ) ); ?></a>
			<?php endif; ?>
		</nav>
		<p class="section-more"><?php echo akari_more_link( get_permalink( (int) get_option( 'page_for_posts' ) ), '一覧へ戻る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>

		<?php if ( akari_page_url( 'night' ) ) : ?>
			<aside class="closing-box closing-box--night">
				<p>燈りの夜は、天ぷらや蕎麦前と日本酒で。締めは手打ちの蕎麦をどうぞ。</p>
				<?php echo akari_more_link( akari_page_url( 'night' ), '夜の楽しみ方を見る', 'more-link--on-night' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</aside>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
