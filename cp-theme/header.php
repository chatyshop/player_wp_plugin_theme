<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>
	if (localStorage.getItem('cp_sidebar_hidden') === 'true') {
		document.documentElement.classList.add('cp-sidebar-hidden');
	}
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="cp-site-header">
	<div class="cp-shell cp-header-inner">
		<div class="cp-brand-group">
			<?php if ( is_user_logged_in() ) : ?>
				<button type="button" class="cp-sidebar-toggle" aria-label="<?php esc_attr_e( 'Toggle navigation', 'cp-theme' ); ?>">
					<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
				</button>
			<?php endif; ?>
			<a class="cp-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php if ( cp_theme_cp_setting( 'logo_url' ) ) : ?><img src="<?php echo esc_url( cp_theme_cp_setting( 'logo_url' ) ); ?>" alt=""><?php else : ?><span class="cp-brand-mark">CP</span><?php endif; ?><span><?php echo esc_html( cp_theme_cp_setting( 'platform_name', get_bloginfo( 'name' ) ) ); ?></span></a>
		</div>
		<form class="cp-search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"><input name="s" type="search" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search videos...', 'cp-theme' ); ?>"><input type="hidden" name="post_type" value="cp_video"><button type="submit"><?php esc_html_e( 'Search', 'cp-theme' ); ?></button></form>
		<nav class="cp-nav"><?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'fallback_cb' => false ) ); ?></nav>
		<div class="cp-account-links">
			<?php if ( is_user_logged_in() ) : ?><a href="<?php echo esc_url( add_query_arg( 'cpwp_auth', 'profile', home_url( '/' ) ) ); ?>"><?php echo esc_html( wp_get_current_user()->display_name ); ?></a><a class="cp-account-primary" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log out', 'cp-theme' ); ?></a>
			<?php else : ?><?php if ( cp_theme_cp_setting( 'enable_login', true ) ) : ?><a href="<?php echo esc_url( add_query_arg( 'cpwp_auth', 'login', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log in', 'cp-theme' ); ?></a><?php endif; ?><?php if ( cp_theme_cp_setting( 'enable_registration', true ) ) : ?><a class="cp-account-primary" href="<?php echo esc_url( add_query_arg( 'cpwp_auth', 'register', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Register', 'cp-theme' ); ?></a><?php endif; ?><?php endif; ?>
		</div>
	</div>
</header>
<main class="cp-main">
