<?php get_header(); ?>
<div class="cp-shell">
	<header class="cp-section-head"><h1><?php post_type_archive_title(); ?></h1></header>
	<div class="cp-theme-grid"><?php while ( have_posts() ) : the_post(); cp_theme_video_card( get_the_ID() ); endwhile; ?></div>
	<?php the_posts_pagination(); ?>
</div>
<?php get_footer(); ?>
