<?php
/**
 * トップ（P-01、基本設計書3-2）。
 * セクション0（臨時休業の帯）は header.php。1〜10をこの順で組む。
 * 本文中の予約ボタンはセクション1・2・9の3か所だけ。条件を満たさない3・5・6・7・10は出さない。
 */

defined( 'ABSPATH' ) || exit;

get_header();

$akari_front   = (int) get_option( 'page_on_front' );
$akari_mv      = akari_main_visual_ids();
$akari_catch   = (string) akari_field( 'akari_catch', $akari_front );
$akari_menu    = akari_page_url( 'menu' );
$akari_night   = akari_page_url( 'night' );
$akari_party   = akari_page_url( 'party' );
$akari_courses = akari_get_courses( 'night' );
$akari_monthly = akari_get_monthly( 1 );
$akari_picks   = akari_rows( 'akari_picks', 'title', $akari_front );
$akari_voices  = akari_rows( 'akari_voices', 'quote', $akari_front );
$akari_news    = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'no_found_rows'  => true,
	)
);
$akari_hours_line = implode(
	'／',
	array_filter(
		array(
			akari_hours( 'lunch' ) ? '昼 ' . akari_hours( 'lunch' ) : '',
			akari_hours( 'dinner' ) ? '夜 ' . akari_hours( 'dinner' ) : '',
		)
	)
) . ( akari_closed_label() ? '（' . akari_closed_label() . '定休）' : '' );
?>

<?php /* 1 メインビジュアル */ ?>
<section class="hero<?php echo $akari_mv['sp'] ? ' has-image' : ''; ?>">
	<?php if ( $akari_mv['sp'] ) : ?>
		<picture class="hero__media">
			<source media="(min-width: 768px)" srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $akari_mv['pc'], 'full' ) ); ?>" sizes="100vw">
			<?php
			echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput
				$akari_mv['sp'],
				'akari-w1080',
				false,
				array(
					'sizes'         => '100vw',
					'loading'       => false,
					'fetchpriority' => 'high',
					'decoding'      => 'async',
					'class'         => 'hero__img',
				)
			);
			?>
		</picture>
	<?php endif; ?>
	<div class="hero__body container">
		<h1 class="hero__title"><?php echo esc_html( akari_store( 'name' ) ); ?><span>三鷹駅北口の蕎麦屋</span></h1>
		<?php if ( $akari_catch ) : ?>
			<p class="hero__catch"><?php echo esc_html( $akari_catch ); ?></p>
		<?php endif; ?>
		<?php if ( $akari_hours_line ) : ?>
			<p class="hero__hours"><?php echo esc_html( $akari_hours_line ); ?></p>
		<?php endif; ?>
		<?php get_template_part( 'template-parts/cta-buttons' ); ?>
	</div>
</section>

