<?php
/**
 * Template: Podcast — Sidebar (Spotify style)
 * Fixed dark sidebar with Home, Search, Your Library, Playlists.
 */
if ( ! is_user_logged_in() ) return;
?>
<aside class="cp-sidebar-logged-in cp-podcast-sidebar">
	
	<div class="cp-podcast-sidebar-brand">
		<?php
		$logo = cp_theme_cp_setting( 'logo_url', '' );
		if ( $logo ) : ?>
			<img src="<?php echo esc_url( $logo ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="cp-podcast-logo">
		<?php else : ?>
			<span class="cp-podcast-brand-name">🎧 <?php bloginfo( 'name' ); ?></span>
		<?php endif; ?>
	</div>

	<nav class="cp-podcast-nav-main">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cp-podcast-nav-link is-active">
			<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l10 9h-3v9h-4v-6h-6v6H5v-9H2z"/></svg>
			<?php esc_html_e( 'Home', 'cp-theme' ); ?>
		</a>
		<a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" class="cp-podcast-nav-link">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
			<?php esc_html_e( 'Search', 'cp-theme' ); ?>
		</a>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>" class="cp-podcast-nav-link">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><path d="M8 12h8M12 8v8"/></svg>
			<?php esc_html_e( 'Your Library', 'cp-theme' ); ?>
		</a>
	</nav>

	<div class="cp-podcast-nav-actions">
		<a href="#" class="cp-podcast-action-link">
			<div class="cp-podcast-action-icon" style="background:#fff;color:#000;">+</div>
			<?php esc_html_e( 'Create Playlist', 'cp-theme' ); ?>
		</a>
		<a href="#" class="cp-podcast-action-link">
			<div class="cp-podcast-action-icon" style="background:linear-gradient(135deg,#450af5,#c4efd9);color:#fff;">♥</div>
			<?php esc_html_e( 'Liked Episodes', 'cp-theme' ); ?>
		</a>
	</div>

	<div class="cp-podcast-divider"></div>

	<div class="cp-podcast-playlists">
		<a href="#" class="cp-podcast-playlist-link">My Favorite Interviews</a>
		<a href="#" class="cp-podcast-playlist-link">Tech News Daily</a>
		<a href="#" class="cp-podcast-playlist-link">Comedy Central</a>
		<a href="#" class="cp-podcast-playlist-link">Deep Dives</a>
	</div>

</aside>
