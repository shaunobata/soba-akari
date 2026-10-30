<?php
/**
 * テンプレートで使う関数とショートコード [akari key="tel"]（F-06）。
 * 共通設定の値は akari_store()（akari-core/store-settings.php）から読む。
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------
 * ショートコード：固定ページの本文から共通設定を呼び出す
 * ------------------------------------------------------------ */

add_shortcode(
	'akari',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'key' => '' ), $atts, 'akari' );
		switch ( $atts['key'] ) {
			case 'address':
				return esc_html( akari_address() );
			case 'lunch_hours':
				return esc_html( akari_hours( 'lunch' ) );
			case 'dinner_hours':
				return esc_html( akari_hours( 'dinner' ) );
			case 'closed':
				return esc_html( akari_closed_label() );
			case 'tel_link':
				return '';
			default:
				$value = akari_store( (string) $atts['key'] );
				return is_scalar( $value ) ? esc_html( (string) $value ) : '';
		}
	}
);

/* ------------------------------------------------------------
 * 小さな部品
 * ------------------------------------------------------------ */

/** インラインSVGのアイコン（aria-hidden）。 */
function akari_icon( string $name ): string {
	$paths = array(
		'tel'      => '<path d="M6.6 10.8a15.2 15.2 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z" fill="currentColor"/>',
		'external' => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
		'menu'     => '<path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
		'close'    => '<path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
		'map'      => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="9.5" r="2.5" fill="currentColor"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.3" cy="6.7" r="1.2" fill="currentColor"/>',
		'lamp'     => '<path d="M12 2v3M8 5h8l-1 3H9zM7.5 8h9a1.5 1.5 0 0 1 1.5 1.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 17.5v-8A1.5 1.5 0 0 1 7.5 8zM10 19v3h4v-3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M12 11.5c1.2 1.2 1.6 2.2 1.6 3a1.6 1.6 0 0 1-3.2 0c0-.8.4-1.8 1.6-3z" fill="currentColor"/>',
	);
	return '<svg class="icon icon--' . esc_attr( $name ) . '" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">' . ( $paths[ $name ] ?? '' ) . '</svg>';
}

/** 電話受付の注記。「電話受付：11:00〜21:00（水曜定休）」。受付時間が未設定なら「水曜定休」だけ。 */
function akari_tel_note(): string {
	$closed = akari_closed_label() ? akari_closed_label() . '定休' : '';
	if ( akari_store( 'tel_hours' ) ) {
		return '電話受付：' . akari_store( 'tel_hours' ) . ( $closed ? '（' . $closed . '）' : '' );
	}
	return $closed;
}

/**
 * 電話ボタン（U-05）。番号が未設定なら何も出さない。
 *
 * @param array{location?: string, label?: string, class?: string, number?: bool} $args
 */
function akari_tel_button( array $args = array() ): string {
	if ( ! akari_store( 'tel' ) ) {
		return '';
	}
	$args = wp_parse_args(
		$args,
		array(
			'location' => 'body',
			'label'    => '電話する',
			'class'    => '',
			'number'   => true,
		)
	);
	return sprintf(
		'<a class="btn btn--tel %1$s" href="tel:%2$s" data-track="tel" data-location="%3$s">%4$s<span class="btn__label">%5$s</span>%6$s</a>',
		esc_attr( $args['class'] ),
		esc_attr( akari_store( 'tel_link' ) ),
		esc_attr( $args['location'] ),
		akari_icon( 'tel' ),
		esc_html( $args['label'] ),
		$args['number'] ? '<span class="btn__num">' . esc_html( akari_store( 'tel' ) ) . '</span>' : ''
	);
}

/**
 * ネット予約ボタン（U-06）。URLが未設定なら何も出さない。
 *
 * @param array{location?: string, course?: string, class?: string} $args
 */
function akari_reserve_button( array $args = array() ): string {
	if ( ! akari_store( 'reserve_url' ) ) {
		return '';
	}
	$args = wp_parse_args(
		$args,
		array(
			'location' => 'body',
			'course'   => '',
			'class'    => '',
		)
	);
	return sprintf(
		'<a class="btn btn--reserve %1$s" href="%2$s" target="_blank" rel="noopener" data-track="reserve" data-location="%3$s"%4$s><span class="btn__label">ネット予約</span>%5$s<span class="screen-reader-text">（新しいタブで開きます）</span></a>',
		esc_attr( $args['class'] ),
		esc_url( akari_store( 'reserve_url' ) ),
		esc_attr( $args['location'] ),
		$args['course'] ? ' data-course="' . esc_attr( $args['course'] ) . '"' : '',
		akari_icon( 'external' )
	);
}

/** 「経路を調べる」リンク。GoogleマップのURLが未設定なら何も出さない。 */
function akari_route_link( string $location = 'body', string $label = '経路を調べる' ): string {
	if ( ! akari_store( 'map_url' ) ) {
		return '';
	}
	return sprintf(
		'<a class="route-link" href="%1$s" target="_blank" rel="noopener" data-track="map" data-location="%2$s">%3$s%4$s<span class="screen-reader-text">（新しいタブで開きます）</span></a>',
		esc_url( akari_store( 'map_url' ) ),
		esc_attr( $location ),
		akari_icon( 'map' ),
		esc_html( $label )
	);
}

/** 「〇〇を見る」のような行き先リンク（矢印つき）。 */
function akari_more_link( string $url, string $label, string $class = '' ): string {
	if ( '' === $url ) {
		return '';
	}
	return sprintf( '<a class="more-link %1$s" href="%2$s">%3$s</a>', esc_attr( $class ), esc_url( $url ), esc_html( $label ) );
}

