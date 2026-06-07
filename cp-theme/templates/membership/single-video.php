<?php
/**
 * Template: Membership — Single Video
 * Player with membership gate if not subscribed.
 */
get_header();

$user_id = get_current_user_id();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-member-content">
		<?php while ( have_posts() ) : the_post();
			$post_id   = get_the_ID();
			$req_group = absint( get_post_meta( $post_id, '_cpwp_community_group', true ) );
			$locked    = false;
			
			if ( $req_group && class_exists( 'CPWP_Community' ) ) {
				$locked = ! CPWP_Community::is_member( $req_group, $user_id ) && ! current_user_can( 'manage_options' );
			}
		?>
		
		<div class="cp-member-watch-layout">
			<div class="cp-member-watch-main">
				
				<?php if ( $locked ) : ?>
					<!-- Locked State -->
					<div class="cp-member-gate">
						<div class="cp-member-gate-icon">🔒</div>
						<h2><?php esc_html_e( 'This content is for members only', 'cp-theme' ); ?></h2>
						<p><?php esc_html_e( 'To watch this video, you need to join the required group.', 'cp-theme' ); ?></p>
						<a href="<?php echo esc_url( get_permalink( $req_group ) ); ?>" class="cp-member-btn-primary"><?php esc_html_e( 'View Group & Join', 'cp-theme' ); ?></a>
					</div>
				<?php else : ?>
					<!-- Unlocked State -->
					<div class="cp-member-player-box">
						<?php the_content(); ?>
					</div>
				<?php endif; ?>

				<div class="cp-member-watch-info">
					<h1 class="cp-member-watch-title"><?php the_title(); ?></h1>
					<div class="cp-member-watch-meta">
						<span class="cp-member-date"><?php echo esc_html( get_the_date() ); ?></span>
						<?php if ( $req_group ) : ?>
						<span class="cp-member-meta-divider">·</span>
						<a href="<?php echo esc_url( get_permalink( $req_group ) ); ?>" class="cp-member-group-tag"><?php echo esc_html( get_the_title( $req_group ) ); ?></a>
						<?php endif; ?>
					</div>
					
					<?php if ( ! $locked ) : ?>
					<div class="cp-member-watch-desc">
						<?php cp_theme_video_details( $post_id ); ?>
					</div>
					<?php endif; ?>
				</div>

				<?php if ( ! $locked && ( comments_open() || get_comments_number() ) ) : ?>
				<div class="cp-member-comments-section">
					<h3><?php esc_html_e( 'Member Discussion', 'cp-theme' ); ?></h3>
					<?php comments_template(); ?>
				</div>
				<?php endif; ?>

			</div>

			<!-- Sidebar -->
			<div class="cp-member-watch-aside">
				<h3><?php esc_html_e( 'Related Exclusive Content', 'cp-theme' ); ?></h3>
				<div class="cp-member-related-list">
					<?php
					$related = get_posts( array( 'post_type' => 'cp_video', 'posts_per_page' => 4, 'post__not_in' => array( $post_id ) ) );
					foreach ( $related as $rel ) :
						$rthumb = get_the_post_thumbnail_url( $rel->ID, 'medium' );
						$rgroup = absint( get_post_meta( $rel->ID, '_cpwp_community_group', true ) );
						$rlock  = false;
						if ( $rgroup && class_exists( 'CPWP_Community' ) ) {
							$rlock = ! CPWP_Community::is_member( $rgroup, $user_id ) && ! current_user_can( 'manage_options' );
						}
					?>
					<a href="<?php echo esc_url( get_permalink( $rel->ID ) ); ?>" class="cp-member-related-card <?php echo $rlock ? 'is-locked' : ''; ?>">
						<div class="cp-member-rc-thumb">
							<?php if ( $rthumb ) : ?><img src="<?php echo esc_url( $rthumb ); ?>" alt=""><?php endif; ?>
							<?php if ( $rlock ) : ?><div class="cp-member-rc-lock">🔒</div><?php endif; ?>
						</div>
						<div class="cp-member-rc-info">
							<h4><?php echo esc_html( $rel->post_title ); ?></h4>
							<span class="cp-member-rc-date"><?php echo esc_html( get_the_date( '', $rel->ID ) ); ?></span>
						</div>
					</a>
					<?php endforeach; ?>
				</div>
			</div>

		</div>
		<?php endwhile; ?>
	</div>
</div>
<?php get_footer(); ?>
