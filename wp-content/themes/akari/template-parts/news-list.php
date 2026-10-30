<?php
/**
 * お知らせの一覧（U-11、F-05）。リンクはタイトルだけ。
 * 引数 posts：表示する投稿、thumb：写真（小）を出すか
 */

defined( 'ABSPATH' ) || exit;

$akari_posts = $args['posts'] ?? array();
$akari_thumb = ! empty( $args['thumb'] );
if ( ! $akari_posts ) {
	return;
}
?>
<ul class="news-list">
	<?php foreach ( $akari_posts as $akari_post ) : ?>
		<?php $akari_cat = get_the_category( $akari_post->ID ); ?>
		<li class="news-item<?php echo ( $akari_thumb && has_post_thumbnail( $akari_post ) ) ? ' has-thumb' : ''; ?>">
			<?php if ( $akari_thumb && has_post_thumbnail( $akari_post ) ) : ?>
				<div class="news-item__thumb ratio-1-1">
					<?php echo akari_img( (int) get_post_thumbnail_id( $akari_post ), '96px', array( 'size' => 'akari-w400' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			<?php endif; ?>
			<div class="news-item__body">
				<p class="news-item__meta">
					<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $akari_post ) ); ?>"><?php echo esc_html( get_the_date( 'Y.n.j', $akari_post ) ); ?></time>
					<?php if ( $akari_cat ) : ?>
						<span class="cat-label"><?php echo esc_html( $akari_cat[0]->name ); ?></span>
					<?php endif; ?>
				</p>
				<a class="news-item__title" href="<?php echo esc_url( get_permalink( $akari_post ) ); ?>"><?php echo esc_html( get_the_title( $akari_post ) ); ?></a>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
