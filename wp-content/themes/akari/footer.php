<?php
/**
 * フッター（C-03、F-09）とスマホの固定バー（U-04、F-03）。
 * 値はすべて共通設定から出す。Instagramへのリンクはヘッダー（ロゴの隣）とフッターに置く（2026-10-31 店主依頼でヘッダーにも追加）。
 */

defined( 'ABSPATH' ) || exit;
$akari_news_id = (int) get_option( 'page_for_posts' );
?>
</main>

<footer class="site-footer">
	<div class="site-footer__inner container">
		<div class="site-footer__info">
			<p class="site-footer__name"><?php echo esc_html( akari_store( 'name' ) ); ?></p>
			<address class="site-footer__address">
				<?php echo esc_html( akari_address() ); ?><br>
				<?php echo esc_html( akari_store( 'access' ) ); ?>
			</address>
			<dl class="site-footer__hours">
				<?php if ( akari_hours( 'lunch' ) ) : ?>
					<div><dt>昼</dt><dd><?php echo esc_html( akari_hours( 'lunch' ) ); ?></dd></div>
				<?php endif; ?>
				<?php if ( akari_hours( 'dinner' ) ) : ?>
					<div><dt>夜</dt><dd><?php echo esc_html( akari_hours( 'dinner' ) ); ?></dd></div>
				<?php endif; ?>
				<?php if ( akari_closed_label() ) : ?>
					<div><dt>定休日</dt><dd><?php echo esc_html( akari_closed_label() . '日' ); ?></dd></div>
				<?php endif; ?>
			</dl>
			<?php if ( akari_store( 'tel' ) ) : ?>
				<p class="site-footer__tel">
					<a href="tel:<?php echo esc_attr( akari_store( 'tel_link' ) ); ?>" data-track="tel" data-location="footer">
						<?php echo akari_icon( 'tel' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span><?php echo esc_html( akari_store( 'tel' ) ); ?></span>
					</a>
				</p>
			<?php endif; ?>
			<?php echo akari_route_link( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<div class="site-footer__links">
			<nav id="footer-nav" class="footer-nav" aria-label="フッターメニュー" tabindex="-1">
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">トップ</a></li>
					<?php foreach ( akari_nav_items() as $item ) : ?>
						<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
					<?php endforeach; ?>
					<?php if ( akari_page_url( 'privacy' ) ) : ?>
						<li><a href="<?php echo esc_url( akari_page_url( 'privacy' ) ); ?>">プライバシーポリシー</a></li>
					<?php endif; ?>
				</ul>
			</nav>
			<ul class="footer-external">
				<?php if ( akari_store( 'tabelog_url' ) ) : ?>
					<li><a href="<?php echo esc_url( akari_store( 'tabelog_url' ) ); ?>" target="_blank" rel="noopener" data-track="sns" data-location="footer">食べログ<span class="screen-reader-text">（新しいタブで開きます）</span></a></li>
				<?php endif; ?>
				<?php if ( akari_store( 'map_url' ) ) : ?>
					<li><a href="<?php echo esc_url( akari_store( 'map_url' ) ); ?>" target="_blank" rel="noopener" data-track="map" data-location="footer">Googleマップ<span class="screen-reader-text">（新しいタブで開きます）</span></a></li>
				<?php endif; ?>
				<?php if ( akari_store( 'instagram_url' ) ) : ?>
					<li><a href="<?php echo esc_url( akari_store( 'instagram_url' ) ); ?>" target="_blank" rel="noopener" data-track="sns" data-location="footer">Instagram（@<?php echo esc_html( basename( untrailingslashit( (string) wp_parse_url( akari_store( 'instagram_url' ), PHP_URL_PATH ) ) ) ); ?>）<span class="screen-reader-text">（新しいタブで開きます）</span></a></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
	<p class="site-footer__copy"><small>&copy; <?php echo esc_html( wp_date( 'Y' ) . ' ' . akari_store( 'name' ) ); ?></small></p>
</footer>

<?php get_template_part( 'template-parts/fixed-cta' ); ?>

<?php wp_footer(); ?>
</body>
</html>
