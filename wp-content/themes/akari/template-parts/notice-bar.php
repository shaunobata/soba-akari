<?php
/**
 * 臨時休業の帯（U-12、F-12）。オンで終了日までの間だけ、全ページのヘッダー直下に出す。
 * サーバー側で出力し、JSで後から差し込まない（CLS防止）。閉じるボタンは付けない（見落とし防止）。
 */

defined( 'ABSPATH' ) || exit;

if ( ! akari_notice_visible() ) {
	return;
}
$akari_n    = akari_notice();
$akari_link = $akari_n['post_id'] && 'publish' === get_post_status( $akari_n['post_id'] ) ? get_permalink( $akari_n['post_id'] ) : '';
?>
<div class="notice-bar" role="region" aria-label="臨時休業のお知らせ">
	<div class="notice-bar__inner container">
		<span class="notice-bar__label">臨時休業</span>
		<span class="notice-bar__text"><?php echo esc_html( $akari_n['text'] ); ?></span>
		<?php if ( $akari_link ) : ?>
			<a class="notice-bar__link" href="<?php echo esc_url( $akari_link ); ?>">詳しく見る</a>
		<?php endif; ?>
	</div>
</div>
