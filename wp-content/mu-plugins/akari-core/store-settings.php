<?php
/**
 * 共通設定「店舗情報」（F-06、詳細設計書3.3）。
 * オプション akari_store（配列）に1か所で持ち、ヘッダー・フッター・各部品・構造化データはここから読む。
 * 編集できるのは edit_akari_store を持つ管理者（KOKUBO）だけ。
 */

defined( 'ABSPATH' ) || exit;

/**
 * 項目の定義。default が空の項目は【要確認】で、空のあいだはページにも構造化データにも出さない。
 */
function akari_store_fields(): array {
	return array(
		'基本情報'   => array(
			'name'             => array( 'label' => '店名', 'type' => 'text', 'default' => '手打そば 燈り', 'help' => 'Google・食べログと一字一句合わせる（「手打そば」と「燈り」の間は全角スペース1つ）' ),
			'postal_code'      => array( 'label' => '郵便番号', 'type' => 'text', 'default' => '', 'help' => '例：180-0006' ),
			'address_locality' => array( 'label' => '市区町村', 'type' => 'text', 'default' => '武蔵野市' ),
			'street_address'   => array( 'label' => '番地・建物', 'type' => 'text', 'default' => '中町1-24-7 カーサトミー1F' ),
			'access'           => array( 'label' => 'アクセス', 'type' => 'text', 'default' => '三鷹駅北口 徒歩5〜7分', 'help' => '実測で1つの数字に統一する' ),
			'tel'              => array( 'label' => '電話番号（表示用）', 'type' => 'text', 'default' => '', 'help' => '半角数字とハイフン。リンク用の国際表記（+81…）は自動で作る' ),
			'tel_hours'        => array( 'label' => '電話受付時間', 'type' => 'text', 'default' => '', 'help' => '例：11:00〜21:00' ),
		),
		'営業時間'   => array(
			'lunch_open'    => array( 'label' => '昼 開店', 'type' => 'time', 'default' => '11:30' ),
			'lunch_close'   => array( 'label' => '昼 閉店', 'type' => 'time', 'default' => '14:30' ),
			'dinner_open'   => array( 'label' => '夜 開店', 'type' => 'time', 'default' => '17:30' ),
			'dinner_close'  => array( 'label' => '夜 閉店', 'type' => 'time', 'default' => '21:30' ),
			'lo_food'       => array( 'label' => 'ラストオーダー（料理）', 'type' => 'time', 'default' => '' ),
			'lo_drink'      => array( 'label' => 'ラストオーダー（飲み物）', 'type' => 'time', 'default' => '' ),
			'closed_days'   => array( 'label' => '定休日', 'type' => 'days', 'default' => array( 3 ) ),
			'closed_note'   => array( 'label' => '定休日の補足', 'type' => 'text', 'default' => '', 'help' => '祝日の扱いなど' ),
		),
		'予約・お支払い' => array(
			'reserve_url' => array( 'label' => 'ネット予約URL', 'type' => 'url', 'default' => '', 'help' => '食べログネット予約の予約ページ。空ならネット予約ボタンを出さない' ),
			'payment'     => array( 'label' => 'お支払い', 'type' => 'text', 'default' => 'カードのみ', 'help' => '使えるブランド・現金の可否が決まったら書き足す' ),
			'receipt'     => array( 'label' => '領収書', 'type' => 'text', 'default' => '' ),
			'otoshi'      => array( 'label' => 'お通し・席料', 'type' => 'text', 'default' => '' ),
		),
		'席・宴会'   => array(
			'seats_total'  => array( 'label' => '席数', 'type' => 'int', 'default' => 24 ),
			'seats_layout' => array( 'label' => '席の構成', 'type' => 'text', 'default' => 'カウンター約6席、8人卓、4人卓×2、2人卓' ),
			'party_max'    => array( 'label' => '宴会の最大人数', 'type' => 'int', 'default' => '' ),
			'private_hire' => array( 'label' => '貸切', 'type' => 'text', 'default' => '貸切はご相談ください', 'help' => 'D3（貸切の条件）が決まったら差し替える' ),
			'smoking'      => array( 'label' => '禁煙', 'type' => 'text', 'default' => '全席禁煙' ),
		),
		'地図・外部リンク' => array(
			'map_url'       => array( 'label' => 'GoogleマップURL', 'type' => 'url', 'default' => '', 'help' => '「経路を調べる」の行き先' ),
			'map_embed'     => array( 'label' => '地図の埋め込みURL', 'type' => 'url', 'default' => '', 'help' => 'Googleマップの「地図を埋め込む」の iframe の src だけ' ),
			'geo_lat'       => array( 'label' => '緯度', 'type' => 'float', 'default' => '' ),
			'geo_lng'       => array( 'label' => '経度', 'type' => 'float', 'default' => '' ),
			'review_url'    => array( 'label' => 'Google口コミ投稿URL', 'type' => 'url', 'default' => '' ),
			'tabelog_url'   => array( 'label' => '食べログ店舗ページ', 'type' => 'url', 'default' => '' ),
			'instagram_url' => array( 'label' => 'Instagram', 'type' => 'url', 'default' => 'https://www.instagram.com/soba_akari/' ),
		),
		'共有・計測' => array(
			'default_desc' => array( 'label' => '既定の説明', 'type' => 'textarea', 'default' => '' ),
			'default_ogp'  => array( 'label' => '既定のOGP画像（1,200×630px）', 'type' => 'image', 'default' => 0 ),
			'gtm_id'       => array( 'label' => 'GTMのコンテナID', 'type' => 'text', 'default' => '', 'help' => '例：GTM-XXXXXXX。空ならGTMを出さない' ),
		),
	);
}

