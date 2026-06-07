<?php
/**
 * Template: Business Training — Single Video
 * Training module player.
 */
get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-business-content" style="background:#fff;border-radius:10px;overflow:hidden;">
		<?php while ( have_posts() ) : the_post();
			$post_id   = get_the_ID();
			$parent_id = get_post_meta( $post_id, '_cpwp_parent_item', true );
			$course    = $parent_id ? get_post( $parent_id ) : null;
		?>
		
		<div class="cp-business-video-header">
			<div style="flex:1;">
				<div class="cp-business-video-header__breadcrumb">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>"><?php esc_html_e( 'Library', 'cp-theme' ); ?></a> / 
					<?php if ( $course ) : ?>
					<a href="<?php echo esc_url( get_permalink( $course->ID ) ); ?>"><?php echo esc_html( $course->post_title ); ?></a> / 
					<?php endif; ?>
					<span><?php esc_html_e( 'Module', 'cp-theme' ); ?></span>
				</div>
				<h1 class="cp-business-video-header__title"><?php the_title(); ?></h1>
			</div>
			<div class="cp-business-meta-chip">
				<span>⏱</span> <?php echo esc_html( get_post_meta( $post_id, '_cpwp_duration', true ) ?: '15 mins' ); ?>
			</div>
		</div>

		<div class="cp-business-player-container">
			<?php 
			if ( class_exists( 'CPWP_Assets' ) ) {
				echo do_shortcode('[cp_player]');
			} else {
				echo '<div style="aspect-ratio:16/9;background:#000;display:flex;align-items:center;justify-content:center;color:#fff;">Player Placeholder</div>';
			}
			?>
		</div>

		<div class="cp-business-video-body">
			<div>
				<div class="cp-business-video-description">
					<?php the_content(); ?>
				</div>

				<div class="cp-business-acknowledgment-box" style="background:var(--cp-business-bg);padding:24px;border-radius:8px;margin-top:32px;display:flex;justify-content:space-between;align-items:center;">
					<div>
						<h3 style="margin:0 0 8px;font-size:1.1rem;"><?php esc_html_e( 'Module Completion', 'cp-theme' ); ?></h3>
						<p style="margin:0;font-size:0.9rem;color:var(--cp-business-text-muted);"><?php esc_html_e( 'Confirm that you have understood the materials presented in this module.', 'cp-theme' ); ?></p>
					</div>
					<button class="cp-business-btn cp-business-btn--primary cp-business-btn--lg"><?php esc_html_e( 'Mark as Completed', 'cp-theme' ); ?></button>
				</div>
			</div>

			<aside style="background:var(--cp-business-surface);border:1px solid var(--cp-business-border);border-radius:8px;padding:24px;">
				<h3 class="cp-business-section-title"><?php esc_html_e( 'Training Resources', 'cp-theme' ); ?></h3>
				<ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:12px;">
					<li><a href="#" class="cp-business-link" style="display:flex;align-items:center;gap:8px;">📄 <?php esc_html_e( 'Download Presentation PDF', 'cp-theme' ); ?></a></li>
					<li><a href="#" class="cp-business-link" style="display:flex;align-items:center;gap:8px;">📝 <?php esc_html_e( 'Policy Document', 'cp-theme' ); ?></a></li>
					<li><a href="#" class="cp-business-link" style="display:flex;align-items:center;gap:8px;">🔗 <?php esc_html_e( 'HR Portal Link', 'cp-theme' ); ?></a></li>
				</ul>

				<?php if ( $course ) : ?>
				<h3 class="cp-business-section-title" style="margin-top:32px;"><?php esc_html_e( 'Up Next in Course', 'cp-theme' ); ?></h3>
				<!-- list next lessons if any -->
				<a href="<?php echo esc_url( get_permalink( $course->ID ) ); ?>" class="cp-business-btn cp-business-btn--outline" style="width:100%;margin-top:16px;"><?php esc_html_e( 'Back to Course', 'cp-theme' ); ?></a>
				<?php endif; ?>
			</aside>
		</div>

		<?php endwhile; ?>
	</div>
</div>
<?php get_footer(); ?>
