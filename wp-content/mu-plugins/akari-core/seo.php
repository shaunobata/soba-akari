<?php
/**
 * title・description・canonical・robots・OGP・サイトマップ（F-11、詳細設計書6.5・6.6）。
 * SEOプラグインは使わず、ここから出す。
 */

defined( 'ABSPATH' ) || exit;

const AKARI_SITE_SUFFIX = '｜手打そば 燈り';

/** title・descriptionを持つ固定ページのID（お知らせ一覧なら投稿ページ）。なければ0。 */
function akari_seo_page_id(): int {
	if ( is_front_page() ) {
		return (int) get_option( 'page_on_front' );
	}
	if ( is_home() ) {
		return (int) get_option( 'page_for_posts' );
	}
	if ( is_page() ) {
		return (int) get_queried_object_id();
	}
	return 0;
}

function akari_seo_title(): string {
	if ( is_404() ) {
		return 'ページが見つかりません' . AKARI_SITE_SUFFIX;
	}
	if ( is_single() ) {
		return single_post_title( '', false ) . AKARI_SITE_SUFFIX;
	}
	if ( is_category() ) {
		return single_cat_title( '', false ) . 'のお知らせ' . AKARI_SITE_SUFFIX;
	}
	$page_id = akari_seo_page_id();
	if ( $page_id ) {
		$title = (string) get_post_meta( $page_id, 'akari_seo_title', true );
		$title = '' !== $title ? $title : get_the_title( $page_id ) . '｜手打そば 燈り 三鷹';
		if ( is_home() && get_query_var( 'paged' ) > 1 ) {
			$title .= '（' . (int) get_query_var( 'paged' ) . 'ページ目）';
		}
		return $title;
	}
	return akari_store( 'name' );
}

function akari_seo_description(): string {
	if ( is_404() ) {
		return '';
	}
	if ( is_single() ) {
		$post = get_queried_object();
		$text = has_excerpt( $post ) ? $post->post_excerpt : $post->post_content;
		$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( $text ) ) ) );
		return mb_strimwidth( $text, 0, 240, '…' ); // 全角で約120字
	}
	$page_id = akari_seo_page_id();
	$desc    = $page_id ? (string) get_post_meta( $page_id, 'akari_seo_desc', true ) : '';
	return '' !== $desc ? $desc : (string) akari_store( 'default_desc' );
}

/** 自分自身の絶対URL（https、末尾スラッシュあり、「?」以降は除く）。UTMつきもUTMなしにまとめる。 */
function akari_canonical_url(): string {
	if ( is_404() ) {
		return '';
	}
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_home() || is_category() ) {
		$paged = max( 1, (int) get_query_var( 'paged' ) );
		$base  = is_home() ? get_permalink( (int) get_option( 'page_for_posts' ) ) : get_category_link( get_queried_object_id() );
		return $paged > 1 ? trailingslashit( $base ) . 'page/' . $paged . '/' : $base;
	}
	if ( is_singular() ) {
		return get_permalink( get_queried_object_id() );
	}
	return '';
}

/** og:image にする添付ファイルのID。ページ別 → 記事の写真 → 既定の画像。 */
function akari_ogp_image_id(): int {
	$page_id = akari_seo_page_id();
	if ( $page_id && get_post_meta( $page_id, 'akari_ogp_image', true ) ) {
		return (int) get_post_meta( $page_id, 'akari_ogp_image', true );
	}
	if ( is_single() && has_post_thumbnail( get_queried_object_id() ) ) {
		return (int) get_post_thumbnail_id( get_queried_object_id() );
	}
	return (int) akari_store( 'default_ogp' );
}

/** noindexにするページか（6.6）。 */
function akari_is_noindex(): bool {
	if ( is_404() || is_category() || is_search() || is_author() || is_date() || is_tag() || is_attachment() ) {
		return true;
	}
	$page_id = akari_seo_page_id();
	return $page_id && get_post_meta( $page_id, 'akari_noindex', true );
}

add_filter( 'pre_get_document_title', 'akari_seo_title' );

add_filter(
	'wp_robots',
	function ( array $robots ) {
		if ( akari_is_noindex() ) {
			unset( $robots['max-image-preview'] );
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}
);

// 本体の canonical は使わず、UTMなどを除いた自前の値を出す。
remove_action( 'wp_head', 'rel_canonical' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

add_action(
	'wp_head',
	function () {
		$title     = akari_seo_title();
		$desc      = akari_seo_description();
		$canonical = akari_canonical_url();

		if ( '' !== $desc ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
		}
		if ( '' !== $canonical ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
		}
		if ( is_404() ) {
			return;
		}

		$og = array(
			'og:type'      => is_single() ? 'article' : 'website',
			'og:site_name' => akari_store( 'name' ),
			'og:locale'    => 'ja_JP',
			'og:title'     => $title,
			'og:description' => $desc,
			'og:url'       => $canonical,
		);
		$image_id = akari_ogp_image_id();
		if ( $image_id ) {
			// 1,200×630で用意したJPEGは原寸のまま使う（SNS側の対応を優先し、WebPにしない）。
			$src = wp_get_attachment_image_src( $image_id, 'full' );
			if ( $src && ( $src[1] > 1200 || $src[2] > 630 ) ) {
				$src = wp_get_attachment_image_src( $image_id, 'akari-ogp' );
			}
			if ( $src ) {
				$og['og:image']        = $src[0];
				$og['og:image:width']  = (string) $src[1];
				$og['og:image:height'] = (string) $src[2];
				$alt                   = (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true );
				if ( '' !== $alt ) {
					$og['og:image:alt'] = $alt;
				}
			}
		}
		foreach ( $og as $property => $content ) {
			if ( '' !== $content ) {
				printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $property ), esc_attr( $content ) );
			}
		}
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	},
	1
);

/* ------------------------------------------------------------
 * XMLサイトマップ（/wp-sitemap.xml）
 * noindexのページ、ユーザー、タグ、カテゴリ、月替わり・お品書き・コースは外す。
 * ------------------------------------------------------------ */

add_filter(
	'wp_sitemaps_add_provider',
	fn( $provider, string $name ) => 'users' === $name ? false : $provider,
	10,
	2
);

add_filter( 'wp_sitemaps_taxonomies', fn() => array() );

add_filter(
	'wp_sitemaps_post_types',
	fn( array $post_types ) => array_intersect_key( $post_types, array_flip( array( 'post', 'page' ) ) )
);

add_filter(
	'wp_sitemaps_posts_query_args',
	function ( array $args, string $post_type ) {
		if ( 'page' === $post_type ) {
			$args['post__not_in'] = get_posts(
				array(
					'post_type'      => 'page',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_key'       => 'akari_noindex', // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_value'     => 1, // phpcs:ignore WordPress.DB.SlowDBQuery
				)
			);
		}
		return $args;
	},
	10,
	2
);
