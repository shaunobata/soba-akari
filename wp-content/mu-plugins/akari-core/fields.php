<?php
/**
 * 入力欄（メタボックス）と保存時の検証（詳細設計書3.2）。
 * ACFは使わず、入力欄を固定して保存時に検証する。必須の欠けや字数の超過があれば
 * 公開せず下書きのまま保存し、直す点を日本語で出す。入力した値は消さない。
 * 字数の上限は仮の値で、ワイヤー（関門2）で確定する。
 */

defined( 'ABSPATH' ) || exit;

/** タイトル（品名・コース名・お知らせのタイトル）の字数の上限。 */
function akari_title_rules( string $post_type ): array {
	$rules = array(
		'akari_monthly'  => array( 'label' => '品名', 'max' => 20 ),
		'akari_seasonal' => array( 'label' => '品名', 'max' => 20 ),
		'akari_menu'     => array( 'label' => '品名', 'max' => 30 ),
		'akari_course'  => array( 'label' => 'コース名', 'max' => 20 ),
		'post'          => array( 'label' => 'タイトル', 'max' => 40 ),
	);
	return $rules[ $post_type ] ?? array();
}

/** 掲載月の初期値。20日以降なら翌月、19日までなら当月（D6で月初に代行入力するときの間違い防止）。 */
function akari_default_month(): string {
	$now = current_datetime();
	return (int) $now->format( 'j' ) >= 20 ? $now->modify( 'first day of next month' )->format( 'Y-m' ) : $now->format( 'Y-m' );
}

/**
 * 投稿タイプ・ページごとの入力欄。
 * type: text / textarea / price / int / radio / select / month / checkbox / image / slug / rows
 */
function akari_meta_fields( string $post_type, int $post_id = 0 ): array {
	switch ( $post_type ) {
		case 'akari_monthly':
			return array(
				'akari_price' => array( 'label' => '税込価格', 'type' => 'price', 'required' => true ),
				'akari_desc'  => array( 'label' => '説明', 'type' => 'textarea', 'max' => 60, 'max_lines' => 2, 'rows' => 3, 'placeholder' => '例：今朝届いた牡蠣を、さっと揚げました。' ),
				'akari_serve' => array( 'label' => '提供', 'type' => 'radio', 'choices' => akari_choices( 'serve' ), 'required' => true, 'default' => 'both' ),
				'akari_month' => array( 'label' => '掲載月', 'type' => 'month', 'required' => true, 'default' => akari_default_month(), 'help' => 'この月のあいだだけ表示されます。月が変わると自動で消えます。' ),
			);

		case 'akari_seasonal':
			return array(
				'akari_price'       => array( 'label' => '税込価格', 'type' => 'price', 'required' => true ),
				'akari_desc'        => array( 'label' => '説明', 'type' => 'textarea', 'max' => 60, 'max_lines' => 2, 'rows' => 3, 'placeholder' => '例：寒い時期だけの、温かい蕎麦です。' ),
				'akari_serve'       => array( 'label' => '提供', 'type' => 'radio', 'choices' => akari_choices( 'serve' ), 'required' => true, 'default' => 'both' ),
				'akari_season_from' => array( 'label' => '掲載の始まりの月', 'type' => 'month', 'required' => true, 'default' => wp_date( 'Y-m' ) ),
				'akari_season_to'   => array( 'label' => '掲載の終わりの月', 'type' => 'month', 'help' => 'この月の終わりまで表示します。空なら、下書きに戻すまで表示し続けます。' ),
			);

		case 'akari_menu':
			return array(
				'akari_price'    => array( 'label' => '税込価格', 'type' => 'price', 'required' => true ),
				'akari_menu_cat' => array( 'label' => '区分', 'type' => 'select', 'choices' => akari_choices( 'menu_cat' ), 'required' => true ),
				'akari_desc'     => array( 'label' => '短い説明', 'type' => 'text', 'max' => 40 ),
				'akari_serve'    => array( 'label' => '提供', 'type' => 'radio', 'choices' => akari_choices( 'serve' ), 'required' => true, 'default' => 'both' ),
			);

		case 'akari_course':
			return array(
				'akari_course_type' => array( 'label' => '種別', 'type' => 'radio', 'choices' => akari_choices( 'course_type' ), 'required' => true, 'default' => 'night' ),
				'akari_price'       => array( 'label' => '税込価格', 'type' => 'price', 'help' => '空なら「価格はお電話でお問い合わせください」と出ます（D1未定のあいだ）' ),
				'akari_items'       => array( 'label' => '品目', 'type' => 'textarea', 'required' => true, 'max_lines' => 10, 'rows' => 8, 'help' => '1行に1品、出てくる順に（最大10行）' ),
				'akari_summary'     => array( 'label' => '品目の概要', 'type' => 'text', 'max' => 40, 'help' => 'トップに出す短い要約。空なら先頭3品と「ほか」' ),
				'akari_conditions'  => array( 'label' => '予約条件', 'type' => 'textarea', 'rows' => 2, 'help' => '人数・締切など' ),
				'akari_drink'       => array( 'label' => '飲み放題', 'type' => 'text' ),
				'akari_course_id'   => array( 'label' => '計測用ID', 'type' => 'slug', 'required' => true, 'help' => '英小文字・数字・_ だけ。例：night_a（reserve_click の course_id になる）' ),
			);

		case 'page':
			return akari_page_fields( $post_id );
	}
	return array();
}

