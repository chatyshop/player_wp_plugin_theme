<?php
/**
 * Template Name: My Cases
 */
if ( ! is_user_logged_in() ) { wp_safe_redirect( add_query_arg( 'cpwp_auth', 'login', home_url( '/' ) ) ); exit; }
get_header(); $cases = class_exists( 'CPWP_Moderation' ) ? CPWP_Moderation::user_cases() : array();
?>
<div class="cp-shell"><section class="cp-section"><header class="cp-section-head"><div><span class="cp-kicker"><?php esc_html_e( 'Trust & Safety', 'cp-theme' ); ?></span><h1><?php esc_html_e( 'My reports and appeals', 'cp-theme' ); ?></h1></div></header>
	<div class="cp-case-list"><?php foreach ( $cases as $case ) : ?><article class="cp-case-card"><div><strong><?php echo esc_html( $case->post_title ); ?></strong><p><?php echo esc_html( $case->post_content ); ?></p></div><span class="cp-status-badge"><?php echo esc_html( ucfirst( get_post_meta( $case->ID, '_cpwp_case_status', true ) ) ); ?></span></article><?php endforeach; ?><?php if ( ! $cases ) : ?><div class="cp-empty"><?php esc_html_e( 'You have not submitted any reports or appeals.', 'cp-theme' ); ?></div><?php endif; ?></div>
</section></div>
<?php get_footer(); ?>