<?php /* 2 宴会・会食・貸切 */ ?>
<section class="section section--party" aria-labelledby="top-party">
	<div class="container split">
		<?php $akari_party_img = (int) akari_field( 'akari_party_img', $akari_front ); ?>
		<?php if ( $akari_party_img ) : ?>
			<div class="split__media ratio-4-3">
				<?php echo akari_img( $akari_party_img, '(min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		<?php endif; ?>
		<div class="split__body">
			<p class="section-eyebrow">会食・宴会の幹事様へ</p>
			<h2 class="section-title" id="top-party">宴会・会食・貸切</h2>
			<ul class="fact-list">
				<?php if ( akari_store( 'seats_total' ) ) : ?>
					<li><span class="fact-list__num"><?php echo esc_html( (string) akari_store( 'seats_total' ) ); ?></span>席</li>
				<?php endif; ?>
				<?php if ( akari_store( 'seats_layout' ) ) : ?>
					<li><?php echo esc_html( akari_store( 'seats_layout' ) ); ?></li>
				<?php endif; ?>
				<?php if ( akari_store( 'private_hire' ) ) : ?>
					<li><?php echo esc_html( akari_store( 'private_hire' ) ); ?></li>
				<?php endif; ?>
				<?php if ( akari_store( 'smoking' ) ) : ?>
					<li><?php echo esc_html( akari_store( 'smoking' ) ); ?></li>
				<?php endif; ?>
			</ul>
			<?php echo akari_text( (string) akari_field( 'akari_party_text', $akari_front ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $akari_party ) : ?>
				<p class="section-more"><?php echo akari_more_link( $akari_party, '宴会・貸切のご案内を見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
			<?php
			get_template_part(
				'template-parts/cta-buttons',
				null,
				array(
					'tel_label' => '電話で相談する',
					'reserve'   => false,
				)
			);
			?>
		</div>
	</div>
</section>

<?php /* 3 夜のコース（D1未定でコースがなければ出さない） */ ?>
<?php if ( $akari_courses ) : ?>
	<section class="section section--night" aria-labelledby="top-course">
		<div class="container">
			<h2 class="section-title" id="top-course">夜のコース</h2>
			<?php
			get_template_part(
				'template-parts/course-cards',
				null,
				array(
					'type'    => 'night',
					'mode'    => 'summary',
					'courses' => $akari_courses,
				)
			);
			?>
			<?php if ( $akari_night ) : ?>
				<p class="section-more"><?php echo akari_more_link( $akari_night, '夜の楽しみ方を見る', 'more-link--on-night' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php /* 4 夜のおすすめ3品 */ ?>
<?php if ( $akari_picks ) : ?>
	<section class="section" aria-labelledby="top-picks">
		<div class="container">
			<h2 class="section-title" id="top-picks">夜のおすすめ</h2>
			<ul class="pick-list">
				<?php foreach ( $akari_picks as $akari_pick ) : ?>
					<li class="pick">
						<?php if ( ! empty( $akari_pick['image'] ) ) : ?>
							<div class="pick__img ratio-4-3">
								<?php echo akari_img( (int) $akari_pick['image'], '(min-width: 1024px) 33vw, (min-width: 768px) 33vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</div>
						<?php endif; ?>
						<h3 class="pick__title"><?php echo esc_html( $akari_pick['title'] ); ?></h3>
						<?php if ( ! empty( $akari_pick['text'] ) ) : ?>
							<p class="pick__text"><?php echo esc_html( $akari_pick['text'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $akari_menu ) : ?>
				<p class="section-more"><?php echo akari_more_link( $akari_menu, 'お品書きを見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php /* 5 今月の月替わり（0件なら出さない） */ ?>
<?php if ( $akari_monthly ) : ?>
	<section class="section section--surface" aria-labelledby="top-monthly">
		<div class="container">
			<h2 class="section-title" id="top-monthly">今月の月替わり</h2>
			<?php
			get_template_part(
				'template-parts/monthly-list',
				null,
				array(
					'mode'  => 'top',
					'items' => $akari_monthly,
				)
			);
			?>
			<?php if ( $akari_menu ) : ?>
				<p class="section-more"><?php echo akari_more_link( $akari_menu . '#monthly', 'お品書きを見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php /* 6 蕎麦へのこだわり（素材がなければ出さない） */ ?>
<?php
$akari_about_text = (string) akari_field( 'akari_about_text', $akari_front );
$akari_about_img  = (int) akari_field( 'akari_about_img', $akari_front );
?>
<?php if ( $akari_about_text || $akari_about_img ) : ?>
	<section class="section" aria-labelledby="top-about">
		<div class="container split split--reverse">
			<?php if ( $akari_about_img ) : ?>
				<div class="split__media ratio-4-3">
					<?php echo akari_img( $akari_about_img, '(min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			<?php endif; ?>
			<div class="split__body">
				<h2 class="section-title" id="top-about">蕎麦へのこだわり</h2>
				<?php echo akari_text( $akari_about_text ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( akari_page_url( 'about' ) ) : ?>
					<p class="section-more"><?php echo akari_more_link( akari_page_url( 'about' ), '蕎麦へのこだわりを見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php /* 7 お客様の声（0件なら出さない） */ ?>
<?php if ( $akari_voices ) : ?>
	<section class="section section--surface" aria-labelledby="top-voice">
		<div class="container">
			<h2 class="section-title" id="top-voice">お客様の声</h2>
			<?php get_template_part( 'template-parts/voice-list', null, array( 'voices' => $akari_voices ) ); ?>
		</div>
	</section>
<?php endif; ?>

<?php /* 8 昼の燈り */ ?>
<section class="section" aria-labelledby="top-lunch">
	<div class="container split">
		<?php $akari_lunch_img = (int) akari_field( 'akari_lunch_img', $akari_front ); ?>
		<?php if ( $akari_lunch_img ) : ?>
			<div class="split__media ratio-4-3">
				<?php echo akari_img( $akari_lunch_img, '(min-width: 768px) 50vw, 100vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		<?php endif; ?>
		<div class="split__body">
			<h2 class="section-title" id="top-lunch">昼の燈り</h2>
			<?php if ( akari_hours( 'lunch' ) ) : ?>
				<p class="lunch-hours">昼の営業 <span class="num"><?php echo esc_html( akari_hours( 'lunch' ) ); ?></span></p>
			<?php endif; ?>
			<?php echo akari_text( (string) akari_field( 'akari_lunch_text', $akari_front ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $akari_menu ) : ?>
				<p class="section-more"><?php echo akari_more_link( $akari_menu . '#soba', 'お品書きを見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php /* 9 店舗情報とご予約の方法 */ ?>
<section class="section section--surface" aria-labelledby="top-store">
	<div class="container">
		<h2 class="section-title" id="top-store">店舗情報</h2>
		<div class="store-block">
			<?php get_template_part( 'template-parts/store-table', null, array( 'mode' => 'summary' ) ); ?>
			<?php get_template_part( 'template-parts/reserve-guide', null, array( 'level' => 3 ) ); ?>
		</div>
		<?php if ( akari_page_url( 'access' ) ) : ?>
			<p class="section-more"><?php echo akari_more_link( akari_page_url( 'access' ), '店舗情報・アクセスを見る' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		<?php endif; ?>
	</div>
</section>

<?php /* 10 お知らせ（0件なら出さない） */ ?>
<?php if ( $akari_news ) : ?>
	<section class="section" aria-labelledby="top-news">
		<div class="container container--narrow">
			<h2 class="section-title" id="top-news">お知らせ</h2>
			<?php get_template_part( 'template-parts/news-list', null, array( 'posts' => $akari_news ) ); ?>
			<p class="section-more"><?php echo akari_more_link( get_permalink( (int) get_option( 'page_for_posts' ) ), 'お知らせ一覧へ' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		</div>
	</section>
<?php endif; ?>

<?php
get_footer();