/** 固定ページの入力欄。SEOの欄は全ページ、それ以外はページごと。 */
function akari_page_fields( int $post_id ): array {
	$fields = array(
		'akari_seo_title' => array( 'label' => 'SEOタイトル', 'type' => 'text', 'soft_max' => 32, 'help' => '空ならページ名＋「｜手打そば 燈り 三鷹」', 'group' => 'seo' ),
		'akari_seo_desc'  => array( 'label' => 'ディスクリプション', 'type' => 'textarea', 'soft_max' => 125, 'rows' => 3, 'help' => '120字前後。OGPの説明にも使います', 'group' => 'seo', 'recommended' => true ),
		'akari_noindex'   => array( 'label' => '検索に出さない', 'type' => 'checkbox', 'group' => 'seo' ),
		'akari_ogp_image' => array( 'label' => 'OGP画像（1,200×630px）', 'type' => 'image', 'help' => '空なら店舗情報の既定の画像', 'group' => 'seo' ),
	);

	$slug = $post_id ? get_post_field( 'post_name', $post_id ) : '';
	if ( $post_id && (int) get_option( 'page_on_front' ) === $post_id ) {
		$slug = '__front';
	}

	$by_page = array(
		'__front' => array(
			'akari_catch'      => array( 'label' => 'キャッチコピー（D8）', 'type' => 'text', 'max' => 24, 'help' => 'メインビジュアルに文字で重ねます' ),
			'akari_mv_sp'      => array( 'label' => '1 メインビジュアル（スマホ 4:5）', 'type' => 'image' ),
			'akari_mv_pc'      => array( 'label' => '1 メインビジュアル（PC 16:9）', 'type' => 'image' ),
			'akari_mv_logo'    => array( 'label' => '1 メインビジュアルのロゴ', 'type' => 'image', 'help' => '背景が透明なPNG（幅360px以上）。空なら店名を文字で出します' ),
			'akari_lunch_text' => array( 'label' => '1.5 昼の燈りの文', 'type' => 'textarea', 'rows' => 2 ),
			'akari_lunch_img'  => array( 'label' => '1.5 昼の写真（蕎麦）', 'type' => 'image' ),
			'akari_night_text' => array( 'label' => '1.5 夜の燈りの文', 'type' => 'textarea', 'rows' => 3 ),
			'akari_night_img'  => array( 'label' => '1.5 夜の写真（天ぷら・酒）', 'type' => 'image' ),
			'akari_party_text' => array( 'label' => '2 宴会・会食・貸切のリード文', 'type' => 'textarea', 'rows' => 3 ),
			'akari_party_img'  => array( 'label' => '2 席の引きの写真', 'type' => 'image' ),
			'akari_picks'      => array(
				'label'  => '4 夜のおすすめ3品',
				'type'   => 'rows',
				'count'  => 3,
				'fields' => array(
					'title' => array( 'label' => '見出し', 'type' => 'text', 'max' => 16 ),
					'text'  => array( 'label' => '短い文', 'type' => 'textarea', 'max' => 60, 'rows' => 2 ),
					'image' => array( 'label' => '写真', 'type' => 'image' ),
				),
			),
			'akari_about_text' => array( 'label' => '6 蕎麦へのこだわりの文', 'type' => 'textarea', 'rows' => 3, 'help' => '文と写真の両方が空ならセクションごと出しません' ),
			'akari_about_img'  => array( 'label' => '6 蕎麦を打つ手元の写真', 'type' => 'image' ),
			// お客様の声（旧7）は 2026-10-31 店主依頼で削除
		),
		'about'   => array(
			'akari_flour_text' => array( 'label' => '2 蕎麦粉の文', 'type' => 'textarea', 'rows' => 4 ),
			'akari_flour_img'  => array( 'label' => '2 蕎麦粉・石臼の写真', 'type' => 'image' ),
			'akari_daynight'   => array(
				'label'  => '3 昼の蕎麦・夜の締めの蕎麦',
				'type'   => 'rows',
				'count'  => 2,
				'fields' => array(
					'title' => array( 'label' => '見出し', 'type' => 'text', 'max' => 16 ),
					'text'  => array( 'label' => '文', 'type' => 'textarea', 'rows' => 3 ),
					'image' => array( 'label' => '写真', 'type' => 'image' ),
				),
			),
			'akari_owner_text' => array( 'label' => '4 店主の想い', 'type' => 'textarea', 'rows' => 5, 'help' => '11/4のインタビューから。空ならこのセクションを出さない' ),
			'akari_owner_img'  => array( 'label' => '4 写真（顔出しはD9次第。決まるまでは手元の写真）', 'type' => 'image' ),
		),
		'menu'    => array(
			'akari_sake_note' => array( 'label' => '日本酒の入れ替えの注記', 'type' => 'text', 'help' => '例：日本酒は月替わりで入れ替わります' ),
		),
		'night'   => array(
			'akari_solo'       => array( 'label' => '3 一人呑みのモデル', 'type' => 'textarea', 'rows' => 3, 'help' => '注文の例と目安の金額（D1確定後）。空ならお品書きへのリンクだけ出す' ),
			'akari_couple'     => array( 'label' => '4 夫婦で楽しむモデル', 'type' => 'textarea', 'rows' => 3 ),
			'akari_sake_text'  => array( 'label' => '5 日本酒の選び方', 'type' => 'textarea', 'rows' => 3 ),
			'akari_sake_img'   => array( 'label' => '5 酒の写真', 'type' => 'image' ),
			'akari_party_lead' => array( 'label' => '7 宴会への案内', 'type' => 'textarea', 'rows' => 2 ),
		),
		// 宴会・貸切のページは廃止し（2026-10-31 店主依頼）、席・貸切・よくある質問は店舗情報ページの「宴会・貸切のご相談」に出す
		'access'  => array(
			'akari_route'        => array(
				'label'  => '4 三鷹駅北口からの道順',
				'type'   => 'rows',
				'count'  => 3,
				'fields' => array(
					'image' => array( 'label' => '目印の写真（4:3）', 'type' => 'image' ),
					'text'  => array( 'label' => '説明', 'type' => 'textarea', 'rows' => 2 ),
				),
			),
			'akari_layout_img'   => array( 'label' => '5 宴会・貸切：席配置図', 'type' => 'image' ),
			'akari_seat_img'     => array( 'label' => '5 宴会・貸切：席の写真', 'type' => 'image' ),
			'akari_seat_guide'   => array(
				'label'  => '5 宴会・貸切：人数別のおすすめ席',
				'type'   => 'rows',
				'count'  => 5,
				'fields' => array(
					'people' => array( 'label' => '人数', 'type' => 'text', 'max' => 12 ),
					'seats'  => array( 'label' => '使う卓', 'type' => 'text', 'max' => 30 ),
				),
			),
			'akari_private_text' => array( 'label' => '5 宴会・貸切：貸切の条件（D3）', 'type' => 'textarea', 'rows' => 3, 'help' => '空なら店舗情報の「貸切」の値を出します' ),
			'akari_faq'          => array(
				'label'  => '5 宴会・貸切：よくある質問',
				'type'   => 'rows',
				'count'  => 6,
				'fields' => array(
					'q' => array( 'label' => '質問', 'type' => 'text' ),
					'a' => array( 'label' => '回答（「お願い」の形で）', 'type' => 'textarea', 'rows' => 2, 'help' => '回答が空の質問は出しません' ),
				),
			),
		),
	);

	return $fields + ( $by_page[ $slug ] ?? array() );
}

