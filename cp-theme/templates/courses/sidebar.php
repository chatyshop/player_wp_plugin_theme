<?php
/**
 * Template: Courses — Sidebar
 * Udemy-style sidebar with user avatar, course navigation groups and account links.
 * Guard: only renders when the user is logged in.
 */
if ( ! is_user_logged_in() ) return;

$user        = wp_get_current_user();
$home_url    = home_url( '/' );
$catalog_url = get_post_type_archive_link( 'cp_course' );

$my_courses_url  = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'my-courses' )      : $catalog_url;
$certs_url       = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'certificates' )     : $home_url;
$in_progress_url = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'student-progress' ) : $home_url;
$completed_url   = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'completed' )        : $home_url;
$profile_url     = add_query_arg( 'cpwp_auth', 'profile', $home_url );

$nav_groups = array(
	'' => array(
		array( 'label' => __( 'Home', 'cp-theme' ),        'url' => $home_url,        'icon' => '🏠' ),
		array( 'label' => __( 'All Courses', 'cp-theme' ),  'url' => $catalog_url,     'icon' => '📚' ),
		array( 'label' => __( 'My Courses', 'cp-theme' ),   'url' => $my_courses_url,  'icon' => '🎓' ),
		array( 'label' => __( 'Certificates', 'cp-theme' ), 'url' => $certs_url,       'icon' => '🏆' ),
	),
	'Progress' => array(
		array( 'label' => __( 'In Progress', 'cp-theme' ), 'url' => $in_progress_url, 'icon' => '▶' ),
		array( 'label' => __( 'Completed', 'cp-theme' ),   'url' => $completed_url,   'icon' => '✅' ),
	),
	'Account' => array(
		array( 'label' => __( 'Profile', 'cp-theme' ),   'url' => $profile_url,                    'icon' => '👤' ),
		array( 'label' => __( 'Sign out', 'cp-theme' ),  'url' => wp_logout_url( $home_url ),       'icon' => '🚪' ),
	),
);

$current_path = rtrim( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
?>
<aside class="cp-sidebar-logged-in cp-udemy-sidebar">

	<!-- Branding -->
	<div class="cp-udemy-sidebar-brand">
		<?php $logo = cp_theme_cp_setting( 'logo_url' ); ?>
		<?php if ( $logo ) : ?>
		<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( cp_theme_cp_setting( 'platform_name', get_bloginfo( 'name' ) ) ); ?>" class="cp-udemy-sidebar-logo">
		<?php else : ?>
		<span class="cp-udemy-sidebar-platform-name"><?php echo esc_html( cp_theme_cp_setting( 'platform_name', get_bloginfo( 'name' ) ) ); ?></span>
		<?php endif; ?>
	</div>

	<!-- User Avatar + Info -->
	<div class="cp-udemy-sidebar-user">
		<?php echo get_avatar( $user->ID, 48, '', '', array( 'class' => 'cp-udemy-sidebar-avatar' ) ); ?>
		<div class="cp-udemy-sidebar-user-info">
			<span class="cp-udemy-sidebar-display-name"><?php echo esc_html( $user->display_name ); ?></span>
			<span class="cp-udemy-sidebar-username">@<?php echo esc_html( $user->user_login ); ?></span>
		</div>
	</div>

	<!-- Navigation -->
	<nav class="cp-udemy-sidebar-nav" aria-label="<?php esc_attr_e( 'Courses navigation', 'cp-theme' ); ?>">
		<?php foreach ( $nav_groups as $group_label => $links ) : ?>
		<div class="cp-udemy-nav-group">
			<?php if ( $group_label ) : ?>
			<span class="cp-udemy-nav-group-label"><?php echo esc_html( $group_label ); ?></span>
			<?php endif; ?>
			<ul class="cp-udemy-nav-list">
				<?php foreach ( $links as $link ) :
					$link_path = rtrim( wp_parse_url( $link['url'], PHP_URL_PATH ) ?? '', '/' );
					$is_active = ( $current_path === $link_path );
				?>
				<li>
					<a
						href="<?php echo esc_url( $link['url'] ); ?>"
						class="cp-udemy-nav-link<?php echo $is_active ? ' is-active' : ''; ?>"
						<?php if ( $is_active ) echo 'aria-current="page"'; ?>
					>
						<span class="cp-udemy-nav-icon" aria-hidden="true"><?php echo $link['icon']; ?></span>
						<span><?php echo esc_html( $link['label'] ); ?></span>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endforeach; ?>
	</nav>

	<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_sidebar' ); ?>
</aside>