/** 項目のキー → 定義 の平たい配列。 */
function akari_store_field_map(): array {
	$map = array();
	foreach ( akari_store_fields() as $group ) {
		$map += $group;
	}
	return $map;
}

/**
 * 共通設定の値を返す。テンプレートからは akari_store('tel') で呼ぶ。
 * tel_link は tel から国際表記を作って返す。
 *
 * @return mixed
 */
function akari_store( string $key ) {
	static $values = null;
	if ( null === $values ) {
		$defaults = array();
		foreach ( akari_store_field_map() as $k => $field ) {
			$defaults[ $k ] = $field['default'];
		}
		$values = wp_parse_args( (array) get_option( 'akari_store', array() ), $defaults );
	}
	if ( 'tel_link' === $key ) {
		return akari_tel_link( (string) $values['tel'] );
	}
	return $values[ $key ] ?? '';
}

/** 0422-00-0000 → +81422000000 */
function akari_tel_link( string $tel ): string {
	$digits = preg_replace( '/\D/', '', mb_convert_kana( $tel, 'n' ) );
	if ( '' === $digits ) {
		return '';
	}
	return '0' === $digits[0] ? '+81' . substr( $digits, 1 ) : $digits;
}

/** 曜日の番号（0=日）→ 表示名 */
function akari_day_labels(): array {
	return array( '日', '月', '火', '水', '木', '金', '土' );
}

/** 定休日の短い表記。例：「水曜」「水・木曜」。なければ空。 */
function akari_closed_label(): string {
	$days = array_map( 'intval', (array) akari_store( 'closed_days' ) );
	if ( ! $days ) {
		return '';
	}
	sort( $days );
	$labels = akari_day_labels();
	return implode( '・', array_map( fn( $d ) => $labels[ $d ], $days ) ) . '曜';
}

/** 営業時間の表記。$part は lunch / dinner。例：「11:30〜14:30」 */
function akari_hours( string $part ): string {
	$open  = akari_store( $part . '_open' );
	$close = akari_store( $part . '_close' );
	return ( $open && $close ) ? $open . '〜' . $close : '';
}

/** ラストオーダーの表記。例：「料理 20:45／飲み物 21:00」 */
function akari_last_order(): string {
	$parts = array();
	if ( akari_store( 'lo_food' ) ) {
		$parts[] = '料理 ' . akari_store( 'lo_food' );
	}
	if ( akari_store( 'lo_drink' ) ) {
		$parts[] = '飲み物 ' . akari_store( 'lo_drink' );
	}
	return implode( '／', $parts );
}

/** 住所の1行表記。例：「〒180-0006 東京都武蔵野市中町1-24-7 カーサトミー1F」 */
function akari_address( bool $with_postal = true ): string {
	$postal = $with_postal && akari_store( 'postal_code' ) ? '〒' . akari_store( 'postal_code' ) . ' ' : '';
	return $postal . '東京都' . akari_store( 'address_locality' ) . akari_store( 'street_address' );
}

/* ------------------------------------------------------------
 * 管理画面「店舗情報」
 * ------------------------------------------------------------ */

add_action(
	'admin_menu',
	function () {
		add_menu_page( '店舗情報', '店舗情報', 'edit_akari_store', 'akari-store', 'akari_store_page', 'dashicons-store', 58 );
	}
);