/* ------------------------------------------------------------
 * メタボックス
 * ------------------------------------------------------------ */

add_action(
	'add_meta_boxes',
	function ( string $post_type, $post ) {
		if ( ! $post instanceof WP_Post ) {
			return;
		}
		$fields = akari_meta_fields( $post_type, $post->ID );
		if ( ! $fields ) {
			return;
		}
		if ( 'page' === $post_type ) {
			$page_fields = array_filter( $fields, fn( $f ) => ( $f['group'] ?? '' ) !== 'seo' );
			$seo_fields  = array_filter( $fields, fn( $f ) => ( $f['group'] ?? '' ) === 'seo' );
			if ( $page_fields ) {
				add_meta_box( 'akari-page-fields', 'ページの入力欄', 'akari_render_meta_box', 'page', 'normal', 'high', array( 'fields' => $page_fields ) );
			}
			add_meta_box( 'akari-seo-fields', '検索・共有の設定', 'akari_render_meta_box', 'page', 'normal', 'default', array( 'fields' => $seo_fields ) );
			return;
		}
		$titles = array(
			'akari_monthly'  => '月替わりの内容',
			'akari_seasonal' => '季節限定の内容',
			'akari_menu'    => 'お品書きの内容',
			'akari_course'  => 'コースの内容',
		);
		add_meta_box( 'akari-fields', $titles[ $post_type ], 'akari_render_meta_box', $post_type, 'normal', 'high', array( 'fields' => $fields ) );
	},
	10,
	2
);

