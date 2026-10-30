<?php
/**
 * 投稿タイプ（詳細設計書3.1）。
 * 月替わり・お品書き・コースは単独のページを持たず、各ページのテンプレートから呼び出すだけ。
 * 標準の「投稿」は「お知らせ」に名前を替え、入力欄をタイトル・本文・写真・カテゴリに絞る。
 */

defined( 'ABSPATH' ) || exit;

/** 区分・提供・種別の選択肢。表示名はここ1か所で持つ。 */
function akari_choices( string $set ): array {
	$sets = array(
		'serve'       => array(
			'lunch' => '昼',
			'night' => '夜',
			'both'  => '昼夜',
		),
		'menu_cat'    => array(
			'soba'     => '昼の蕎麦',
			'otsumami' => '夜のつまみ・天ぷら',
			'sake'     => '日本酒・蕎麦焼酎',
		),
		'course_type' => array(
			'night' => '夜',
			'party' => '宴会',
		),
	);
	return $sets[ $set ] ?? array();
}

add_action(
	'init',
	function () {
		$common = array(
			'public'              => false,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'rewrite'             => false,
			'query_var'           => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'map_meta_cap'        => true,
		);

		register_post_type(
			'akari_monthly',
			$common + array(
				'labels'          => akari_post_type_labels( '月替わり', '品' ),
				'menu_icon'       => 'dashicons-carrot',
				'menu_position'   => 4,
				'supports'        => array( 'title', 'thumbnail' ),
				'capability_type' => array( 'akari_monthly', 'akari_monthlies' ),
			)
		);

		register_post_type(
			'akari_menu',
			$common + array(
				'labels'          => akari_post_type_labels( 'お品書き', '品' ),
				'menu_icon'       => 'dashicons-list-view',
				'menu_position'   => 21,
				'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
				'capability_type' => array( 'akari_menu_item', 'akari_menu_items' ),
			)
		);

		register_post_type(
			'akari_course',
			$common + array(
				'labels'          => akari_post_type_labels( 'コース', 'コース' ),
				'menu_icon'       => 'dashicons-food',
				'menu_position'   => 22,
				'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
				'capability_type' => array( 'akari_course', 'akari_courses' ),
			)
		);

		// 投稿 → お知らせ。コメント・抜粋・フォーマット・タグは使わない。
		foreach ( array( 'comments', 'trackbacks', 'excerpt', 'post-formats', 'custom-fields', 'author' ) as $feature ) {
			remove_post_type_support( 'post', $feature );
		}
		unregister_taxonomy_for_object_type( 'post_tag', 'post' );
	}
);

function akari_post_type_labels( string $name, string $unit ): array {
	return array(
		'name'               => $name,
		'singular_name'      => $name,
		'menu_name'          => $name,
		'add_new'            => '新しく追加',
		'add_new_item'       => $name . 'を追加',
		'edit_item'          => $name . 'を編集',
		'new_item'           => '新しい' . $unit,
		'search_items'       => $name . 'を探す',
		'not_found'          => 'まだありません',
		'not_found_in_trash' => 'ゴミ箱は空です',
		'all_items'          => $name . 'の一覧',
	);
}

// 「投稿」の表示名を「お知らせ」に替える。
add_filter(
	'post_type_labels_post',
	function ( $labels ) {
		$map = array(
			'name'          => 'お知らせ',
			'singular_name' => 'お知らせ',
			'menu_name'     => 'お知らせ',
			'name_admin_bar' => 'お知らせ',
			'add_new'       => 'お知らせを書く',
			'add_new_item'  => 'お知らせを書く',
			'edit_item'     => 'お知らせを編集',
			'all_items'     => 'お知らせの一覧',
			'view_item'     => 'お知らせを見る',
			'search_items'  => 'お知らせを探す',
			'not_found'     => 'お知らせはまだありません',
		);
		foreach ( $map as $key => $value ) {
			$labels->$key = $value;
		}
		return $labels;
	}
);

// お知らせはブロックエディタを使わず、太字・リンクだけの簡易エディタにする（固定ページはブロックエディタのまま）。
add_filter(
	'use_block_editor_for_post_type',
	fn( $use, $post_type ) => 'post' === $post_type ? false : $use,
	10,
	2
);

add_filter(
	'wp_editor_settings',
	function ( $settings, $editor_id ) {
		if ( 'content' === $editor_id && 'post' === get_current_screen()?->post_type ) {
			$settings['teeny']         = true;
			$settings['media_buttons'] = false;
			$settings['quicktags']     = false;
			$settings['textarea_rows'] = 12;
		}
		return $settings;
	},
	10,
	2
);

add_filter( 'teeny_mce_buttons', fn() => array( 'bold', 'link', 'unlink', 'undo', 'redo' ) );

/* ------------------------------------------------------------
 * お知らせのカテゴリを1つだけ選ぶ（ラジオボタン）
 * ------------------------------------------------------------ */

add_action(
	'add_meta_boxes_post',
	function () {
		remove_meta_box( 'categorydiv', 'post', 'side' );
		add_meta_box( 'akari-category', 'カテゴリ', 'akari_category_radio_box', 'post', 'side', 'high' );
	}
);

function akari_category_radio_box( WP_Post $post ): void {
	$current = wp_get_post_categories( $post->ID );
	$current = $current ? (int) $current[0] : (int) get_option( 'default_category' );
	$terms   = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
		)
	);
	foreach ( $terms as $term ) {
		printf(
			'<p><label><input type="radio" name="post_category[]" value="%1$d" %2$s> %3$s</label></p>',
			(int) $term->term_id,
			checked( $current, $term->term_id, false ),
			esc_html( $term->name )
		);
	}
}
