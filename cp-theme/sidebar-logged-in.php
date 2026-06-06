<?php
/**
 * Sidebar for logged-in users.
 */

if ( ! is_user_logged_in() ) {
	return;
}

$user = wp_get_current_user();
$watch_later_url = cp_theme_get_template_page_url( 'page-watch-later.php' );
$favorites_url = cp_theme_get_template_page_url( 'page-favorites.php' );
?>
<aside class="cp-sidebar-logged-in">
	<div class="cp-sidebar-user-profile">
		<div class="cp-sidebar-avatar">
			<?php echo get_avatar( $user->ID, 56 ); ?>
		</div>
		<div class="cp-sidebar-user-info">
			<span class="cp-sidebar-display-name"><?php echo esc_html( $user->display_name ); ?></span>
			<span class="cp-sidebar-username">@<?php echo esc_html( $user->user_login ); ?></span>
		</div>
	</div>

	<nav class="cp-sidebar-nav">
		<ul class="cp-sidebar-menu">
			<li>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="<?php echo is_front_page() ? 'active' : ''; ?>">
					<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
					<span><?php esc_html_e( 'Home', 'cp-theme' ); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>" class="<?php echo is_post_type_archive( 'cp_video' ) ? 'active' : ''; ?>">
					<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
					<span><?php esc_html_e( 'All Videos', 'cp-theme' ); ?></span>
				</a>
			</li>
			
			<li class="cp-sidebar-divider"></li>
			<li class="cp-sidebar-section-title"><?php esc_html_e( 'Library', 'cp-theme' ); ?></li>

			<li>
				<a href="<?php echo esc_url( $watch_later_url ); ?>" class="<?php echo is_page_template( 'page-watch-later.php' ) ? 'active' : ''; ?>">
					<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
					<span><?php esc_html_e( 'Watch Later', 'cp-theme' ); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( $favorites_url ); ?>" class="<?php echo is_page_template( 'page-favorites.php' ) ? 'active' : ''; ?>">
					<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
					<span><?php esc_html_e( 'Favorites', 'cp-theme' ); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( add_query_arg( 'cpwp_auth', 'profile', home_url( '/' ) ) ); ?>">
					<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
					<span><?php esc_html_e( 'My Profile', 'cp-theme' ); ?></span>
				</a>
			</li>
		</ul>
	</nav>
</aside>
