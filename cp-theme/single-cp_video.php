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
			<?php cp_theme_video_details( get_the_ID() ); ?>
			<?php if ( is_user_logged_in() ) : ?><p><button class="cp-button" data-cpwp-report="content" data-target-id="<?php the_ID(); ?>">Report content</button> <button class="cp-button" data-cpwp-report="copyright" data-target-id="<?php the_ID(); ?>">Copyright claim</button></p><?php endif; ?>
			<?php if ( comments_open() || get_comments_number() ) : comments_template(); endif; ?>
		</article>
		<?php endwhile; ?>
	</div>
</div>
<?php get_footer(); ?>
