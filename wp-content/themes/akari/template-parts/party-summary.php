<?php
/**
 * 幹事向けの要点ボックス（U-13）。店舗情報ページの「宴会・貸切のご相談」（#party）の先頭。
 * 未定の項目は「ご相談ください」とし、行は消さない（幹事が聞くべき項目だと分かるように）。
 */

defined( 'ABSPATH' ) || exit;

$akari_ask     = 'ご相談ください';
$akari_courses = akari_get_courses( 'party' );
$akari_course_text = array();
$akari_drink       = '';
foreach ( $akari_courses as $akari_c ) {
	$akari_price         = akari_price_text( get_post_meta( $akari_c->ID, 'akari_price', true ) );
	$akari_course_text[] = get_the_title( $akari_c ) . ( $akari_price ? ' ' . $akari_price : '' );
	if ( ! $akari_drink && get_post_meta( $akari_c->ID, 'akari_drink', true ) ) {
		$akari_drink = (string) get_post_meta( $akari_c->ID, 'akari_drink', true );
	}
}
$akari_access_url = akari_page_url( 'access' );

$akari_items = array(
	'人数'   => akari_store( 'party_max' ) ? '最大' . akari_store( 'party_max' ) . '名様' : $akari_ask,
	'席'    => trim( ( akari_store( 'seats_total' ) ? '全' . akari_store( 'seats_total' ) . '席　' : '' ) . akari_store( 'seats_layout' ) ),
	'コース'  => $akari_course_text ? implode( '／', $akari_course_text ) : $akari_ask,
	'飲み放題' => $akari_drink ? $akari_drink : $akari_ask,
	'貸切'   => akari_store( 'private_hire' ) ? akari_store( 'private_hire' ) : $akari_ask,
	'お支払い' => akari_store( 'payment' ) ? akari_store( 'payment' ) : $akari_ask,
	'領収書'  => akari_store( 'receipt' ) ? akari_store( 'receipt' ) : $akari_ask,
	'駅から'  => akari_store( 'access' ),
	'禁煙'   => akari_store( 'smoking' ),
);
?>
<section class="party-summary" aria-labelledby="party-summary-title">
	<h3 class="party-summary__title" id="party-summary-title">幹事様へ：要点のまとめ</h3>
	<dl class="party-summary__list">
		<?php foreach ( $akari_items as $akari_term => $akari_desc ) : ?>
			<?php
			if ( '' === $akari_desc ) {
				continue;
			}
			?>
			<div>
				<dt><?php echo esc_html( $akari_term ); ?></dt>
				<dd>
					<?php if ( '駅から' === $akari_term && $akari_access_url ) : ?>
						<a href="<?php echo esc_url( $akari_access_url . '#route' ); ?>"><?php echo esc_html( $akari_desc ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $akari_desc ); ?>
					<?php endif; ?>
				</dd>
			</div>
		<?php endforeach; ?>
	</dl>
	<?php if ( akari_store( 'tel' ) ) : ?>
		<div class="party-summary__cta">
			<?php echo akari_tel_button( array( 'label' => '電話で相談する' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( akari_tel_note() ) : ?>
				<p class="cta-note"><?php echo esc_html( akari_tel_note() ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</section>
