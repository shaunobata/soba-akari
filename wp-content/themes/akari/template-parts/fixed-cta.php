<?php
/**
 * スマホの画面下の固定バー（U-04、F-03）。768px未満の全ページ（/review/を除く）。
 * 片方の値が未設定ならもう片方を全幅にする。bodyの下の余白はCSSで最初から取る。
 */

defined( 'ABSPATH' ) || exit;

if ( ! akari_has_fixed_bar() ) {
	return;
}
?>
<div class="fixed-cta" role="region" aria-label="電話・ネット予約">
	<?php
	echo akari_tel_button( // phpcs:ignore WordPress.Security.EscapeOutput
		array(
			'location' => 'fixed_bar',
			'number'   => false,
		)
	);
	echo akari_reserve_button( array( 'location' => 'fixed_bar' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	?>
</div>
