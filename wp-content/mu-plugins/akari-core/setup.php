<?php
/**
 * 初回の設定（基本設計書2章・5章、詳細設計書2.2）。
 * 管理者が管理画面を開いたときに1回だけ、固定ページ・カテゴリ・パーマリンクなどを作る。
 * すでにあるページ（同じスラッグ）は作り直さず、値も上書きしない。
 * 本文・SEOの文は原稿（関門3a）の確定前の初期値で、確定後に管理画面で差し替える。
 */

defined( 'ABSPATH' ) || exit;

const AKARI_SETUP_VERSION = '1';

add_action(
	'admin_init',
	function () {
		if ( get_option( 'akari_setup_version' ) === AKARI_SETUP_VERSION || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		akari_run_setup();
		update_option( 'akari_setup_version', AKARI_SETUP_VERSION );
	}
);

function akari_run_setup(): void {
	// 基本の設定
	update_option( 'timezone_string', 'Asia/Tokyo' );
	update_option( 'date_format', 'Y年n月j日' );
	update_option( 'time_format', 'H:i' );
	update_option( 'start_of_week', 1 );
	update_option( 'posts_per_page', 10 );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	update_option( 'uploads_use_yearmonth_folders', 1 );

	// お知らせのカテゴリ（3つに固定）。「未分類」は「季節」に名前を替えて既定にする。
	$default = (int) get_option( 'default_category' );
	if ( $default ) {
		wp_update_term(
			$default,
			'category',
			array(
				'name' => '季節',
				'slug' => 'season',
			)
		);
	}
	foreach ( array( 'menu' => 'メニュー', 'closed' => '休業' ) as $slug => $name ) {
		if ( ! term_exists( $slug, 'category' ) ) {
			wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
	}

	// 固定ページ
	$ids = array();
	foreach ( akari_setup_pages() as $slug => $page ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			continue;
		}
		$ids[ $slug ] = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => $page['status'] ?? 'publish',
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_content' => $page['content'] ?? '',
				'menu_order'   => $page['order'] ?? 0,
				'meta_input'   => $page['meta'] ?? array(),
			)
		);
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['top'] );
	update_option( 'page_for_posts', $ids['news'] );
	update_option( 'wp_page_for_privacy_policy', $ids['privacy'] );

	// お知らせ詳細は /news/{投稿ID}/、カテゴリ別一覧は /news/category/{スラッグ}/
	update_option( 'permalink_structure', '/news/%post_id%/' );
	update_option( 'category_base', 'news/category' );
	update_option( 'tag_base', '' );
	flush_rewrite_rules();

	// 最初から入っているサンプルを消す
	foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page', 'privacy-policy' => 'page' ) as $slug => $type ) {
		$sample = get_page_by_path( $slug, OBJECT, $type );
		if ( $sample && $sample->ID !== ( $ids['privacy'] ?? 0 ) ) {
			wp_delete_post( $sample->ID, true );
		}
	}
}

