<?php
/**
 * 店主ロール「店主（燈り）」（詳細設計書4.1）。
 * ロールはデータベースに保存されるため、akari_role_version が変わったときだけ作り直す。
 */

defined( 'ABSPATH' ) || exit;

const AKARI_ROLE_VERSION = '2'; // 2：季節限定の権限を追加

/** 投稿タイプの capability_type（複数形）から、編集に要る権限の一覧を作る。 */
function akari_post_type_caps( string $plural ): array {
	return array(
		"edit_{$plural}",
		"edit_others_{$plural}",
		"edit_published_{$plural}",
		"edit_private_{$plural}",
		"publish_{$plural}",
		"read_private_{$plural}",
		"delete_{$plural}",
		"delete_others_{$plural}",
		"delete_published_{$plural}",
		"delete_private_{$plural}",
	);
}

add_action(
	'init',
	function () {
		if ( get_option( 'akari_role_version' ) === AKARI_ROLE_VERSION ) {
			return;
		}

		remove_role( 'akari_owner' );
		add_role(
			'akari_owner',
			'店主（燈り）',
			array(
				'read'                             => true,
				'upload_files'                     => true,
				'edit_posts'                       => true,
				'edit_published_posts'             => true,
				'publish_posts'                    => true,
				'delete_posts'                     => true,
				'delete_published_posts'           => true,
				'edit_others_posts'                => true,
				'edit_akari_monthlies'             => true,
				'edit_published_akari_monthlies'   => true,
				'publish_akari_monthlies'          => true,
				'delete_akari_monthlies'           => true,
				'delete_published_akari_monthlies' => true,
				'edit_others_akari_monthlies'      => true,
				'edit_akari_seasonals'             => true,
				'edit_published_akari_seasonals'   => true,
				'publish_akari_seasonals'          => true,
				'delete_akari_seasonals'           => true,
				'delete_published_akari_seasonals' => true,
				'edit_others_akari_seasonals'      => true,
				'edit_akari_notice'                => true,
			)
		);

		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$caps = array_merge(
				akari_post_type_caps( 'akari_monthlies' ),
				akari_post_type_caps( 'akari_seasonals' ),
				akari_post_type_caps( 'akari_menu_items' ),
				akari_post_type_caps( 'akari_courses' ),
				array( 'edit_akari_notice', 'edit_akari_store' )
			);
			foreach ( $caps as $cap ) {
				$admin->add_cap( $cap );
			}
		}

		update_option( 'akari_role_version', AKARI_ROLE_VERSION );
	},
	5
);

/** いまのユーザーが店主か。 */
function akari_is_owner(): bool {
	$user = wp_get_current_user();
	return $user->exists() && in_array( 'akari_owner', (array) $user->roles, true );
}