function akari_render_meta_box( WP_Post $post, array $box ): void {
	wp_nonce_field( 'akari_fields', 'akari_fields_nonce' );
	echo '<div class="akari-fields">';
	foreach ( $box['args']['fields'] as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		if ( '' === $value && 'auto-draft' === $post->post_status && isset( $field['default'] ) ) {
			$value = $field['default'];
		}
		akari_render_field( 'akari[' . $key . ']', 'akari-' . $key, $field, $value );
	}
	echo '</div>';
}

/** @param mixed $value */
function akari_render_field( string $name, string $id, array $field, $value ): void {
	$required = ! empty( $field['required'] );
	$max      = $field['max'] ?? ( $field['soft_max'] ?? 0 );
	echo '<div class="akari-field akari-field--' . esc_attr( $field['type'] ) . '">';

	if ( 'rows' === $field['type'] ) {
		echo '<fieldset><legend class="akari-field__label">' . esc_html( $field['label'] ) . '</legend>';
		$rows = is_array( $value ) ? $value : array();
		for ( $i = 0; $i < $field['count']; $i++ ) {
			echo '<div class="akari-row"><span class="akari-row__num">' . ( $i + 1 ) . '</span>';
			foreach ( $field['fields'] as $sub_key => $sub ) {
				akari_render_field( $name . '[' . $i . '][' . $sub_key . ']', $id . '-' . $i . '-' . $sub_key, $sub, $rows[ $i ][ $sub_key ] ?? '' );
			}
			echo '</div>';
		}
		echo '</fieldset></div>';
		return;
	}

	$label = esc_html( $field['label'] ) . ( $required ? ' <span class="akari-required">必須</span>' : '' );
	$attrs = $max ? sprintf( ' data-max="%d"', (int) $max ) : '';
	if ( ! empty( $field['placeholder'] ) ) {
		$attrs .= ' placeholder="' . esc_attr( $field['placeholder'] ) . '"';
	}

	switch ( $field['type'] ) {
		case 'radio':
			echo '<fieldset><legend class="akari-field__label">' . $label . '</legend>'; // phpcs:ignore WordPress.Security.EscapeOutput
			foreach ( $field['choices'] as $v => $text ) {
				printf(
					'<label class="akari-choice"><input type="radio" name="%1$s" value="%2$s" %3$s> %4$s</label>',
					esc_attr( $name ),
					esc_attr( $v ),
					checked( (string) $value, (string) $v, false ),
					esc_html( $text )
				);
			}
			echo '</fieldset>';
			break;

		case 'select':
			printf( '<label class="akari-field__label" for="%1$s">%2$s</label><select id="%1$s" name="%3$s"><option value="">選んでください</option>', esc_attr( $id ), $label, esc_attr( $name ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			foreach ( $field['choices'] as $v => $text ) {
				printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $v ), selected( (string) $value, (string) $v, false ), esc_html( $text ) );
			}
			echo '</select>';
			break;

		case 'checkbox':
			printf( '<label class="akari-choice"><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label>', esc_attr( $name ), checked( (bool) $value, true, false ), esc_html( $field['label'] ) );
			break;

		case 'image':
			echo '<span class="akari-field__label">' . $label . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
			akari_render_image_picker( $name, (int) $value, $id );
			break;

		case 'textarea':
			printf(
				'<label class="akari-field__label" for="%1$s">%2$s</label><textarea id="%1$s" name="%3$s" rows="%4$d" class="large-text"%5$s>%6$s</textarea>',
				esc_attr( $id ),
				$label, // phpcs:ignore WordPress.Security.EscapeOutput
				esc_attr( $name ),
				(int) ( $field['rows'] ?? 3 ),
				$attrs, // phpcs:ignore WordPress.Security.EscapeOutput
				esc_textarea( (string) $value )
			);
			break;

		case 'price':
			printf(
				'<label class="akari-field__label" for="%1$s">%2$s</label><span class="akari-price"><input type="text" inputmode="numeric" id="%1$s" name="%3$s" value="%4$s" class="small-text" data-price autocomplete="off"> 円</span>',
				esc_attr( $id ),
				$label, // phpcs:ignore WordPress.Security.EscapeOutput
				esc_attr( $name ),
				esc_attr( (string) $value )
			);
			break;

		default:
			$types = array(
				'month' => 'month',
				'int'   => 'number',
			);
			printf(
				'<label class="akari-field__label" for="%1$s">%2$s</label><input type="%3$s" id="%1$s" name="%4$s" value="%5$s" class="%6$s"%7$s>',
				esc_attr( $id ),
				$label, // phpcs:ignore WordPress.Security.EscapeOutput
				esc_attr( $types[ $field['type'] ] ?? 'text' ),
				esc_attr( $name ),
				esc_attr( (string) $value ),
				'month' === $field['type'] ? '' : 'large-text',
				$attrs // phpcs:ignore WordPress.Security.EscapeOutput
			);
	}

	if ( ! empty( $field['help'] ) ) {
		echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
	}
	echo '</div>';
}

