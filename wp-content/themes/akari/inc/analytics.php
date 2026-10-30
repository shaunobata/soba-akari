<?php
/**
 * 計測の出力（詳細設計書7.2）。テーマが出すのは dataLayer とGTMのスニペットだけ。
 * イベント（tel_click など）はボタンの data-track・data-location をGTM側で拾う。
 */

defined( 'ABSPATH' ) || exit;

/** page_type：top／menu／night／party／access／news／about／review／privacy／404 */
function akari_page_type(): string {
	if ( is_404() ) {
		return '404';
	}
	if ( is_front_page() ) {
		return 'top';
	}
	if ( is_home() || is_single() || is_category() ) {
		return 'news';
	}
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( in_array( $slug, array( 'menu', 'night', 'party', 'access', 'about', 'review', 'privacy' ), true ) ) {
			return $slug;
		}
	}
	return 'other';
}

add_action(
	'wp_head',
	function () {
		$gtm = (string) akari_store( 'gtm_id' );
		$dl  = array(
			'page_type' => akari_page_type(),
			'logged_in' => is_user_logged_in(),
		);
		echo '<script>window.dataLayer=window.dataLayer||[];dataLayer.push(' . wp_json_encode( $dl ) . ");</script>\n";
		if ( ! preg_match( '/^GTM-[A-Z0-9]+$/', $gtm ) ) {
			return;
		}
		?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo esc_js( $gtm ); ?>');</script>
		<?php
	},
	3
);

add_action(
	'wp_body_open',
	function () {
		$gtm = (string) akari_store( 'gtm_id' );
		if ( preg_match( '/^GTM-[A-Z0-9]+$/', $gtm ) ) {
			printf(
				'<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n",
				esc_attr( $gtm )
			);
		}
	}
);
