<?php
/**
 * Sidebar for logged-in users.
 */

if ( ! is_user_logged_in() ) {
	return;
}

$user = wp_get_current_user();
$navigation = cp_theme_preset_navigation();
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
		<ul class="cp-sidebar-menu"><?php foreach ( $navigation as $group => $links ) : ?><?php if ( $links ) : ?><li class="cp-sidebar-section-title"><?php echo esc_html( ucfirst( $group ) ); ?></li><?php foreach ( $links as $link ) : ?><li><a href="<?php echo esc_url( $link['url'] ); ?>"><span><?php echo esc_html( $link['label'] ); ?></span></a></li><?php endforeach; ?><li class="cp-sidebar-divider"></li><?php endif; ?><?php endforeach; ?></ul>
	</nav>
	<?php if ( is_front_page() && class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_sidebar' ); ?>
</aside>