/* ------------------------------------------------------------
 * 値を整える・検証する
 * ------------------------------------------------------------ */

/**
 * @param mixed $raw
 * @return mixed
 */
function akari_sanitize_field( array $field, $raw ) {
	switch ( $field['type'] ) {
		case 'rows':
			$rows = array();
			for ( $i = 0; $i < $field['count']; $i++ ) {
				foreach ( $field['fields'] as $sub_key => $sub ) {
					$rows[ $i ][ $sub_key ] = akari_sanitize_field( $sub, $raw[ $i ][ $sub_key ] ?? '' );
				}
			}
			return $rows;
		case 'price':
		case 'int':
			// 全角数字は半角に直し、「円」「,」などは削る。
			$digits = preg_replace( '/\D/', '', mb_convert_kana( (string) $raw, 'n' ) );
			return '' === $digits ? '' : (int) $digits;
		case 'radio':
		case 'select':
			return array_key_exists( (string) $raw, $field['choices'] ) ? (string) $raw : '';
		case 'month':
			return preg_match( '/^\d{4}-(0[1-9]|1[0-2])$/', (string) $raw ) ? (string) $raw : '';
		case 'checkbox':
			return $raw ? 1 : 0;
		case 'image':
			return absint( $raw );
		case 'slug':
			return preg_replace( '/[^a-z0-9_]/', '', strtolower( mb_convert_kana( (string) $raw, 'a' ) ) );
		case 'textarea':
			return trim( sanitize_textarea_field( (string) $raw ) );
		default:
			return trim( sanitize_text_field( (string) $raw ) );
	}
}

