<?php
/**
 * 構造化データ（F-13、詳細設計書6.1〜6.4）。
 * 共通設定の値をPHPの配列で組み立て、wp_json_encode でJSON-LDに書き出す。
 * Restaurant型は店舗情報が画面に出ているトップと /access/ だけ。下層ページにはBreadcrumbList。
 * 口コミの引用にはReview・AggregateRatingを付けない。値が空の項目は出さない。
 */

defined( 'ABSPATH' ) || exit;

/**
 * パンくずの階層。画面のパンくず（U-15）と構造化データの両方がここから作る。
 *
 * @return array<int, array{name: string, url: string}>
 */
function akari_breadcrumb_items(): array {
	// トップ・口コミのお願い（店内QR専用）・404には出さない。
	if ( is_front_page() || is_404() || is_page( 'review' ) ) {
		return array();
	}
	$items = array(
		array(
			'name' => 'トップ',
			'url'  => home_url( '/' ),
		),
	);
	$news_id = (int) get_option( 'page_for_posts' );

	if ( is_home() ) {
		$items[] = array(
			'name' => get_the_title( $news_id ),
			'url'  => get_permalink( $news_id ),
		);
	} elseif ( is_single() || is_category() ) {
		if ( $news_id ) {
			$items[] = array(
				'name' => get_the_title( $news_id ),
				'url'  => get_permalink( $news_id ),
			);
		}
		$items[] = array(
			'name' => is_single() ? single_post_title( '', false ) : single_cat_title( '', false ),
			'url'  => akari_canonical_url(),
		);
	} elseif ( is_page() ) {
		$items[] = array(
			'name' => get_the_title( get_queried_object_id() ),
			'url'  => get_permalink( get_queried_object_id() ),
		);
	} else {
		return array();
	}
	return $items;
}

/** Restaurant型の配列。値が空の項目は入れない。 */
function akari_restaurant_schema(): array {
	$site = home_url( '/' );

	$address = array_filter(
		array(
			'@type'           => 'PostalAddress',
			'postalCode'      => akari_store( 'postal_code' ),
			'addressRegion'   => '東京都',
			'addressLocality' => akari_store( 'address_locality' ),
			'streetAddress'   => akari_store( 'street_address' ),
			'addressCountry'  => 'JP',
		)
	);

	// 定休日を除く曜日について、昼と夜の2枠を書く。L.O.は入れない。
	$day_names = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
	$closed    = array_map( 'intval', (array) akari_store( 'closed_days' ) );
	$open_days = array();
	foreach ( array( 1, 2, 3, 4, 5, 6, 0 ) as $d ) { // 月曜始まり
		if ( ! in_array( $d, $closed, true ) ) {
			$open_days[] = $day_names[ $d ];
		}
	}
	$hours = array();
	foreach ( array( 'lunch', 'dinner' ) as $part ) {
		if ( akari_store( $part . '_open' ) && akari_store( $part . '_close' ) ) {
			$hours[] = array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => $open_days,
				'opens'     => akari_store( $part . '_open' ),
				'closes'    => akari_store( $part . '_close' ),
			);
		}
	}

	$data = array(
		'@context'                  => 'https://schema.org',
		'@type'                     => 'Restaurant',
		'@id'                       => $site . '#restaurant',
		'name'                      => akari_store( 'name' ),
		'url'                       => $site,
		'telephone'                 => akari_store( 'tel' ),
		'address'                   => $address,
		'servesCuisine'             => array( '蕎麦', '和食' ),
		'acceptsReservations'       => true,
		'paymentAccepted'           => akari_store( 'payment' ),
		'currenciesAccepted'        => 'JPY',
		'openingHoursSpecification' => $hours,
	);

	$menu = get_page_by_path( 'menu' );
	if ( $menu && 'publish' === $menu->post_status ) {
		$data['hasMenu'] = get_permalink( $menu );
	}
	$image = akari_store( 'default_ogp' ) ? wp_get_attachment_image_url( (int) akari_store( 'default_ogp' ), 'full' ) : '';
	if ( $image ) {
		$data['image'] = array( $image );
	}
	if ( akari_store( 'geo_lat' ) !== '' && akari_store( 'geo_lng' ) !== '' ) {
		$data['geo'] = array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => (float) akari_store( 'geo_lat' ),
			'longitude' => (float) akari_store( 'geo_lng' ),
		);
	}
	$same_as = array_values( array_filter( array( akari_store( 'instagram_url' ), akari_store( 'tabelog_url' ) ) ) );
	if ( $same_as ) {
		$data['sameAs'] = $same_as;
	}

	return array_filter( $data, fn( $v ) => '' !== $v && array() !== $v );
}

function akari_breadcrumb_schema(): array {
	$items = akari_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return array();
	}
	$list = array();
	foreach ( $items as $i => $item ) {
		$list[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => 0 === $i ? 'ホーム' : $item['name'],
			'item'     => $item['url'],
		);
	}
	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $list,
	);
}

add_action(
	'wp_head',
	function () {
		$graphs = array();
		if ( is_front_page() || is_page( 'access' ) ) {
			$graphs[] = akari_restaurant_schema();
		}
		$breadcrumb = akari_breadcrumb_schema();
		if ( $breadcrumb ) {
			$graphs[] = $breadcrumb;
		}
		foreach ( $graphs as $graph ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_UNICODE ) . "</script>\n";
		}
	},
	20
);
