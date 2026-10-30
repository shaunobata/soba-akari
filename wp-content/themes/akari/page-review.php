<?php
/**
 * 口コミのお願い（P-08、F-10）。店内QRからだけ開く。noindex・サイトマップ外・ナビに出さない。
 * 満足度で行き先を分けたり、特典と引き換えにしたりしない（Googleのポリシーに沿う）。
 * 口コミ投稿に集中してもらうため、固定バーとパンくずは出さない。
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="container container--narrow page-body review-page">
		<h1 class="page-title"><?php the_title(); ?></h1>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
		<?php if ( akari_store( 'review_url' ) ) : ?>
			<p class="review-page__cta">
				<a class="btn btn--primary btn--block" href="<?php echo esc_url( akari_store( 'review_url' ) ); ?>" target="_blank" rel="noopener" data-track="review" data-location="body">
					<span class="btn__label">Googleマップで感想を書く</span><?php echo akari_icon( 'external' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="screen-reader-text">（新しいタブで開きます）</span>
				</a>
			</p>
			<p class="note">ボタンを押すと、Googleマップの口コミの投稿画面が開きます。Googleアカウントでのログインが必要です。</p>
		<?php endif; ?>
		<p class="review-page__back"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( akari_store( 'name' ) ); ?>のトップへ</a></p>
	</div>
	<?php
endwhile;

get_footer();
