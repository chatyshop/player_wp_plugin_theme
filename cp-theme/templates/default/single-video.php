<?php
/**
 * Template: Default — Single Video Page
 * Generic video page used by all non-streaming site types.
 */
get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>
	<div class="cp-page-content">
		<?php while ( have_posts() ) : the_post(); ?>
		<article class="cp-article cp-video-layout">
			<div class="cp-content"><?php the_content(); ?></div>
			<header class="cp-article-header">
				<h1 class="cp-video-title"><?php the_title(); ?></h1>
			</header>
			<div class="cp-video-details-wrap">
				<?php cp_theme_video_details( get_the_ID() ); ?>
				<?php if ( is_user_logged_in() ) : ?>
				<div class="cp-video-report-actions">
					<button class="cp-button" data-cpwp-report="content" data-target-id="<?php the_ID(); ?>" aria-label="<?php esc_attr_e( 'Report content', 'cp-theme' ); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
						<?php esc_html_e( 'Report content', 'cp-theme' ); ?>
					</button>
					<button class="cp-button" data-cpwp-report="copyright" data-target-id="<?php the_ID(); ?>" aria-label="<?php esc_attr_e( 'Copyright claim', 'cp-theme' ); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M14.83 14.83a4 4 0 1 1 0-5.66"/></svg>
						<?php esc_html_e( 'Copyright claim', 'cp-theme' ); ?>
					</button>
				</div>
				<?php endif; ?>
			</div>
			<div class="cp-video-comments-wrap">
				<?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
			</div>
			<aside class="cp-video-sidebar">
				<?php cp_theme_video_section( 'Up next', array( 'post__not_in' => array( get_the_ID() ), 'orderby' => 'rand' ) ); ?>
			</aside>
		</article>
		<?php endwhile; ?>
	</div>
</div>
<?php get_footer(); ?>
