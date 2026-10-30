<?php
/**
 * ヘッダー（U-01〜U-03）と臨時休業の帯（U-12）。
 * SP（768px未満）：ロゴ・電話アイコン・メニュー。768px以上：電話番号とネット予約。1024px以上：ナビを展開。
 */

defined( 'ABSPATH' ) || exit;
$akari_nav = akari_nav_items();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="format-detection" content="telephone=no">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">本文へ移動</a>

<header class="site-header">
	<div class="site-header__inner container">
		<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( akari_store( 'name' ) ); ?> トップへ">
			<?php echo akari_icon( 'lamp' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="site-logo__text"><?php echo esc_html( akari_store( 'name' ) ); ?></span>
		</a>

		<?php if ( akari_store( 'instagram_url' ) ) : ?>
			<a class="header-insta" href="<?php echo esc_url( akari_store( 'instagram_url' ) ); ?>" target="_blank" rel="noopener" data-track="sns" data-location="header">
				<?php echo akari_icon( 'instagram' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="header-insta__label">Instagram</span>
				<span class="screen-reader-text">（新しいタブで開きます）</span>
			</a>
		<?php endif; ?>

		<?php if ( $akari_nav ) : ?>
			<nav class="global-nav" aria-label="メインメニュー">
				<ul>
					<?php foreach ( $akari_nav as $item ) : ?>
						<li><a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $item['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $item['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>

		<div class="header-contact">
			<?php if ( akari_store( 'tel' ) ) : ?>
				<a class="header-contact__tel" href="tel:<?php echo esc_attr( akari_store( 'tel_link' ) ); ?>" data-track="tel" data-location="header">
					<?php echo akari_icon( 'tel' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="header-contact__num"><?php echo esc_html( akari_store( 'tel' ) ); ?></span>
					<?php if ( akari_tel_note() ) : ?>
						<span class="header-contact__note"><?php echo esc_html( akari_tel_note() ); ?></span>
					<?php endif; ?>
				</a>
			<?php endif; ?>
			<?php echo akari_reserve_button( array( 'location' => 'header', 'class' => 'btn--sm' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<?php if ( akari_store( 'tel' ) ) : ?>
			<a class="header-icon header-icon--tel" href="tel:<?php echo esc_attr( akari_store( 'tel_link' ) ); ?>" data-track="tel" data-location="header">
				<?php echo akari_icon( 'tel' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="header-icon__label">電話</span>
			</a>
		<?php endif; ?>

		<?php /* JSが動かない環境ではフッターのナビへのページ内リンクとして働く。JSが動くとボタンになる。 */ ?>
		<a class="header-icon menu-toggle" href="#footer-nav" aria-controls="nav-panel" aria-expanded="false" data-menu-toggle>
			<?php echo akari_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="header-icon__label">メニュー</span>
		</a>
	</div>

	<div class="nav-panel" id="nav-panel" hidden data-menu-panel>
		<div class="nav-panel__inner container">
			<button type="button" class="nav-panel__close" data-menu-close>
				<?php echo akari_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span>閉じる</span>
			</button>
			<nav aria-label="メインメニュー（パネル）">
				<ul class="nav-panel__list">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"<?php echo is_front_page() ? ' aria-current="page"' : ''; ?>>トップ</a></li>
					<?php foreach ( $akari_nav as $item ) : ?>
						<li><a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $item['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $item['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<div class="nav-panel__cta">
				<?php echo akari_tel_button( array( 'location' => 'header' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( akari_tel_note() ) : ?>
					<p class="cta-note"><?php echo esc_html( akari_tel_note() ); ?></p>
				<?php endif; ?>
				<?php echo akari_reserve_button( array( 'location' => 'header' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<p class="nav-panel__hours">
				<?php
				echo esc_html(
					implode(
						'　',
						array_filter(
							array(
								akari_hours( 'lunch' ) ? '昼 ' . akari_hours( 'lunch' ) : '',
								akari_hours( 'dinner' ) ? '夜 ' . akari_hours( 'dinner' ) : '',
								akari_closed_label() ? akari_closed_label() . '定休' : '',
							)
						)
					)
				);
				?>
			</p>
		</div>
	</div>
</header>

<?php get_template_part( 'template-parts/notice-bar' ); ?>

<main id="main" class="site-main" tabindex="-1">