/** 作る固定ページ。title・descriptionは基本設計書5章の案。 */
function akari_setup_pages(): array {
	return array(
		'top'     => array(
			'title' => 'トップ',
			'meta'  => array(
				'akari_seo_title'  => '【公式】手打そば 燈り｜三鷹駅北口の蕎麦店',
				'akari_seo_desc'   => '三鷹駅北口から徒歩5〜7分の手打そば店です。常陸秋そばを石臼で挽いた蕎麦に加え、夜は天ぷら・蕎麦前と日本酒・蕎麦焼酎をご用意しています。全24席で、貸切のご相談も承ります。水曜定休。',
				'akari_catch'      => '昼は手打ち蕎麦、夜は蕎麦屋で一献。', // D8の仮。昼夜の両方を出す案
				'akari_night_text' => '揚げたての天ぷらや蕎麦前を、日本酒・蕎麦焼酎と。締めは手打ちの蕎麦でどうぞ。',
				'akari_party_text' => 'ご家族やご友人とのお集まりから、会社のご宴会まで。手打ちの蕎麦とお料理を囲んで、ゆっくりとお過ごしください。ご宴会や貸切のご利用も、お気軽にご相談ください。',
				'akari_picks'      => array(
					array(
						'title' => '天ぷら・かき揚げ',
						'text'  => '揚げたての天ぷらとかき揚げを、お酒と一緒に。',
						'image' => 0,
					),
					array(
						'title' => '蕎麦前',
						'text'  => '蕎麦屋のつまみを少しずつ。締めの蕎麦までゆっくりと。',
						'image' => 0,
					),
					array(
						'title' => '日本酒・蕎麦焼酎',
						'text'  => 'つまみに合わせて選べる日本酒と蕎麦焼酎をご用意しています。',
						'image' => 0,
					),
				),
				'akari_about_text' => '常陸秋そばを石臼で挽き、店で手打ちしています。呑んだあとの締めまで、おいしく召し上がっていただくために。',
				'akari_lunch_text' => '常陸秋そばを石臼で挽いた手打ちの蕎麦を、お昼に気軽にどうぞ。',
			),
		),
		// 季節のおすすめ（2026-10-31 店主依頼）。今月の月替わりと、掲載期間中の季節限定を出す
		'seasonal' => array(
			'title'   => '季節のおすすめ',
			'order'   => 0,
			'content' => "<!-- wp:paragraph -->\n<p>今月の月替わりと、季節限定の品をご紹介します。価格はすべて税込です。</p>\n<!-- /wp:paragraph -->",
			'meta'    => array(
				'akari_seo_title' => '季節のおすすめ（月替わり・季節限定）｜手打そば 燈り 三鷹',
				'akari_seo_desc'  => '手打そば 燈り（三鷹駅北口）の季節のおすすめです。今月の月替わりと、季節限定の天ぷら・蕎麦・酒肴を税込価格でご紹介します。',
			),
		),
		'menu'    => array(
			'title'   => 'お品書き',
			'order'   => 1,
			'content' => "<!-- wp:paragraph -->\n<p>昼の蕎麦から、夜のつまみ・天ぷら、日本酒・蕎麦焼酎まで、燈りのお品書きをすべて税込価格で載せています。蕎麦は常陸秋そばを石臼で挽き、店で手打ちしています。</p>\n<!-- /wp:paragraph -->",
			'meta'    => array(
				'akari_seo_title' => 'お品書き（メニューと価格）｜手打そば 燈り 三鷹',
				'akari_seo_desc'  => '手打そば 燈り（三鷹駅北口）のお品書きです。昼の蕎麦、夜のつまみ・天ぷら、日本酒・蕎麦焼酎、今月の月替わりまで、すべての品を税込価格で載せています。',
			),
		),
		'night'   => array(
			'title'   => '夜の楽しみ方とコース',
			'order'   => 2,
			// コース（D1）が決まるまでは下書きで作る。公開すればナビ・各ページのリンクに自動で出る
			'status'  => 'draft',
			'content' => "<!-- wp:paragraph -->\n<p>燈りの夜は、天ぷら・かき揚げや蕎麦前（酒のつまみ）を日本酒・蕎麦焼酎と楽しみ、手打ちの蕎麦で締める時間です。お一人様も、ご夫婦でのお食事も、どうぞゆっくりお過ごしください。</p>\n<!-- /wp:paragraph -->",
			'meta'    => array(
				'akari_seo_title'  => '夜の楽しみ方とコース｜手打そば 燈り 三鷹',
				'akari_seo_desc'   => '手打そば 燈りの夜は、天ぷら・かき揚げや蕎麦前を日本酒と楽しみ、手打ち蕎麦で締める時間です。夜のコースと、お一人様・ご夫婦での楽しみ方をご案内します。',
				'akari_party_lead' => '4名様以上の会食・宴会、貸切のご相談は、店舗情報の「宴会・貸切のご相談」をご覧ください。',
			),
		),
		// 宴会・貸切のページは作らない（2026-10-31 店主依頼）。案内はトップの #party と店舗情報の #party に出す
		'access'  => array(
			'title'   => '店舗情報・アクセス',
			'order'   => 4,
			'content' => "<!-- wp:paragraph -->\n<p>[akari key=\"access\"]。夜は店先の灯りを目印にお越しください。</p>\n<!-- /wp:paragraph -->",
			'meta'    => array(
				'akari_seo_title' => '店舗情報・アクセス｜手打そば 燈り 三鷹駅北口',
				'akari_seo_desc'  => '手打そば 燈りの住所、営業時間、定休日、席数、三鷹駅北口からの道順と、宴会・貸切のご相談をご案内します。昼11:30〜14:30、夜17:30〜21:30、水曜定休、全席禁煙です。',
				'akari_faq'       => array(
					array(
						'q' => 'お支払い方法を教えてください。',
						'a' => '',
					),
					array(
						'q' => '領収書は出してもらえますか。',
						'a' => '',
					),
					array(
						'q' => '予約はいつまでにすればよいですか。',
						'a' => '',
					),
					array(
						'q' => 'キャンセルや人数の変更はできますか。',
						'a' => '',
					),
					array(
						'q' => 'たばこは吸えますか。',
						'a' => '全席禁煙です。ご理解をお願いいたします。',
					),
				),
			),
		),
		'news'    => array(
			'title' => 'お知らせ',
			'order' => 5,
			'meta'  => array(
				'akari_seo_title' => 'お知らせ｜手打そば 燈り 三鷹',
				'akari_seo_desc'  => '手打そば 燈り（三鷹駅北口）からのお知らせです。今月の月替わり、季節のメニュー、臨時休業などをお知らせしています。ご来店前にご覧ください。',
			),
		),
		'about'   => array(
			'title'   => '蕎麦へのこだわり',
			'order'   => 6,
			'content' => "<!-- wp:paragraph -->\n<p>燈りの蕎麦は、常陸秋そばを石臼で挽き、店で手打ちしています。お昼に気軽に食べていただく一枚も、夜にお酒を楽しんだあとの締めの一枚も、手打ちでご用意しています。</p>\n<!-- /wp:paragraph -->",
			'meta'    => array(
				'akari_seo_title'  => '蕎麦へのこだわり｜手打そば 燈り 三鷹',
				'akari_seo_desc'   => '手打そば 燈りの蕎麦は、常陸秋そばを石臼で挽き、手で打っています。昼の蕎麦と夜の締めの蕎麦、蕎麦を打つ手元の写真と店主の想いをご紹介します。',
				'akari_flour_text' => '蕎麦粉には、茨城県で育つ品種「常陸秋そば」を使っています。石臼で挽いた粉を、店で手打ちしています。',
				'akari_daynight'   => array(
					array(
						'title' => '昼の蕎麦',
						'text'  => 'お昼は、手打ちの蕎麦を気軽に。お仕事の合間にも、ゆっくりのお食事にもどうぞ。',
						'image' => 0,
					),
					array(
						'title' => '夜の締めの蕎麦',
						'text'  => '天ぷらや蕎麦前と一緒にお酒を楽しんだあとは、手打ちの蕎麦で締めを。',
						'image' => 0,
					),
				),
			),
		),
		'review'  => array(
			'title'   => 'ご来店ありがとうございました',
			'order'   => 7,
			'content' => "<!-- wp:paragraph -->\n<p>本日はご来店いただき、ありがとうございました。よろしければ、Googleマップへご感想をお寄せください。</p>\n<!-- /wp:paragraph -->",
			'meta'    => array(
				'akari_seo_title' => '口コミのお願い｜手打そば 燈り',
				'akari_seo_desc'  => 'ご来店ありがとうございました。よろしければ、Googleマップへご感想をお寄せください。',
				'akari_noindex'   => 1,
			),
		),
		'privacy' => array(
			'title'   => 'プライバシーポリシー',
			'order'   => 8,
			'content' => akari_setup_privacy_content(),
			'meta'    => array(
				'akari_seo_title' => 'プライバシーポリシー｜手打そば 燈り',
				'akari_seo_desc'  => '手打そば 燈り公式サイトで利用しているアクセス解析ツール（Googleアナリティクス）と、取得する情報についてご説明します。',
			),
		),
	);
}

