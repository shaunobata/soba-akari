<?php
/**
 * 画像の縮小・WebP化・Exifの削除・altの補完（F-14、詳細設計書9.2）。
 * 画像最適化プラグインは使わず、本体の機能で作る。
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		// srcset に使う幅。本文の写真 400/800/1200、メインビジュアル SP 720/1080/1440・PC 1200/1600/（原寸1920）。
		foreach ( array( 400, 720, 800, 1080, 1200, 1440, 1600 ) as $w ) {
			add_image_size( 'akari-w' . $w, $w, 0 );
		}
		add_image_size( 'akari-ogp', 1200, 630, true );
	}
);

// 使わない標準のサイズは作らない（店主のスマホ写真でディスクを使いすぎないため）。
add_filter(
	'intermediate_image_sizes_advanced',
	function ( array $sizes ) {
		unset( $sizes['medium_large'], $sizes['large'], $sizes['1536x1536'], $sizes['2048x2048'] );
		return $sizes;
	}
);

// 長辺1,920pxを超える画像は縮小する。
add_filter( 'big_image_size_threshold', fn() => 1920 );

// JPEG・PNG から WebP を作る（品質78）。
add_filter(
	'image_editor_output_format',
	function ( array $formats ) {
		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';
		return $formats;
	}
);
add_filter(
	'wp_editor_set_quality',
	fn( $quality, $mime ) => 'image/webp' === $mime ? 78 : $quality,
	10,
	2
);

/**
 * アップロードされたJPEGからExif（撮影位置の情報を含む）を消す。
 * 向きの情報は先に画素へ反映してから消す。
 */
add_filter(
	'wp_handle_upload',
	function ( array $upload ) {
		if ( empty( $upload['file'] ) || 'image/jpeg' !== ( $upload['type'] ?? '' ) ) {
			return $upload;
		}
		akari_strip_jpeg_metadata( $upload['file'] );
		return $upload;
	}
);

function akari_strip_jpeg_metadata( string $file ): void {
	if ( extension_loaded( 'imagick' ) ) {
		try {
			$im = new Imagick( $file );
			if ( method_exists( $im, 'autoOrient' ) ) {
				$im->autoOrient();
			}
			$im->stripImage();
			$im->setImageCompressionQuality( 90 );
			$im->writeImage( $file );
			$im->clear();
			return;
		} catch ( Exception $e ) {
			// GD で続ける
		}
	}
	if ( ! function_exists( 'imagecreatefromjpeg' ) ) {
		return;
	}
	$img = @imagecreatefromjpeg( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	if ( ! $img ) {
		return;
	}
	$orientation = 1;
	if ( function_exists( 'exif_read_data' ) ) {
		$exif        = @exif_read_data( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$orientation = (int) ( $exif['Orientation'] ?? 1 );
	}
	$rotate = array(
		3 => 180,
		6 => -90,
		8 => 90,
	);
	if ( isset( $rotate[ $orientation ] ) ) {
		$rotated = imagerotate( $img, $rotate[ $orientation ], 0 );
		if ( $rotated ) {
			imagedestroy( $img );
			$img = $rotated;
		}
	}
	imagejpeg( $img, $file, 90 ); // GD は書き出し時にメタデータを持たない
	imagedestroy( $img );
}

/**
 * 写真のaltが空なら「（品名・タイトル）の写真」を自動で入れる（月替わり・お知らせ・お品書き・コース）。
 */
add_action(
	'save_post',
	function ( int $post_id, WP_Post $post ) {
		if ( wp_is_post_revision( $post_id ) || ! in_array( $post->post_type, array( 'akari_monthly', 'post', 'akari_menu', 'akari_course' ), true ) ) {
			return;
		}
		$thumb_id = (int) get_post_thumbnail_id( $post_id );
		if ( ! $thumb_id || '' === trim( $post->post_title ) ) {
			return;
		}
		if ( '' === trim( (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) ) ) {
			update_post_meta( $thumb_id, '_wp_attachment_image_alt', mb_substr( $post->post_title, 0, 55 ) . 'の写真' );
		}
	},
	20,
	2
);
