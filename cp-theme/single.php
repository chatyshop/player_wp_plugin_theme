<?php get_header(); ?>
<div class="cp-shell"><?php while ( have_posts() ) : the_post(); cp_theme_module_single(); endwhile; ?></div>
<?php get_footer(); ?>