function akari_store_page(): void {
	if ( ! current_user_can( 'edit_akari_store' ) ) {
		return;
	}
	wp_enqueue_media();
	?>
	<div class="wrap akari-admin">
		<h1>店舗情報</h1>
		<p>ここを変えると、ヘッダー・フッター・店舗情報・電話ボタン・ご予約の方法・構造化データのすべてに反映されます。Google・食べログと同じ日に揃えてください。</p>
		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success"><p>保存しました。</p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="akari_save_store">
			<?php wp_nonce_field( 'akari_save_store' ); ?>
			<?php foreach ( akari_store_fields() as $group => $fields ) : ?>
				<h2><?php echo esc_html( $group ); ?></h2>
				<table class="form-table" role="presentation">
					<?php foreach ( $fields as $key => $field ) : ?>
						<tr>
							<th scope="row"><label for="akari-store-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
							<td>
								<?php akari_store_render_input( $key, $field, akari_store( $key ) ); ?>
								<?php if ( ! empty( $field['help'] ) ) : ?>
									<p class="description"><?php echo esc_html( $field['help'] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>
			<?php submit_button( '保存する' ); ?>
		</form>
	</div>
	<?php
}

/** @param mixed $value */
function akari_store_render_input( string $key, array $field, $value ): void {
	$name = 'akari_store[' . $key . ']';
	$id   = 'akari-store-' . $key;
	switch ( $field['type'] ) {
		case 'days':
			$value = array_map( 'intval', (array) $value );
			foreach ( akari_day_labels() as $num => $label ) {
				printf(
					'<label style="margin-right:1em"><input type="checkbox" name="%1$s[]" value="%2$d" %3$s> %4$s曜</label>',
					esc_attr( $name ),
					(int) $num,
					checked( in_array( $num, $value, true ), true, false ),
					esc_html( $label )
				);
			}
			break;
		case 'textarea':
			printf( '<textarea id="%1$s" name="%2$s" rows="3" class="large-text">%3$s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( (string) $value ) );
			break;
		case 'image':
			akari_render_image_picker( $name, (int) $value, $id );
			break;
		default:
			$types = array(
				'time'  => 'time',
				'url'   => 'url',
				'int'   => 'number',
				'float' => 'text',
			);
			printf(
				'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="%5$s">',
				esc_attr( $types[ $field['type'] ] ?? 'text' ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value ),
				in_array( $field['type'], array( 'time', 'int', 'float' ), true ) ? 'small-text' : 'regular-text'
			);
	}
}

add_action(
	'admin_post_akari_save_store',
	function () {
		if ( ! current_user_can( 'edit_akari_store' ) ) {
			wp_die( '権限がありません。' );
		}
		check_admin_referer( 'akari_save_store' );

		$input  = wp_unslash( $_POST['akari_store'] ?? array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$values = array();
		foreach ( akari_store_field_map() as $key => $field ) {
			$raw = $input[ $key ] ?? '';
			switch ( $field['type'] ) {
				case 'days':
					$values[ $key ] = array_values( array_filter( array_map( 'intval', (array) $raw ), fn( $d ) => $d >= 0 && $d <= 6 ) );
					break;
				case 'url':
					$values[ $key ] = esc_url_raw( trim( (string) $raw ) );
					break;
				case 'int':
					$values[ $key ] = '' === trim( (string) $raw ) ? '' : absint( mb_convert_kana( (string) $raw, 'n' ) );
					break;
				case 'float':
					$raw            = trim( mb_convert_kana( (string) $raw, 'n' ) );
					$values[ $key ] = is_numeric( $raw ) ? (string) (float) $raw : '';
					break;
				case 'image':
					$values[ $key ] = absint( $raw );
					break;
				case 'time':
					$raw            = mb_convert_kana( trim( (string) $raw ), 'n' );
					$values[ $key ] = preg_match( '/^([01]?\d|2[0-3]):[0-5]\d$/', $raw ) ? $raw : '';
					break;
				case 'textarea':
					$values[ $key ] = sanitize_textarea_field( (string) $raw );
					break;
				default:
					$values[ $key ] = sanitize_text_field( (string) $raw );
			}
		}
		update_option( 'akari_store', $values );
		wp_safe_redirect( add_query_arg( 'updated', '1', admin_url( 'admin.php?page=akari-store' ) ) );
		exit;
	}
);

/**
 * 画像の選択欄（メディアから1枚選ぶ）。店舗情報と固定ページの入力欄で共用する。
 * 動きは admin.js の [data-akari-image] が受け持つ。
 */
function akari_render_image_picker( string $name, int $attachment_id, string $id = '' ): void {
	$preview = $attachment_id ? wp_get_attachment_image( $attachment_id, 'thumbnail' ) : '';
	?>
	<div class="akari-image" data-akari-image>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $attachment_id ); ?>" <?php echo $id ? 'id="' . esc_attr( $id ) . '"' : ''; ?>>
		<div class="akari-image__preview"><?php echo $preview; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<button type="button" class="button" data-akari-image-select>画像を選ぶ</button>
		<button type="button" class="button-link akari-image__remove" data-akari-image-remove <?php echo $attachment_id ? '' : 'hidden'; ?>>外す</button>
	</div>
	<?php
}