/** 公開中の固定ページのURL。下書き・未作成なら空（リンク切れを作らないため）。 */
function akari_page_url( string $slug ): string {
	$page = get_page_by_path( $slug );
	return ( $page && 'publish' === $page->post_status ) ? get_permalink( $page ) : '';
}

/** 固定ページの入力欄の値。$page_id を省くと表示中のページ。 */
function akari_field( string $key, int $page_id = 0 ) {
	return get_post_meta( $page_id ? $page_id : get_queried_object_id(), $key, true );
}

/** 繰り返しの入力欄から、指定した項目が入っている行だけを返す。 */
function akari_rows( string $key, string $required_sub, int $page_id = 0 ): array {
	$rows = akari_field( $key, $page_id );
	if ( ! is_array( $rows ) ) {
		return array();
	}
	return array_values( array_filter( $rows, fn( $row ) => ! empty( $row[ $required_sub ] ) ) );
}

/** 複数行の文を段落にする（入力欄の文用）。 */
function akari_text( string $text ): string {
	return '' === trim( $text ) ? '' : wpautop( esc_html( $text ) );
}

/**
 * 画像（9.2・9.3）。width・height を必ず出し、最初の画面に入らないものは遅延読み込み。
 *
 * @param array{lazy?: bool, class?: string, size?: string, high?: bool} $args
 */
function akari_img( int $id, string $sizes, array $args = array() ): string {
	if ( ! $id ) {
		return '';
	}
	$args = wp_parse_args(
		$args,
		array(
			'lazy'  => true,
			'class' => '',
			'size'  => 'akari-w1200',
			'high'  => false,
		)
	);
	$attr = array(
		'sizes'    => $sizes,
		'class'    => $args['class'],
		'decoding' => 'async',
		'loading'  => $args['lazy'] ? 'lazy' : false,
	);
	if ( $args['high'] ) {
		$attr['fetchpriority'] = 'high';
	}
	return wp_get_attachment_image( $id, $args['size'], false, $attr );
}

/**
 * 文を読点・句点の直後で区切り、句ごとに span で包む（キャッチが句の途中で折り返さないように）。文字は変えない。
 * 返り値はエスケープ済みのHTML。
 */
function akari_phrases( string $text, string $class = 'hero__phrase' ): string {
	$parts = preg_split( '/(?<=[、。，．！？])/u', $text, -1, PREG_SPLIT_NO_EMPTY );
	$html  = '';
	foreach ( (array) $parts as $part ) {
		$html .= '<span class="' . esc_attr( $class ) . '">' . esc_html( $part ) . '</span>';
	}
	return $html;
}

/** トップのメインビジュアルの画像ID。片方だけなら両方に同じものを使う。 */
function akari_main_visual_ids(): array {
	$front = (int) get_option( 'page_on_front' );
	$sp    = (int) get_post_meta( $front, 'akari_mv_sp', true );
	$pc    = (int) get_post_meta( $front, 'akari_mv_pc', true );
	return array(
		'sp' => $sp ? $sp : $pc,
		'pc' => $pc ? $pc : $sp,
	);
}

/**
 * グローバルナビの項目（U-02）。公開しているページだけ出す。
 * 夜の楽しみ方はコース（D1）が決まるまでナビから外す（2026-10-31 店主依頼。ページのファイルは残す）。
 * 宴会・貸切のページは廃止（2026-10-31 店主依頼。案内はトップと店舗情報の #party）。
 */
function akari_nav_items(): array {
	$items = array(
		'menu'   => 'お品書き',
		'about'  => 'こだわり',
		'access' => '店舗情報',
	);
	$list = array();
	foreach ( $items as $slug => $label ) {
		$url = akari_page_url( $slug );
		if ( $url ) {
			$list[] = array(
				'label'   => $label,
				'url'     => $url,
				'current' => is_page( $slug ),
			);
		}
	}
	$news_id = (int) get_option( 'page_for_posts' );
	if ( $news_id ) {
		$list[] = array(
			'label'   => 'お知らせ',
			'url'     => get_permalink( $news_id ),
			'current' => is_home() || is_single() || is_category(),
		);
	}
	return $list;
}

/* ------------------------------------------------------------
 * データの取得
 * ------------------------------------------------------------ */

/** コース（種別：night / party）。表示順の小さい順。 */
function akari_get_courses( string $type ): array {
	return get_posts(
		array(
			'post_type'      => 'akari_course',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'meta_key'       => 'akari_course_type', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $type, // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
			'no_found_rows'  => true,
		)
	);
}

/** 当月（日本時間）の月替わり。$limit=-1 で全件。 */
function akari_get_monthly( int $limit = -1 ): array {
	return get_posts(
		array(
			'post_type'      => 'akari_monthly',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'   => 'akari_month',
					'value' => wp_date( 'Y-m' ),
				),
			),
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);
}

/** お品書き（区分ごと）。 */
function akari_get_menu_items( string $cat ): array {
	return get_posts(
		array(
			'post_type'      => 'akari_menu',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_key'       => 'akari_menu_cat', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $cat, // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
		)
	);
}

/** 品目（1行1品）を配列にする。 */
function akari_course_items( int $post_id ): array {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', (string) get_post_meta( $post_id, 'akari_items', true ) ) ) ) );
}

/** 提供のラベル（昼／夜／昼夜）。 */
function akari_serve_label( int $post_id ): string {
	$serve = (string) get_post_meta( $post_id, 'akari_serve', true );
	$label = akari_choices( 'serve' )[ $serve ] ?? '';
	return $label ? '<span class="serve-label serve-label--' . esc_attr( $serve ) . '">' . esc_html( $label ) . '</span>' : '';
}
