<?php
/**
 * Template: Business Training — Sidebar
 * Corporate navigation for LMS.
 */
if ( ! is_user_logged_in() ) return;

$user_id = get_current_user_id();
$suites_class = class_exists( 'CPWP_Page_Suites' );
?>
<aside class="cp-sidebar-logged-in cp-business-sidebar">
	
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cp-business-sidebar__logo">
		📊 <?php bloginfo( 'name' ); ?>
	</a>

	<div class="cp-business-sidebar__section-label"><?php esc_html_e( 'Learning Management', 'cp-theme' ); ?></div>
	<nav>
		<ul>
			<li>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="active">
					<?php esc_html_e( 'Dashboard', 'cp-theme' ); ?>
				</a>
			</li>
			<li>
				<a href="#">
					<?php esc_html_e( 'My Training', 'cp-theme' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>">
					<?php esc_html_e( 'Company Library', 'cp-theme' ); ?>
				</a>
			</li>
			<li>
				<a href="#">
					<?php esc_html_e( 'Certificates', 'cp-theme' ); ?>
				</a>
			</li>
		</ul>
	</nav>

	<div class="cp-business-sidebar__section-label"><?php esc_html_e( 'Organization', 'cp-theme' ); ?></div>
	<nav>
		<ul>
			<?php if ( $suites_class ) : ?>
			<li>
				<a href="<?php echo esc_url( CPWP_Page_Suites::url( 'groups' ) ); ?>">
					<?php esc_html_e( 'Departments', 'cp-theme' ); ?>
				</a>
			</li>
			<?php endif; ?>
			<li>
				<a href="#">
					<?php esc_html_e( 'Company Wiki', 'cp-theme' ); ?>
				</a>
			</li>
		</ul>
	</nav>

</aside>
