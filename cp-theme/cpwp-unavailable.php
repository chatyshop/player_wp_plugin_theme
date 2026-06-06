<?php get_header(); ?>
<div class="cp-shell"><section class="cp-state-page" role="alert">
	<span class="cp-state-code">403</span>
	<h1><?php esc_html_e( 'This video is unavailable', 'cp-theme' ); ?></h1>
	<p><?php echo esc_html( get_query_var( 'cpwp_unavailable_message', __( 'You do not have permission to view this content.', 'cp-theme' ) ) ); ?></p>
	<div><a class="cp-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return home', 'cp-theme' ); ?></a><?php if ( ! is_user_logged_in() ) : ?> <a class="cp-button cp-button-secondary" href="<?php echo esc_url( add_query_arg( 'cpwp_auth', 'login', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log in', 'cp-theme' ); ?></a><?php endif; ?></div>
</section></div>
<?php get_footer(); ?>
