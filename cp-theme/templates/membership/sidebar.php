<?php
/**
 * Template: Membership — Sidebar
 * Navigation for community features, groups, and exclusive content.
 */
if ( ! is_user_logged_in() ) return;
$user_id = get_current_user_id();

// Fetch groups the user is a member of
$my_groups = array();
if ( class_exists( 'CPWP_Community' ) ) {
	$all_groups = CPWP_Community::groups();
	foreach ( $all_groups as $g ) {
		if ( CPWP_Community::is_member( $g->ID, $user_id ) ) {
			$my_groups[] = $g;
		}
	}
}

$suites_class = class_exists( 'CPWP_Page_Suites' );
?>
<aside class="cp-sidebar-logged-in cp-member-sidebar">
	
	<div class="cp-member-sidebar-user">
		<img src="<?php echo esc_url( get_avatar_url( $user_id ) ); ?>" alt="">
		<div>
			<span class="cp-member-user-name"><?php echo esc_html( wp_get_current_user()->display_name ); ?></span>
			<span class="cp-member-user-badge"><?php esc_html_e( 'Pro Member', 'cp-theme' ); ?></span>
		</div>
	</div>

	<nav class="cp-member-sidebar-nav">
		<div class="cp-member-nav-section">
			<span class="cp-member-nav-label"><?php esc_html_e( 'Main Menu', 'cp-theme' ); ?></span>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cp-member-nav-link is-active">
				<span class="cp-member-nav-icon">🏠</span>
				<?php esc_html_e( 'Dashboard', 'cp-theme' ); ?>
			</a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>" class="cp-member-nav-link">
				<span class="cp-member-nav-icon">🎬</span>
				<?php esc_html_e( 'All Content', 'cp-theme' ); ?>
			</a>
			<?php if ( $suites_class ) : ?>
			<a href="<?php echo esc_url( CPWP_Page_Suites::url( 'community-feed' ) ); ?>" class="cp-member-nav-link">
				<span class="cp-member-nav-icon">💬</span>
				<?php esc_html_e( 'Community Feed', 'cp-theme' ); ?>
			</a>
			<a href="<?php echo esc_url( CPWP_Page_Suites::url( 'groups' ) ); ?>" class="cp-member-nav-link">
				<span class="cp-member-nav-icon">👥</span>
				<?php esc_html_e( 'Discover Groups', 'cp-theme' ); ?>
			</a>
			<?php endif; ?>
		</div>

		<div class="cp-member-nav-divider"></div>

		<div class="cp-member-nav-section">
			<span class="cp-member-nav-label"><?php esc_html_e( 'My Groups', 'cp-theme' ); ?></span>
			<?php if ( $my_groups ) : ?>
				<?php foreach ( $my_groups as $g ) : ?>
				<a href="<?php echo esc_url( get_permalink( $g->ID ) ); ?>" class="cp-member-nav-link cp-member-nav-group-link">
					<div class="cp-member-group-dot"></div>
					<?php echo esc_html( $g->post_title ); ?>
				</a>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="cp-member-empty-text"><?php esc_html_e( 'You haven\'t joined any groups yet.', 'cp-theme' ); ?></p>
			<?php endif; ?>
		</div>

		<div class="cp-member-nav-divider"></div>

		<div class="cp-member-nav-section">
			<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="cp-member-nav-link">
				<span class="cp-member-nav-icon">🚪</span>
				<?php esc_html_e( 'Sign Out', 'cp-theme' ); ?>
			</a>
		</div>
	</nav>

</aside>
