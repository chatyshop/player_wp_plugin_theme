<?php
/**
 * Template: Gaming — Sidebar (Twitch style)
 * Fixed dark sidebar: Followed Channels, Recommended Channels.
 */
if ( ! is_user_logged_in() ) return;

$user_id = get_current_user_id();

// Fetch followed channels if CPWP_Channels exists
$followed_channels = array();
if ( class_exists( 'CPWP_Channels' ) ) {
	$following = CPWP_Channels::following( $user_id );
	if ( $following ) {
		foreach ( $following as $uid ) {
			$ch = CPWP_Channels::get( $uid );
			if ( $ch ) {
				$ch['user_id'] = $uid;
				$followed_channels[] = $ch;
			}
		}
	}
}

// Top streamers / recommended
$top_users = get_users( array( 'number' => 8, 'exclude' => array( $user_id ) ) );

?>
<aside class="cp-sidebar-logged-in cp-twitch-sidebar">

	<div class="cp-twitch-sidebar-head">
		<h2><?php esc_html_e( 'For You', 'cp-theme' ); ?></h2>
	</div>

	<nav class="cp-twitch-sidebar-nav">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cp-twitch-nav-link is-active">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
			<span><?php esc_html_e( 'Browse', 'cp-theme' ); ?></span>
		</a>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_event' ) ); ?>" class="cp-twitch-nav-link">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z"/></svg>
			<span><?php esc_html_e( 'Esports', 'cp-theme' ); ?></span>
		</a>
	</nav>

	<div class="cp-twitch-divider"></div>

	<!-- Followed Channels -->
	<div class="cp-twitch-sidebar-section">
		<h3 class="cp-twitch-section-heading"><?php esc_html_e( 'Followed Channels', 'cp-theme' ); ?></h3>
		<ul class="cp-twitch-channel-list">
			<?php if ( $followed_channels ) : ?>
				<?php foreach ( array_slice( $followed_channels, 0, 8 ) as $ch ) : ?>
				<li>
					<a href="<?php echo esc_url( CPWP_Channels::public_url( $ch ) ); ?>" class="cp-twitch-channel-link">
						<div class="cp-twitch-ch-avatar">
							<img src="<?php echo esc_url( $ch['logo_url'] ?? get_avatar_url( $ch['user_id'] ) ); ?>" alt="">
						</div>
						<div class="cp-twitch-ch-info">
							<span class="cp-twitch-ch-name"><?php echo esc_html( $ch['name'] ); ?></span>
							<span class="cp-twitch-ch-game">Just Chatting</span>
						</div>
						<div class="cp-twitch-ch-status">
							<span class="cp-twitch-dot"></span>
							<span class="cp-twitch-viewers"><?php echo number_format_i18n( rand( 100, 5000 ) ); ?></span>
						</div>
					</a>
				</li>
				<?php endforeach; ?>
			<?php else : ?>
				<li class="cp-twitch-empty"><?php esc_html_e( 'No channels followed yet.', 'cp-theme' ); ?></li>
			<?php endif; ?>
		</ul>
	</div>

	<div class="cp-twitch-divider"></div>

	<!-- Recommended Channels -->
	<div class="cp-twitch-sidebar-section">
		<h3 class="cp-twitch-section-heading"><?php esc_html_e( 'Recommended Channels', 'cp-theme' ); ?></h3>
		<ul class="cp-twitch-channel-list">
			<?php foreach ( $top_users as $tu ) : ?>
			<li>
				<a href="#" class="cp-twitch-channel-link">
					<div class="cp-twitch-ch-avatar">
						<img src="<?php echo esc_url( get_avatar_url( $tu->ID ) ); ?>" alt="">
					</div>
					<div class="cp-twitch-ch-info">
						<span class="cp-twitch-ch-name"><?php echo esc_html( $tu->display_name ); ?></span>
						<span class="cp-twitch-ch-game">Esports</span>
					</div>
					<div class="cp-twitch-ch-status">
						<span class="cp-twitch-dot"></span>
						<span class="cp-twitch-viewers"><?php echo number_format_i18n( rand( 100, 5000 ) ); ?></span>
					</div>
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>

</aside>