/** 送られてきた入力欄の値を整えて返す。 */
function akari_collect_fields( array $fields ): array {
	$input  = wp_unslash( $_POST['akari'] ?? array() ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
	$values = array();
	foreach ( $fields as $key => $field ) {
		$values[ $key ] = akari_sanitize_field( $field, $input[ $key ] ?? '' );
	}
	return $values;
}

/** 直す点の一覧（日本語）を返す。空なら問題なし。 */
function akari_validate_fields( array $fields, array $values ): array {
	$errors = array();
	foreach ( $fields as $key => $field ) {
		$value = $values[ $key ] ?? '';
		$label = $field['label'];
		if ( ! empty( $field['required'] ) && ( '' === $value || 0 === $value ) ) {
			$errors[] = $label . 'を入れてください。';
			continue;
		}
		if ( 'price' === $field['type'] && '' !== $value && $value < 1 ) {
			$errors[] = $label . 'は1円以上の数字で入れてください。';
		}
		if ( ! empty( $field['max'] ) && is_string( $value ) && mb_strlen( str_replace( "\n", '', $value ) ) > $field['max'] ) {
			$errors[] = sprintf( '%sは%d字以内で入れてください（今は%d字）。', $label, $field['max'], mb_strlen( str_replace( "\n", '', $value ) ) );
		}
		if ( ! empty( $field['max_lines'] ) && is_string( $value ) && count( preg_split( '/\R/u', $value ) ) > $field['max_lines'] ) {
			$errors[] = 'akari_items' === $key
				? sprintf( '%sは%d行までにしてください。', $label, $field['max_lines'] )
				: sprintf( '%sの改行は%dつまでにしてください。', $label, $field['max_lines'] - 1 );
		}
		if ( 'rows' === $field['type'] && is_array( $value ) ) {
			foreach ( $value as $i => $row ) {
				foreach ( akari_validate_fields( $field['fields'], $row ) as $error ) {
					$errors[] = sprintf( '%s（%d件目）：%s', $label, $i + 1, $error );
				}
			}
		}
	}
	return $errors;
}

/**
 * 保存の直前に検証する。問題があれば公開せず下書きにし、直す点を一時保存しておく。
 * 固定ページ（KOKUBOだけが編集）は表示を止めないよう、警告だけにする。
 */
add_filter(
	'wp_insert_post_data',
	function ( array $data, array $postarr ) {
		if ( empty( $_POST['akari_fields_nonce'] ) && empty( $_POST['akari_title_check'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $data;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $data;
		}
		$post_type        = $data['post_type'];
		$data['post_title'] = trim( $data['post_title'] );
		$errors           = array();

		$title = akari_title_rules( $post_type );
		if ( $title ) {
			$plain = wp_unslash( $data['post_title'] );
			if ( '' === $plain ) {
				$errors[] = $title['label'] . 'を入れてください。';
			} elseif ( mb_strlen( $plain ) > $title['max'] ) {
				$errors[] = sprintf( '%sは%d字以内で入れてください（今は%d字）。', $title['label'], $title['max'], mb_strlen( $plain ) );
			}
		}

		$fields = akari_meta_fields( $post_type, (int) ( $postarr['ID'] ?? 0 ) );
		if ( $fields && ! empty( $_POST['akari_fields_nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$values = akari_collect_fields( $fields );
			$errors = array_merge( $errors, akari_validate_fields( $fields, $values ) );
			// 季節限定：終わりの月は始まりの月と同じかそれより後
			if ( 'akari_seasonal' === $post_type && '' !== $values['akari_season_to'] && '' !== $values['akari_season_from'] && $values['akari_season_to'] < $values['akari_season_from'] ) {
				$errors[] = '掲載の終わりの月は、始まりの月と同じか、それより後の月にしてください。';
			}
		}

		if ( $errors ) {
			if ( ! empty( $postarr['ID'] ) ) {
				set_transient( 'akari_errors_' . get_current_user_id() . '_' . (int) $postarr['ID'], $errors, 5 * MINUTE_IN_SECONDS );
			}
			if ( 'page' !== $post_type && in_array( $data['post_status'], array( 'publish', 'future' ), true ) ) {
				$data['post_status'] = 'draft';
			}
		}
		return $data;
	},
	10,
	2
);

// タイトルだけの検証（お知らせ）のための目印。お知らせにはメタボックスがないため。
add_action(
	'edit_form_after_title',
	function ( WP_Post $post ) {
		if ( akari_title_rules( $post->post_type ) ) {
			echo '<input type="hidden" name="akari_title_check" value="1">';
		}
	}
);

add_action(
	'save_post',
	function ( int $post_id, WP_Post $post ) {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( empty( $_POST['akari_fields_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['akari_fields_nonce'] ), 'akari_fields' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$fields = akari_meta_fields( $post->post_type, $post_id );
		foreach ( akari_collect_fields( $fields ) as $key => $value ) {
			if ( '' === $value || array() === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	},
	10,
	2
);

// 問題があったときは「公開しました」ではなく、直す点を出す。
add_filter(
	'redirect_post_location',
	function ( string $location, int $post_id ) {
		if ( get_transient( 'akari_errors_' . get_current_user_id() . '_' . $post_id ) ) {
			$location = add_query_arg( 'message', 10, $location ); // 「下書きを更新しました」
		}
		return $location;
	},
	10,
	2
);

add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'post' !== $screen->base || empty( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$key    = 'akari_errors_' . get_current_user_id() . '_' . absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification
		$errors = get_transient( $key );
		if ( ! $errors ) {
			return;
		}
		delete_transient( $key );
		$lead = 'page' === $screen->post_type ? '次の点を確かめてください。' : '直す点があるため、公開せず下書きで保存しました。直してからもう一度「公開」を押してください。';
		echo '<div class="notice notice-error"><p><strong>' . esc_html( $lead ) . '</strong></p><ul style="list-style:disc;padding-left:1.5em">';
		foreach ( $errors as $error ) {
			echo '<li>' . esc_html( $error ) . '</li>';
		}
		echo '</ul></div>';
	}
);

/* ------------------------------------------------------------
 * 一覧画面の列
 * ------------------------------------------------------------ */

/** 月替わりの状態：掲載中／来月から／〇月から／終了 */
function akari_monthly_status( string $month ): string {
	$now = wp_date( 'Y-m' );
	if ( '' === $month ) {
		return '―';
	}
	if ( $month === $now ) {
		return '掲載中';
	}
	if ( $month < $now ) {
		return '終了';
	}
	$next = current_datetime()->modify( 'first day of next month' )->format( 'Y-m' );
	return $month === $next ? '来月から' : (int) substr( $month, 5 ) . '月から';
}

add_filter(
	'manage_akari_monthly_posts_columns',
	function ( array $columns ) {
		$columns['title'] = '品名';
		return array_slice( $columns, 0, 2 ) + array(
			'akari_month'  => '掲載月',
			'akari_status' => '状態',
			'akari_price'  => '税込価格',
		) + $columns;
	}
);
/** 季節限定の状態：掲載中／〇月から／終了 */
function akari_seasonal_status( string $from, string $to ): string {
	$now = wp_date( 'Y-m' );
	if ( '' === $from ) {
		return '―';
	}
	if ( $from > $now ) {
		return (int) substr( $from, 5 ) . '月から';
	}
	return ( '' !== $to && $to < $now ) ? '終了' : '掲載中';
}

/** 季節限定の掲載期間の表示。「2026年11月〜2027年2月」「2026年11月〜」 */
function akari_season_period( string $from, string $to ): string {
	$fmt = fn( string $m ) => '' === $m ? '' : (int) substr( $m, 0, 4 ) . '年' . (int) substr( $m, 5 ) . '月';
	return '' === $from ? '―' : $fmt( $from ) . '〜' . $fmt( $to );
}

add_filter(
	'manage_akari_seasonal_posts_columns',
	function ( array $columns ) {
		$columns['title'] = '品名';
		return array_slice( $columns, 0, 2 ) + array(
			'akari_period' => '掲載期間',
			'akari_status' => '状態',
			'akari_price'  => '税込価格',
		) + $columns;
	}
);
add_action(
	'manage_akari_seasonal_posts_custom_column',
	function ( string $column, int $post_id ) {
		$from = (string) get_post_meta( $post_id, 'akari_season_from', true );
		$to   = (string) get_post_meta( $post_id, 'akari_season_to', true );
		switch ( $column ) {
			case 'akari_period':
				echo esc_html( akari_season_period( $from, $to ) );
				break;
			case 'akari_status':
				echo esc_html( akari_seasonal_status( $from, $to ) );
				break;
			case 'akari_price':
				echo esc_html( akari_price_text( get_post_meta( $post_id, 'akari_price', true ) ) );
				break;
		}
	},
	10,
	2
);

add_filter(
	'manage_akari_menu_posts_columns',
	fn( array $columns ) => array_slice( $columns, 0, 2 ) + array(
		'akari_menu_cat' => '区分',
		'akari_price'    => '税込価格',
	) + $columns
);
add_filter(
	'manage_akari_course_posts_columns',
	fn( array $columns ) => array_slice( $columns, 0, 2 ) + array(
		'akari_course_type' => '種別',
		'akari_price'       => '税込価格',
	) + $columns
);

foreach ( array( 'akari_monthly', 'akari_menu', 'akari_course' ) as $akari_pt ) {
	add_action(
		"manage_{$akari_pt}_posts_custom_column",
		function ( string $column, int $post_id ) {
			$meta = get_post_meta( $post_id, $column === 'akari_status' ? 'akari_month' : $column, true );
			switch ( $column ) {
				case 'akari_month':
					echo esc_html( $meta ? str_replace( '-', '年', $meta ) . '月' : '―' );
					break;
				case 'akari_status':
					echo esc_html( akari_monthly_status( (string) $meta ) );
					break;
				case 'akari_price':
					echo esc_html( akari_price_text( $meta ) );
					break;
				case 'akari_menu_cat':
					echo esc_html( akari_choices( 'menu_cat' )[ $meta ] ?? '―' );
					break;
				case 'akari_course_type':
					echo esc_html( akari_choices( 'course_type' )[ $meta ] ?? '―' );
					break;
			}
		},
		10,
		2
	);
}
unset( $akari_pt );

/** 価格の表示。「1,200円（税込）」。空なら空文字。 */
function akari_price_text( $price ): string {
	return ( '' === $price || null === $price || ! is_numeric( $price ) ) ? '' : number_format( (int) $price ) . '円（税込）';
}

/* ------------------------------------------------------------
 * 管理画面のJS・CSS（字数カウンタ、価格の全角→半角、画像の選択）
 * ------------------------------------------------------------ */

add_action(
	'admin_enqueue_scripts',
	function ( string $hook ) {
		$screen = get_current_screen();
		$is_edit = in_array( $hook, array( 'post.php', 'post-new.php' ), true );
		if ( ! $is_edit && ! str_contains( $hook, 'akari-' ) ) {
			return;
		}
		$theme_uri = get_template_directory_uri() . '/assets/';
		$theme_dir = get_template_directory() . '/assets/';
		if ( file_exists( $theme_dir . 'js/admin.js' ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'akari-admin', $theme_uri . 'js/admin.js', array(), (string) filemtime( $theme_dir . 'js/admin.js' ), true );
			$rule = $screen ? akari_title_rules( $screen->post_type ) : array();
			wp_add_inline_script( 'akari-admin', 'window.akariAdmin=' . wp_json_encode( array( 'titleMax' => $rule['max'] ?? 0 ) ) . ';', 'before' );
		}
		if ( file_exists( $theme_dir . 'css/admin.css' ) ) {
			wp_enqueue_style( 'akari-admin', $theme_uri . 'css/admin.css', array(), (string) filemtime( $theme_dir . 'css/admin.css' ) );
		}
	}
);
