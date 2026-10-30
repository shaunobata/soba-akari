<?php
/**
 * お客様の声（U-14、D9）。店主と選んだ口コミを原文のまま、3件まで。
 * 投稿者名と★の数値は出さない。構造化データ（Review）は付けない。
 * 引数 voices：引用文の入った行
 */

defined( 'ABSPATH' ) || exit;

$akari_voices = array_slice( $args['voices'] ?? array(), 0, 3 );
if ( ! $akari_voices ) {
	return;
}
?>
<ul class="voice-list">
	<?php foreach ( $akari_voices as $akari_voice ) : ?>
		<?php
		$akari_source = $akari_voice['source'] ? $akari_voice['source'] : 'Google';
		$akari_month  = $akari_voice['month'] ? '（' . (int) substr( $akari_voice['month'], 0, 4 ) . '年' . (int) substr( $akari_voice['month'], 5 ) . '月）' : '';
		?>
		<li>
			<figure class="voice">
				<span class="voice__mark" aria-hidden="true">“</span>
				<blockquote class="voice__quote"><?php echo akari_text( $akari_voice['quote'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></blockquote>
				<figcaption class="voice__source"><?php echo esc_html( $akari_source . 'の口コミより' . $akari_month ); ?></figcaption>
			</figure>
		</li>
	<?php endforeach; ?>
</ul>
