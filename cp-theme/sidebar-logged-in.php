<?php
/**
 * Sidebar Router
 * Detects site type and loads the matching sidebar template.
 */
if ( ! is_user_logged_in() ) {
	return;
}
$cp_site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
$cp_tpl       = locate_template( 'templates/' . $cp_site_type . '/sidebar.php' );
if ( ! $cp_tpl ) {
	$cp_tpl = locate_template( 'templates/default/sidebar.php' );
}
if ( $cp_tpl ) {
	load_template( $cp_tpl, false );
}
