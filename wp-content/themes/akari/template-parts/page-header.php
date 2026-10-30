<?php
/**
 * 下層ページの見出し：パンくず（U-15）、H1、アイキャッチ画像（最初の画面に入るので遅延読み込みにしない）。
 * 引数 title（省略時はページ名）、image（省略時はアイキャッチ）
 */

defined( 'ABSPATH' ) || exit;

$akari_title = $args['title'] ?? get_the_title();
$akari_image = $args['image'] ?? ( is_singular() ? (int) get_post_thumbnail_id() : 0 );

get_template_part( 'template-parts/breadcrumb' );
?>
<header class="page-header<?php echo $akari_image ? ' has-image' : ''; ?>">
	<?php if ( $akari_image ) : ?>
		<div class="page-header__img">
			<?php
			echo akari_img( // phpcs:ignore WordPress.Security.EscapeOutput
				$akari_image,
				'100vw',
				array(
					'lazy' => false,
					'high' => true,
					'size' => 'akari-w1600',
				)
			);
			?>
		</div>
	<?php endif; ?>
	<div class="page-header__body container">
		<h1 class="page-title"><?php echo esc_html( $akari_title ); ?></h1>
	</div>
</header>
