<?php
/**
 * Template: Streaming — Sidebar
 * OTT-style sidebar with Movies, TV Shows, Series, Seasons and user library links.
 */
if ( ! is_user_logged_in() ) return;

$user        = wp_get_current_user();
$movies_url  = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'movies' )   : get_post_type_archive_link( 'cp_video' );
$tv_url      = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'tv-shows' )  : get_post_type_archive_link( 'cp_video' );
$wl_url      = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'watch-later' ) : home_url( '/' );
$history_url = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'watch-history' ) : home_url( '/' );
$profile_url = add_query_arg( 'cpwp_auth', 'profile', home_url( '/' ) );

$nav_groups = array(
	'Browse' => array(
		array( 'label' => 'Home',      'url' => home_url( '/' ),                          'icon' => '🏠' ),
		array( 'label' => 'Movies',    'url' => $movies_url,                              'icon' => '🎬' ),
		array( 'label' => 'TV Shows',  'url' => $tv_url,                                  'icon' => '📺' ),
		array( 'label' => 'Series',    'url' => get_post_type_archive_link( 'cp_series' ), 'icon' => '🎭' ),
		array( 'label' => 'All Videos', 'url' => get_post_type_archive_link( 'cp_video' ), 'icon' => '▶' ),
	),
	'My Library' => array(
		array( 'label' => 'My List',       'url' => $wl_url,      'icon' => '➕' ),
		array( 'label' => 'Watch History', 'url' => $history_url, 'icon' => '🕐' ),
	),
	'Account' => array(
		array( 'label' => 'My Profile', 'url' => $profile_url,                        'icon' => '👤' ),
		array( 'label' => 'Log out',    'url' => wp_logout_url( home_url( '/' ) ),    'icon' => '🚪' ),
	),
);
?>
<aside class="cp-sidebar-logged-in cp-ott-sidebar">
	<div class="cp-ott-sidebar-brand">
		<?php $logo = cp_theme_cp_setting( 'logo_url' ); ?>
		<?php if ( $logo ) : ?>
		<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( cp_theme_cp_setting( 'platform_name', get_bloginfo( 'name' ) ) ); ?>" class="cp-ott-sidebar-logo">
		<?php else : ?>
		<span class="cp-ott-sidebar-name"><?php echo esc_html( cp_theme_cp_setting( 'platform_name', get_bloginfo( 'name' ) ) ); ?></span>
		<?php endif; ?>
	</div>

	<div class="cp-ott-sidebar-user">
		<?php echo get_avatar( $user->ID, 40 ); ?>
		<div>
			<span class="cp-ott-sidebar-display"><?php echo esc_html( $user->display_name ); ?></span>
			<span class="cp-ott-sidebar-username">@<?php echo esc_html( $user->user_login ); ?></span>
		</div>
	</div>

	<nav class="cp-ott-sidebar-nav">
		<?php foreach ( $nav_groups as $group_label => $links ) : ?>
		<div class="cp-ott-nav-group">
			<span class="cp-ott-nav-group-label"><?php echo esc_html( $group_label ); ?></span>
			<ul>
				<?php foreach ( $links as $link ) : ?>
				<li>
					<a href="<?php echo esc_url( $link['url'] ); ?>" class="cp-ott-nav-link <?php echo ( rtrim( $_SERVER['REQUEST_URI'], '/' ) === rtrim( parse_url( $link['url'], PHP_URL_PATH ), '/' ) ) ? 'is-active' : ''; ?>">
						<span class="cp-ott-nav-icon" aria-hidden="true"><?php echo $link['icon']; ?></span>
						<span><?php echo esc_html( $link['label'] ); ?></span>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endforeach; ?>
	</nav>

	<?php if ( class_exists( 'CPWP_Monetization' ) && is_front_page() ) echo CPWP_Monetization::render( 'home_sidebar' ); ?>
</aside>
