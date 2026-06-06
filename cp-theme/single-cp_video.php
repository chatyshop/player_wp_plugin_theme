<?php get_header(); ?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php 
	if ( is_user_logged_in() ) {
		get_template_part( 'sidebar', 'logged-in' );
	}
	?>
	<div class="cp-page-content">
		<?php while ( have_posts() ) : the_post(); ?>
		<article class="cp-article cp-video-layout">
			<div class="cp-content"><?php the_content(); ?></div>
			<header class="cp-article-header">
				<h1 class="cp-video-title"><?php the_title(); ?></h1>
			</header>
			<?php if ( comments_open() || get_comments_number() ) : comments_template(); endif; ?>
		</article>
		<?php endwhile; ?>
	</div>
</div>
<?php get_footer(); ?>
