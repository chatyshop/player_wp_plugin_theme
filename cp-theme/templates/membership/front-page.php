<?php
/**
 * Template: Membership — Homepage
 * Exclusive content community dashboard.
 */
get_header();

$is_logged_in = is_user_logged_in();
$user_id      = get_current_user_id();

// Featured content (cp_video)
$latest_videos = get_posts( array(
	'post_type'      => 'cp_video',
	'posts_per_page' => 12,
	'post_status'    => 'publish',
) );

// For demonstration, let's assume some videos require group membership. 
// If CPWP_Community exists, check visibility.
$groups = class_exists( 'CPWP_Community' ) ? CPWP_Community::groups() : array();
?>

<div class="cp-shell <?php echo $is_logged_in ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( $is_logged_in ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-member-content">
		
		<!-- Hero / CTA for guests -->
		<?php if ( ! $is_logged_in ) : ?>
		<section class="cp-member-hero">
			<div class="cp-member-hero-inner">
				<h1><?php esc_html_e( 'Unlock Exclusive Content', 'cp-theme' ); ?></h1>
				<p><?php esc_html_e( 'Join our community to access premium videos, behind-the-scenes content, and connect with other members.', 'cp-theme' ); ?></p>
				<a href="<?php echo esc_url( wp_login_url() ); ?>" class="cp-member-btn-primary"><?php esc_html_e( 'Become a Member', 'cp-theme' ); ?></a>
			</div>
		</section>
		<?php else : ?>
		<!-- Member Welcome -->
		<section class="cp-member-welcome">
			<div class="cp-member-welcome-inner">
				<h1><?php esc_html_e( 'Welcome back,', 'cp-theme' ); ?> <?php echo esc_html( wp_get_current_user()->display_name ); ?>!</h1>
				<p><?php esc_html_e( 'Here is the latest content available for your membership level.', 'cp-theme' ); ?></p>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_hero' ); ?>

		<!-- Featured Groups (if any) -->
		<?php if ( ! empty( $groups ) && ! is_wp_error( $groups ) ) : ?>
		<section class="cp-member-section">
			<div class="cp-member-section-head">
				<h2><?php esc_html_e( 'Featured Communities', 'cp-theme' ); ?></h2>
				<a href="<?php echo esc_url( class_exists('CPWP_Page_Suites') ? CPWP_Page_Suites::url('groups') : '#' ); ?>" class="cp-member-link"><?php esc_html_e( 'View all groups', 'cp-theme' ); ?> →</a>
			</div>
			<div class="cp-member-group-strip">
				<?php foreach ( array_slice( $groups, 0, 4 ) as $g ) : 
					$is_member = class_exists( 'CPWP_Community' ) ? CPWP_Community::is_member( $g->ID, $user_id ) : false;
					$thumb     = get_the_post_thumbnail_url( $g->ID, 'medium' );
				?>
				<a href="<?php echo esc_url( get_permalink( $g->ID ) ); ?>" class="cp-member-group-card">
					<div class="cp-member-gc-thumb">
						<?php if ( $thumb ) : ?>
						<img src="<?php echo esc_url( $thumb ); ?>" alt="">
						<?php else : ?>
						<div class="cp-member-gc-placeholder">👥</div>
						<?php endif; ?>
					</div>
					<div class="cp-member-gc-info">
						<h3><?php echo esc_html( $g->post_title ); ?></h3>
						<?php if ( $is_member ) : ?>
						<span class="cp-member-status-badge is-unlocked">✓ <?php esc_html_e( 'Joined', 'cp-theme' ); ?></span>
						<?php endif; ?>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<!-- Latest Exclusive Content -->
		<?php if ( $latest_videos ) : ?>
		<section class="cp-member-section">
			<div class="cp-member-section-head">
				<h2><?php esc_html_e( 'Latest Content', 'cp-theme' ); ?></h2>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>" class="cp-member-link"><?php esc_html_e( 'Browse all', 'cp-theme' ); ?> →</a>
			</div>
			<div class="cp-member-video-grid">
				<?php foreach ( $latest_videos as $vid ) :
					$thumb = get_the_post_thumbnail_url( $vid->ID, 'medium_large' );
					
					// Determine if locked
					// If the video belongs to a group, and user is not in group -> locked.
					// For simplicity in the theme, we'll randomize or check group meta.
					$req_group = absint( get_post_meta( $vid->ID, '_cpwp_community_group', true ) );
					$locked = false;
					if ( $req_group && class_exists( 'CPWP_Community' ) ) {
						$locked = ! CPWP_Community::is_member( $req_group, $user_id ) && ! current_user_can( 'manage_options' );
					}
				?>
				<article class="cp-member-card <?php echo $locked ? 'is-locked' : ''; ?>">
					<a href="<?php echo esc_url( get_permalink( $vid->ID ) ); ?>" class="cp-member-card-thumb">
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
						<h3><a href="<?php echo esc_url( get_permalink( $vid->ID ) ); ?>"><?php echo esc_html( $vid->post_title ); ?></a></h3>
						<div class="cp-member-card-meta">
							<span class="cp-member-date"><?php echo esc_html( get_the_date( '', $vid->ID ) ); ?></span>
							<?php if ( ! $locked ) : ?>
							<span class="cp-member-status-badge is-unlocked"><?php esc_html_e( 'Unlocked', 'cp-theme' ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_grid' ); ?>

	</div>
</div>
<?php get_footer(); ?>
