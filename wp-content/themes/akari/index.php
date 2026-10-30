<?php
/**
 * 予備のテンプレート。通常は front-page.php・page-*.php・home.php・single.php が使われる。
 */

defined( 'ABSPATH' ) || exit;

if ( is_home() ) {
	require __DIR__ . '/home.php';
	return;
}

get_header();
?>
<div class="container container--narrow page-body">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<h1 class="page-title"><?php the_title(); ?></h1>
		<div class="entry-content"><?php the_content(); ?></div>
	<?php endwhile; ?>
</div>
<?php
get_footer();
