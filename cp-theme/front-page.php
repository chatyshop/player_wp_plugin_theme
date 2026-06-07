<?php
/**
 * Front Page Router
 * Detects site type and loads the matching template.
 */
$cp_site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
$cp_tpl       = locate_template( 'templates/' . $cp_site_type . '/front-page.php' );
if ( ! $cp_tpl ) {
	$cp_tpl = locate_template( 'templates/default/front-page.php' );
}
if ( $cp_tpl ) {
	load_template( $cp_tpl, false );
}
