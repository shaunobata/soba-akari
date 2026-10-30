<?php
/**
 * 404（P-10）。ステータス404を返し、noindex。トップ・お品書き・電話へのリンクを並べる。
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container container--narrow page-body not-found">
	<h1 class="page-title">お探しのページが見つかりませんでした</h1>
	<p>ページが移動したか、URLが変わった可能性があります。お手数ですが、下のリンクからお進みください。</p>
	<ul class="not-found__links">
		<li><a class="btn btn--outline" href="<?php echo esc_url( home_url( '/' ) ); ?>">トップへ</a></li>
		<?php if ( akari_page_url( 'menu' ) ) : ?>
			<li><a class="btn btn--outline" href="<?php echo esc_url( akari_page_url( 'menu' ) ); ?>">お品書きを見る</a></li>
		<?php endif; ?>
		<?php if ( akari_store( 'tel' ) ) : ?>
			<li><?php echo akari_tel_button(); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
		<?php endif; ?>
	</ul>
</div>
<?php
get_footer();
