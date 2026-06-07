<?php
/**
 * Template: Membership — Single Group
 * Group details, members, join button, and exclusive content feed.
 */
get_header();

$user_id = get_current_user_id();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-member-content">
		<?php while ( have_posts() ) : the_post();
			$group_id  = get_the_ID();
			$thumb     = get_the_post_thumbnail_url( $group_id, 'full' );
			$is_member = false;
			$members   = array();
			
			if ( class_exists( 'CPWP_Community' ) ) {
				$is_member = CPWP_Community::is_member( $group_id, $user_id );
				$members   = CPWP_Community::ids( get_post_meta( $group_id, CPWP_Community::MEMBERS, true ) );
			}
			$member_count = count( $members );
		?>

		<div class="cp-member-group-header">
			<?php if ( $thumb ) : ?>
			<div class="cp-member-group-cover">
				<img src="<?php echo esc_url( $thumb ); ?>" alt="">
			</div>
			<?php endif; ?>
			<div class="cp-member-group-info-bar">
				<div class="cp-member-group-details">
					<h1 class="cp-member-group-title"><?php the_title(); ?></h1>
					<span class="cp-member-group-stats">
						<strong><?php echo number_format_i18n( $member_count ); ?></strong> <?php esc_html_e( 'Members', 'cp-theme' ); ?>
					</span>
				</div>
				<div class="cp-member-group-actions">
					<?php if ( ! is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="cp-member-btn-primary"><?php esc_html_e( 'Log in to Join', 'cp-theme' ); ?></a>
					<?php elseif ( $is_member ) : ?>
						<span class="cp-member-btn-secondary is-active">✓ <?php esc_html_e( 'Joined', 'cp-theme' ); ?></span>
					<?php else : ?>
						<!-- In a real scenario this might be a form/button handled by JS -->
						<button class="cp-member-btn-primary" data-cpwp-join-group="<?php echo esc_attr( $group_id ); ?>"><?php esc_html_e( 'Join Community', 'cp-theme' ); ?></button>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="cp-member-group-layout">
			<!-- Main Feed -->
			<div class="cp-member-group-main">
				
				<?php if ( ! $is_member && ! current_user_can( 'manage_options' ) ) : ?>
					<div class="cp-member-gate" style="margin-top:0;">
						<div class="cp-member-gate-icon">🔒</div>
						<h2><?php esc_html_e( 'Private Group', 'cp-theme' ); ?></h2>
						<p><?php esc_html_e( 'You must join this community to see its exclusive content and discussions.', 'cp-theme' ); ?></p>
					</div>
				<?php else : ?>

					<!-- About / Description -->
					<div class="cp-member-group-box">
						<h2><?php esc_html_e( 'About this Community', 'cp-theme' ); ?></h2>
						<div class="cp-member-group-desc">
							<?php the_content(); ?>
						</div>
					</div>

					<!-- Exclusive Videos -->
					<div class="cp-member-group-videos">
						<h3><?php esc_html_e( 'Exclusive Content', 'cp-theme' ); ?></h3>
						<?php
						$group_videos = get_posts( array(
							'post_type'  => 'cp_video',
							'meta_query' => array(
								array( 'key' => '_cpwp_community_group', 'value' => $group_id )
							),
							'posts_per_page' => 12
						) );

						if ( $group_videos ) :
						?>
						<div class="cp-member-video-grid cp-member-video-grid--sm">
							<?php foreach ( $group_videos as $vid ) :
								$vthumb = get_the_post_thumbnail_url( $vid->ID, 'medium' );
							?>
							<article class="cp-member-card">
								<a href="<?php echo esc_url( get_permalink( $vid->ID ) ); ?>" class="cp-member-card-thumb">
									<?php if ( $vthumb ) : ?>
									<img src="<?php echo esc_url( $vthumb ); ?>" alt="" loading="lazy">
									<?php else : ?>
									<div class="cp-member-card-placeholder">▶</div>
									<?php endif; ?>
								</a>
								<div class="cp-member-card-body">
									<h4 style="font-size:.9rem;margin:0 0 4px;"><a href="<?php echo esc_url( get_permalink( $vid->ID ) ); ?>" style="text-decoration:none;color:inherit;"><?php echo esc_html( $vid->post_title ); ?></a></h4>
									<span class="cp-member-date"><?php echo esc_html( get_the_date( '', $vid->ID ) ); ?></span>
								</div>
							</article>
							<?php endforeach; ?>
						</div>
						<?php else : ?>
						<p class="cp-member-empty"><?php esc_html_e( 'No exclusive videos posted yet.', 'cp-theme' ); ?></p>
						<?php endif; ?>
					</div>

				<?php endif; ?>

			</div>

			<!-- Sidebar -->
			<div class="cp-member-group-aside">
				<div class="cp-member-group-box">
					<h3><?php esc_html_e( 'Members', 'cp-theme' ); ?></h3>
					<?php if ( $members ) : ?>
					<div class="cp-member-avatar-grid">
						<?php foreach ( array_slice( $members, 0, 16 ) as $uid ) : ?>
						<img src="<?php echo esc_url( get_avatar_url( $uid, array('size'=>40) ) ); ?>" alt="" class="cp-member-avatar-sm" title="<?php echo esc_attr( get_userdata($uid)->display_name ?? '' ); ?>">
						<?php endforeach; ?>
					</div>
					<?php if ( $member_count > 16 ) : ?>
					<p class="cp-member-more-text">+ <?php echo ( $member_count - 16 ); ?> <?php esc_html_e( 'more', 'cp-theme' ); ?></p>
					<?php endif; ?>
					<?php else : ?>
					<p class="cp-member-empty-sm"><?php esc_html_e( 'No members yet.', 'cp-theme' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php endwhile; ?>
	</div>
</div>
<?php get_footer(); ?>
