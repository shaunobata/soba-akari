<?php
/**
 * 共通ブロック「ご予約の方法」（U-17、C-02）。トップ・宴会・店舗情報に置く。
 */

defined( 'ABSPATH' ) || exit;

// 引数 level：見出しの階層（トップではセクション9の中に置くため3）
$akari_args  = wp_parse_args(
	$args ?? array(),
	array(
		'tel_label' => '電話する',
		'level'     => 2,
	)
);
$akari_level = 3 === (int) $akari_args['level'] ? 'h3' : 'h2';
?>
<section class="reserve-guide" aria-labelledby="reserve-guide-title">
	<<?php echo $akari_level; // phpcs:ignore WordPress.Security.EscapeOutput ?> class="reserve-guide__title" id="reserve-guide-title">ご予約の方法</<?php echo $akari_level; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<dl class="reserve-guide__list">
		<div>
			<dt>少人数のお席</dt>
			<dd><?php echo akari_store( 'reserve_url' ) ? 'ネット予約、またはお電話で承ります。' : 'お電話で承ります。'; ?></dd>
		</div>
		<div>
			<dt>宴会・貸切</dt>
			<dd>お電話でご相談ください。</dd>
		</div>
	</dl>
	<?php
	get_template_part(
		'template-parts/cta-buttons',
		null,
		array(
			'location'  => 'body',
			'tel_label' => $akari_args['tel_label'],
		)
	);
	?>
</section>
