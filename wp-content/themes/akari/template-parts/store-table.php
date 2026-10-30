<?php
/**
 * 店舗情報の表（U-07、F-06）。値はすべて共通設定から出し、未設定の行は出さない。
 * 引数 mode：full（店舗情報ページ）／summary（トップのセクション9）
 */

defined( 'ABSPATH' ) || exit;

$akari_mode   = ( $args['mode'] ?? 'full' ) === 'summary' ? 'summary' : 'full';
$akari_hours  = array_filter(
	array(
		akari_hours( 'lunch' ) ? '昼 ' . akari_hours( 'lunch' ) : '',
		akari_hours( 'dinner' ) ? '夜 ' . akari_hours( 'dinner' ) : '',
	)
);
$akari_closed = akari_closed_label() ? akari_closed_label() . '日' . ( akari_store( 'closed_note' ) ? '（' . akari_store( 'closed_note' ) . '）' : '' ) : '';
$akari_seats  = akari_store( 'seats_total' ) ? akari_store( 'seats_total' ) . '席' . ( akari_store( 'seats_layout' ) ? '（' . akari_store( 'seats_layout' ) . '）' : '' ) : '';

// 行の定義：見出し => 値（HTMLを含む場合は html キー）
$akari_rows = array(
	'店名'        => array( 'text' => akari_store( 'name' ), 'full' => true ),
	'住所'        => array( 'text' => akari_address() ),
	'アクセス'      => array( 'text' => akari_store( 'access' ) ),
	'営業時間'      => array( 'html' => implode( '<br>', array_map( 'esc_html', $akari_hours ) ) ),
	'ラストオーダー'   => array( 'text' => akari_last_order(), 'full' => true ),
	'定休日'       => array( 'text' => $akari_closed ),
	'席数'        => array( 'text' => $akari_seats ),
	'貸切'        => array( 'text' => akari_store( 'private_hire' ) ),
	'お支払い'      => array( 'text' => akari_store( 'payment' ) ),
	'お通し・席料'    => array( 'text' => akari_store( 'otoshi' ), 'full' => true ),
	'禁煙・喫煙'     => array( 'text' => akari_store( 'smoking' ) ),
	'電話番号'      => array(
		'html' => akari_store( 'tel' ) ? sprintf(
			'<a href="tel:%1$s" data-track="tel" data-location="body">%2$s</a>',
			esc_attr( akari_store( 'tel_link' ) ),
			esc_html( akari_store( 'tel' ) )
		) : '',
		'full' => true,
	),
);
?>
<div class="store-table">
	<table>
		<caption class="screen-reader-text">店舗情報</caption>
		<tbody>
			<?php foreach ( $akari_rows as $akari_head => $akari_row ) : ?>
				<?php
				if ( 'summary' === $akari_mode && ! empty( $akari_row['full'] ) ) {
					continue;
				}
				$akari_value = isset( $akari_row['html'] ) ? $akari_row['html'] : esc_html( (string) $akari_row['text'] );
				if ( '' === $akari_value ) {
					continue;
				}
				?>
				<tr>
					<th scope="row"><?php echo esc_html( $akari_head ); ?></th>
					<td><?php echo $akari_value; // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php echo akari_route_link( 'body' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
