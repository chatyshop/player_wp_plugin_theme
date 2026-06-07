<?php
/**
 * Template: Membership — Archive
 * Browse all exclusive content with lock badges.
 */

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$args  = array(
	'post_type'      => 'cp_video',
	'posts_per_page' => 24,
	'paged'          => $paged,
);
$query = new WP_Query( $args );

get_header();

$user_id = get_current_user_id();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-member-content">
		
		<div class="cp-member-archive-head">
			<h1><?php esc_html_e( 'All Premium Content', 'cp-theme' ); ?></h1>
			<p><?php esc_html_e( 'Browse all exclusive videos available to our members.', 'cp-theme' ); ?></p>
		</div>

		<?php if ( $query->have_posts() ) : ?>
		<div class="cp-member-video-grid">
			<?php while ( $query->have_posts() ) : $query->the_post();
				$vid_id    = get_the_ID();
				$thumb     = get_the_post_thumbnail_url( $vid_id, 'medium_large' );
				$req_group = absint( get_post_meta( $vid_id, '_cpwp_community_group', true ) );
				$locked    = false;
				
				if ( $req_group && class_exists( 'CPWP_Community' ) ) {
					$locked = ! CPWP_Community::is_member( $req_group, $user_id ) && ! current_user_can( 'manage_options' );
				}
			?>
			<article class="cp-member-card <?php echo $locked ? 'is-locked' : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $vid_id ) ); ?>" class="cp-member-card-thumb">
					<?php if ( $thumb ) : ?>
					<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
					<?php else : ?>
					<div class="cp-member-card-placeholder">▶</div>
					<?php endif; ?>
					
					<?php if ( $locked ) : ?>
					<div class="cp-member-lock-overlay">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
						<span><?php esc_html_e( 'Members Only', 'cp-theme' ); ?></span>
					</div>
					<?php endif; ?>
				</a>
				<div class="cp-member-card-body">
					<h3><a href="<?php echo esc_url( get_permalink( $vid_id ) ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h3>
					<div class="cp-member-card-meta">
						<span class="cp-member-date"><?php echo esc_html( get_the_date() ); ?></span>
						<?php if ( ! $locked ) : ?>
						<span class="cp-member-status-badge is-unlocked"><?php esc_html_e( 'Unlocked', 'cp-theme' ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<?php if ( $query->max_num_pages > 1 ) : ?>
		<div class="cp-member-pagination">
			<?php echo paginate_links( array( 'total' => $query->max_num_pages ) ); ?>
		</div>
		<?php endif; ?>

		<?php else : ?>
		<div class="cp-member-empty">
			<p><?php esc_html_e( 'No content available right now.', 'cp-theme' ); ?></p>
		</div>
		<?php endif; ?>

	</div>
</div>
<?php get_footer(); ?>
