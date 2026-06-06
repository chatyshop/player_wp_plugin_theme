<?php get_header(); ?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php 
	if ( is_user_logged_in() ) {
		get_template_part( 'sidebar', 'logged-in' );
	}
	?>
	<div class="cp-page-content">
		<header class="cp-section-head"><div><span class="cp-kicker"><?php esc_html_e( 'Category', 'cp-theme' ); ?></span><h1 class="cp-page-title"><?php single_cat_title(); ?></h1></div></header>
		<?php
		$query = new WP_Query( array( 'post_type' => 'cp_video', 'cat' => get_queried_object_id(), 'posts_per_page' => 12, 'paged' => max( 1, get_query_var( 'paged' ) ) ) );
		if ( $query->have_posts() ) : ?><div class="cp-theme-grid"><?php while ( $query->have_posts() ) : $query->the_post(); cp_theme_video_card( get_the_ID() ); endwhile; ?></div><?php else : ?><div class="cp-empty"><?php esc_html_e( 'No videos found in this category.', 'cp-theme' ); ?></div><?php endif; wp_reset_postdata();
		?>
		<?php $pagination = get_the_posts_pagination( array( 'mid_size' => 2 ) ); if ( $pagination ) echo $pagination; ?>
	</div>
</div>
<?php get_footer(); ?>
