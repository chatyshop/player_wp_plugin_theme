<?php get_header(); ?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php 
	if ( is_user_logged_in() ) {
		get_template_part( 'sidebar', 'logged-in' );
	}
	?>
	<div class="cp-page-content">
		<header class="cp-section-head"><div><span class="cp-kicker"><?php esc_html_e( 'Search', 'cp-theme' ); ?></span><h1 class="cp-page-title"><?php printf( esc_html__( 'Results for "%s"', 'cp-theme' ), esc_html( get_search_query() ) ); ?></h1></div></header>
		<?php if ( have_posts() ) : ?><div class="cp-theme-grid"><?php while ( have_posts() ) : the_post(); cp_theme_video_card( get_the_ID() ); endwhile; ?></div><?php the_posts_pagination(); else : ?><div class="cp-empty"><?php esc_html_e( 'No matching videos found.', 'cp-theme' ); ?></div><?php endif; ?>
	</div>
</div>
<?php get_footer(); ?>
