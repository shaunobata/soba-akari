<?php
/**
 * コースカード（U-08）。
 * 引数 type：night／party、mode：full（夜・宴会ページ）／summary（トップのセクション3）、courses：取得済みのコース（省略可）
 * カード全体はリンクにしない。価格未定なら「価格はお電話でお問い合わせください」。
 */

defined( 'ABSPATH' ) || exit;

$akari_type    = ( $args['type'] ?? 'night' ) === 'party' ? 'party' : 'night';
$akari_mode    = ( $args['mode'] ?? 'full' ) === 'summary' ? 'summary' : 'full';
$akari_courses = $args['courses'] ?? akari_get_courses( $akari_type );

if ( 'summary' === $akari_mode ) {
	$akari_courses = array_slice( $akari_courses, 0, 2 );
}

if ( ! $akari_courses ) {
	if ( 'full' === $akari_mode ) {
		echo '<p class="course-empty">コースはお電話でご相談ください。</p>';
	}
	return;
}
?>
<div class="course-list course-list--<?php echo esc_attr( $akari_mode ); ?>"<?php echo ( 'night' === $akari_type && 'full' === $akari_mode ) ? ' id="course-list"' : ''; ?>>
	<?php foreach ( $akari_courses as $akari_course ) : ?>
		<?php
		$akari_id    = $akari_course->ID;
		$akari_price = akari_price_text( get_post_meta( $akari_id, 'akari_price', true ) );
		$akari_items = akari_course_items( $akari_id );
		?>
		<div class="course-list__item">
			<article class="course-card">
				<?php if ( 'full' === $akari_mode && has_post_thumbnail( $akari_id ) ) : ?>
					<div class="course-card__img ratio-4-3">
						<?php echo akari_img( (int) get_post_thumbnail_id( $akari_id ), '(min-width: 1024px) 560px, (min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				<?php endif; ?>
				<div class="course-card__body">
					<h3 class="course-card__name"><?php echo esc_html( get_the_title( $akari_course ) ); ?></h3>
					<p class="course-card__price<?php echo $akari_price ? '' : ' is-ask'; ?>"><?php echo esc_html( $akari_price ? $akari_price : '価格はお電話でお問い合わせください' ); ?></p>

					<?php if ( 'summary' === $akari_mode ) : ?>
						<?php
						$akari_summary = (string) get_post_meta( $akari_id, 'akari_summary', true );
						if ( '' === $akari_summary && $akari_items ) {
							$akari_summary = implode( '、', array_slice( $akari_items, 0, 3 ) ) . ( count( $akari_items ) > 3 ? ' ほか' : '' );
						}
						?>
						<?php if ( $akari_summary ) : ?>
							<p class="course-card__summary"><?php echo esc_html( $akari_summary ); ?></p>
						<?php endif; ?>
					<?php else : ?>
						<?php if ( $akari_items ) : ?>
							<ol class="course-card__items">
								<?php foreach ( $akari_items as $akari_item ) : ?>
									<li><?php echo esc_html( $akari_item ); ?></li>
								<?php endforeach; ?>
							</ol>
						<?php endif; ?>
						<?php
						$akari_details = array_filter(
							array(
								'ご予約の条件' => (string) get_post_meta( $akari_id, 'akari_conditions', true ),
								'飲み放題'   => 'party' === $akari_type ? (string) get_post_meta( $akari_id, 'akari_drink', true ) : '',
							)
						);
						?>
						<?php if ( $akari_details ) : ?>
							<dl class="course-card__details">
								<?php foreach ( $akari_details as $akari_term => $akari_desc ) : ?>
									<div><dt><?php echo esc_html( $akari_term ); ?></dt><dd><?php echo nl2br( esc_html( $akari_desc ) ); ?></dd></div>
								<?php endforeach; ?>
							</dl>
						<?php endif; ?>
						<?php if ( akari_store( 'otoshi' ) ) : ?>
							<p class="note">お通し・席料：<?php echo esc_html( akari_store( 'otoshi' ) ); ?></p>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</article>

			<?php
			if ( 'full' === $akari_mode ) {
				get_template_part(
					'template-parts/cta-buttons',
					null,
					'night' === $akari_type
						? array(
							'tel_label' => '電話で予約',
							'course'    => (string) get_post_meta( $akari_id, 'akari_course_id', true ),
						)
						: array(
							'tel_label' => '電話で相談する',
							'reserve'   => false,
						)
				);
			}
			?>
		</div>
	<?php endforeach; ?>
</div>
