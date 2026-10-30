<?php
/**
 * 電話ボタンとネット予約ボタンの組（U-05・U-06、F-01・F-02）。
 * 電話ボタンのすぐ下に受付時間、本文用ではネット予約の補足を出す。
 *
 * 引数：location（header／fixed_bar／body／footer）、tel_label、course（data-course）、reserve（ネット予約を出すか）
 */

defined( 'ABSPATH' ) || exit;

$akari_args = wp_parse_args(
	$args ?? array(),
	array(
		'location'  => 'body',
		'tel_label' => '電話する',
		'course'    => '',
		'reserve'   => true,
	)
);

$akari_tel     = akari_tel_button(
	array(
		'location' => $akari_args['location'],
		'label'    => $akari_args['tel_label'],
	)
);
$akari_reserve = $akari_args['reserve'] ? akari_reserve_button(
	array(
		'location' => $akari_args['location'],
		'course'   => $akari_args['course'],
	)
) : '';

if ( ! $akari_tel && ! $akari_reserve ) {
	return;
}
?>
<div class="cta">
	<?php if ( $akari_tel ) : ?>
		<div class="cta__item">
			<?php echo $akari_tel; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( akari_tel_note() ) : ?>
				<p class="cta-note"><?php echo esc_html( akari_tel_note() ); ?></p>
			<?php endif; ?>
			<?php if ( $akari_reserve ) : ?>
				<p class="cta-note">受付時間外はネット予約をご利用ください</p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php if ( $akari_reserve ) : ?>
		<div class="cta__item">
			<?php echo $akari_reserve; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<p class="cta-note">食べログの予約ページが開きます</p>
		</div>
	<?php endif; ?>
</div>