function akari_setup_privacy_content(): string {
	$blocks = array(
		array( 'p', '手打そば 燈り（以下「当店」）は、当店の公式サイト（以下「本サイト」）での個人に関する情報の扱いを、次のとおり定めます。' ),
		array( 'h2', 'アクセス解析ツールについて' ),
		array( 'p', '本サイトでは、より使いやすいサイトにするため、Google LLCのアクセス解析ツール「Googleアナリティクス」と、その設定に使う「Googleタグマネージャー」を利用しています。Googleアナリティクスは、Cookieを使って、閲覧したページ、ボタンを押した回数、おおよその地域、端末やブラウザの種類などの情報を集めます。これらの情報に、お名前など個人を特定する情報は含まれません。' ),
		array( 'p', '集めた情報はGoogleのプライバシーポリシーに基づいて管理されます。ブラウザの設定でCookieを無効にすると、情報の収集を止めることができます。また、Googleが提供する「Googleアナリティクス オプトアウト アドオン」を使って止めることもできます。' ),
		array( 'h2', '地図の表示について' ),
		array( 'p', '店舗情報・アクセスのページでは、Googleマップを埋め込んで表示しています。地図を表示すると、Googleに閲覧の情報が送られることがあります。' ),
		array( 'h2', '外部サイトへのリンクについて' ),
		array( 'p', '本サイトのネット予約は、食べログの予約ページで受け付けています。リンク先のサイトでの情報の扱いは、それぞれのサイトのプライバシーポリシーをご確認ください。' ),
		array( 'h2', 'お問い合わせ' ),
		array( 'p', 'このポリシーについてのお問い合わせは、お電話（[akari key="tel"]）で承ります。' ),
		array( 'h2', '改定について' ),
		array( 'p', '当店は、必要に応じてこのポリシーを改定します。改定した内容は本サイトに掲載した時点から有効になります。' ),
		array( 'p', '制定日：2026年12月4日' ),
	);
	$out = array();
	foreach ( $blocks as $block ) {
		list( $tag, $text ) = $block;
		$name  = 'h2' === $tag ? 'heading' : 'paragraph';
		$out[] = "<!-- wp:{$name} -->\n<{$tag}>{$text}</{$tag}>\n<!-- /wp:{$name} -->";
	}
	return implode( "\n\n", $out );
}
