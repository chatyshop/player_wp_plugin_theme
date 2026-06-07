<?php
/**
 * Template: Creator Platform — Sidebar
 * YouTube-style sidebar: Home, Trending, Channels, Subscriptions, Library.
 */
if ( ! is_user_logged_in() ) return;

$user        = wp_get_current_user();
$profile_url = add_query_arg( 'cpwp_auth', 'profile', home_url( '/' ) );
$studio_url  = add_query_arg( 'cpwp_suite', 'studio', home_url( '/' ) );
$upload_url  = add_query_arg( 'cpwp_suite', 'upload', home_url( '/' ) );
$wl_url      = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'watch-later' ) : home_url( '/' );
$hist_url    = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'watch-history' ) : home_url( '/' );
$fav_url     = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'favorites' ) : home_url( '/' );

// Followed channels for "Subscriptions" section.
$following = class_exists( 'CPWP_Channels' ) ? CPWP_Channels::followed_channels() : array();

$nav_groups = array(
	'' => array(
		array( 'label' => 'Home',       'url' => home_url( '/' ),                          'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>' ),
		array( 'label' => 'All Videos',  'url' => get_post_type_archive_link( 'cp_video' ), 'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2"/><polygon points="10,8 16,12 10,16"/></svg>' ),
		array( 'label' => 'Trending',    'url' => add_query_arg( array( 'post_type' => 'cp_video', 'cp_sort' => 'views' ), get_post_type_archive_link( 'cp_video' ) ), 'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>' ),
	),
	'You' => array(
		array( 'label' => 'My Channel',    'url' => $profile_url, 'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>' ),
		array( 'label' => 'Creator Studio','url' => $studio_url,  'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>' ),
		array( 'label' => 'Upload Video',  'url' => $upload_url,  'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>' ),
		array( 'label' => 'Watch Later',   'url' => $wl_url,      'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' ),
		array( 'label' => 'Watch History', 'url' => $hist_url,    'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><polyline points="12 7 12 12 15 15"/></svg>' ),
		array( 'label' => 'Liked Videos',  'url' => $fav_url,     'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>' ),
	),
);
?>
<aside class="cp-sidebar-logged-in cp-yt-sidebar">

	<!-- Logo / Brand -->
	<div class="cp-yt-sidebar-brand">
		<?php $logo = cp_theme_cp_setting( 'logo_url' ); ?>
		<?php if ( $logo ) : ?>
		<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( cp_theme_cp_setting( 'platform_name', get_bloginfo( 'name' ) ) ); ?>" class="cp-yt-logo">
		<?php else : ?>
		<span class="cp-yt-brand-name"><?php echo esc_html( cp_theme_cp_setting( 'platform_name', get_bloginfo( 'name' ) ) ); ?></span>
		<?php endif; ?>
	</div>

	<!-- Navigation -->
	<nav class="cp-yt-sidebar-nav">
		<?php foreach ( $nav_groups as $group_label => $links ) : ?>
		<?php if ( $group_label ) : ?><div class="cp-yt-nav-divider"></div><span class="cp-yt-nav-group-label"><?php echo esc_html( $group_label ); ?></span><?php endif; ?>
		<ul>
			<?php foreach ( $links as $link ) :
				$path    = parse_url( $link['url'], PHP_URL_PATH );
				$current = ( untrailingslashit( $_SERVER['REQUEST_URI'] ) === untrailingslashit( $path ) );
			?>
			<li>
				<a href="<?php echo esc_url( $link['url'] ); ?>" class="cp-yt-nav-link <?php echo $current ? 'is-active' : ''; ?>">
					<span class="cp-yt-nav-icon" aria-hidden="true"><?php echo $link['icon']; ?></span>
					<span><?php echo esc_html( $link['label'] ); ?></span>
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endforeach; ?>

		<?php if ( $following ) : ?>
		<div class="cp-yt-nav-divider"></div>
		<span class="cp-yt-nav-group-label"><?php esc_html_e( 'Subscriptions', 'cp-theme' ); ?></span>
		<ul class="cp-yt-subscriptions">
			<?php foreach ( array_slice( $following, 0, 8 ) as $sub ) :
				$sub_logo = $sub['channel']['logo_url'] ?? get_avatar_url( $sub['user']->ID );
				$sub_url  = CPWP_Channels::public_url( $sub['channel'] );
			?>
			<li>
				<a href="<?php echo esc_url( $sub_url ); ?>" class="cp-yt-nav-link cp-yt-sub-link">
					<img src="<?php echo esc_url( $sub_logo ); ?>" alt="" class="cp-yt-sub-avatar">
					<span><?php echo esc_html( $sub['channel']['name'] ); ?></span>
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>

		<div class="cp-yt-nav-divider"></div>
		<ul>
			<li>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="cp-yt-nav-link">
					<span class="cp-yt-nav-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></span>
					<span><?php esc_html_e( 'Sign out', 'cp-theme' ); ?></span>
				</a>
			</li>
		</ul>
	</nav>

	<?php if ( is_front_page() && class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_sidebar' ); ?>
</aside>
