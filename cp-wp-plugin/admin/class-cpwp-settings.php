<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_Settings {
	public static function ajax_export() {
		self::verify_ajax( 'cpwp_manage_settings' );
		$options = self::get();
		unset( $options['storage_secret_key'], $options['storage_access_key'] );
		wp_send_json_success( array( 'settings' => $options ) );
	}

	public static function ajax_import() {
		self::verify_ajax( 'cpwp_manage_settings' );
		$decoded = json_decode( wp_unslash( $_POST['settings'] ?? '' ), true );
		if ( ! is_array( $decoded ) ) wp_send_json_error( array( 'message' => __( 'Invalid settings JSON.', 'cp-wp-plugin' ) ) );
		$existing = self::get();
		if ( $clean['site_type'] !== $existing['site_type'] ) update_option( 'cpwp_flush_rewrites', 1 );
		$decoded['storage_secret_key'] = $existing['storage_secret_key'];
		$decoded['storage_access_key'] = $existing['storage_access_key'];
		update_option( 'cpwp_settings', self::sanitize( $decoded ) );
		wp_send_json_success();
	}

	public static function ajax_reset() {
		self::verify_ajax( 'cpwp_manage_settings' );
		delete_option( 'cpwp_settings' );
		wp_send_json_success();
	}

	public static function defaults() {
		return array(
			'site_type' => 'creator_platform',
			'platform_name' => '',
			'tagline' => '',
			'logo_url' => '',
			'accent_color' => '#6d5dfc',
			'footer_text' => '',
			'player_version' => '1.0.7',
			'custom_cdn' => '',
			'default_preload' => 'metadata',
			'default_muted' => false,
			'show_sharing' => true,
			'show_transcript' => true,
			'show_related' => true,
			'enable_comments' => true,
			'enable_analytics' => true,
			'enable_login' => true,
			'enable_registration' => true,
			'comments_login_only' => false,
			'enable_password_recovery' => true,
			'enable_email_verification' => true,
			'enable_password_confirmation' => true,
			'enable_login_rate_limit' => true,
			'enable_auth_captcha' => true,
			'enable_account_deletion' => true,
			'enable_reactions' => true,
			'enable_favorites_watch_later' => true,
			'enable_playlists' => true,
			'enable_continue_watching' => true,
			'enable_comment_reactions' => true,
			'enable_monetization' => false,
			'enable_creator_monetization' => false,
			'enable_creator_channels' => false,
			'ad_provider' => 'custom_html',
			'ad_publisher_id' => '',
			'ad_home_hero' => '',
			'ad_home_grid' => '',
			'ad_home_sidebar' => '',
			'ad_video_above' => '',
			'ad_video_below' => '',
			'ad_video_description' => '',
			'ad_player_overlay' => '',
			'ad_preroll_url' => '',
			'ad_postroll_url' => '',
			'facebook_url' => '',
			'x_url' => '',
			'storage_provider' => 'direct',
			'storage_endpoint' => '',
			'storage_bucket' => '',
			'storage_region' => 'auto',
			'storage_public_url' => '',
			'storage_access_key' => '',
			'storage_secret_key' => '',
			'home_section_order' => 'categories,trending,latest,most_viewed,category_rows,promo',
			'home_show_categories' => true,
			'home_show_trending' => true,
			'home_show_latest' => true,
			'home_show_most_viewed' => true,
			'home_show_category_rows' => true,
			'home_show_promo' => false,
			'home_featured_video' => 0,
			'home_videos_per_section' => 8,
			'home_trending_title' => 'Trending now',
			'home_latest_title' => 'Latest videos',
			'home_most_viewed_title' => 'Most viewed',
			'home_category_ids' => '',
			'home_hero_title' => '',
			'home_hero_description' => '',
			'home_hero_button' => 'Watch now',
			'home_promo_title' => '',
			'home_promo_content' => '',
			'home_promo_button' => '',
			'home_promo_url' => '',
		);
	}

	public static function get( $key = null ) {
		$options = wp_parse_args( get_option( 'cpwp_settings', array() ), self::defaults() );
		return null === $key ? $options : ( $options[ $key ] ?? null );
	}

	public static function register_settings() {
		register_setting( 'cpwp_settings_group', 'cpwp_settings', array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
	}

	public static function register_menu() {
		add_submenu_page( 'edit.php?post_type=cp_video', __( 'CP Settings', 'cp-wp-plugin' ), __( 'Settings', 'cp-wp-plugin' ), 'manage_options', 'cpwp-settings', array( __CLASS__, 'render' ) );
	}

	public static function site_types() {
		return array(
			'creator_platform' => __( 'YouTube-style creator platform', 'cp-wp-plugin' ),
			'streaming' => __( 'Video streaming site', 'cp-wp-plugin' ),
			'courses' => __( 'Online course platform', 'cp-wp-plugin' ),
			'gaming' => __( 'Gaming video platform', 'cp-wp-plugin' ),
			'membership' => __( 'Membership video community', 'cp-wp-plugin' ),
			'news' => __( 'News video website', 'cp-wp-plugin' ),
			'podcast' => __( 'Podcast and interview platform', 'cp-wp-plugin' ),
			'business_training' => __( 'Business training portal', 'cp-wp-plugin' ),
			'video_library' => __( 'Self-hosted video library', 'cp-wp-plugin' ),
			'affiliate' => __( 'Affiliate video website', 'cp-wp-plugin' ),
		);
	}

	private static function site_type_presets() {
		$community = array( 'enable_login' => true, 'enable_registration' => true, 'enable_comments' => true, 'enable_comment_reactions' => true, 'enable_reactions' => true, 'enable_favorites_watch_later' => true, 'enable_playlists' => true, 'enable_continue_watching' => true );
		return array(
			'creator_platform' => array_merge( $community, array( 'enable_creator_channels' => true, 'enable_monetization' => true, 'enable_creator_monetization' => true, 'show_sharing' => true, 'show_related' => true ) ),
			'streaming' => array_merge( $community, array( 'enable_creator_channels' => false, 'enable_creator_monetization' => false, 'enable_monetization' => true, 'show_related' => true ) ),
			'courses' => array_merge( $community, array( 'enable_creator_channels' => false, 'enable_creator_monetization' => false, 'enable_monetization' => false, 'show_transcript' => true, 'comments_login_only' => true ) ),
			'gaming' => array_merge( $community, array( 'enable_creator_channels' => true, 'enable_creator_monetization' => true, 'enable_monetization' => true, 'show_sharing' => true ) ),
			'membership' => array_merge( $community, array( 'enable_creator_channels' => false, 'enable_creator_monetization' => false, 'enable_monetization' => false, 'comments_login_only' => true ) ),
			'news' => array_merge( $community, array( 'enable_creator_channels' => false, 'enable_creator_monetization' => false, 'enable_monetization' => true, 'show_sharing' => true, 'enable_playlists' => false ) ),
			'podcast' => array_merge( $community, array( 'enable_creator_channels' => true, 'enable_creator_monetization' => true, 'enable_monetization' => true, 'show_transcript' => true ) ),
			'business_training' => array_merge( $community, array( 'enable_registration' => false, 'enable_creator_channels' => false, 'enable_creator_monetization' => false, 'enable_monetization' => false, 'enable_comments' => false, 'enable_comment_reactions' => false, 'enable_reactions' => false, 'show_sharing' => false, 'show_transcript' => true, 'comments_login_only' => true ) ),
			'video_library' => array_merge( $community, array( 'enable_registration' => false, 'enable_creator_channels' => false, 'enable_creator_monetization' => false, 'enable_monetization' => false, 'enable_comments' => false, 'enable_comment_reactions' => false, 'enable_reactions' => false, 'show_sharing' => false ) ),
			'affiliate' => array_merge( $community, array( 'enable_creator_channels' => false, 'enable_creator_monetization' => false, 'enable_monetization' => true, 'show_sharing' => true ) ),
		);
	}

	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$clean = array();
		foreach ( array( 'platform_name', 'tagline', 'footer_text', 'player_version', 'storage_bucket', 'storage_region', 'storage_access_key', 'home_section_order', 'home_trending_title', 'home_latest_title', 'home_most_viewed_title', 'home_category_ids', 'home_hero_title', 'home_hero_button', 'home_promo_title', 'home_promo_button', 'ad_publisher_id' ) as $key ) {
			$clean[ $key ] = sanitize_text_field( $input[ $key ] ?? '' );
		}
		foreach ( array( 'logo_url', 'custom_cdn', 'facebook_url', 'x_url', 'storage_endpoint', 'storage_public_url', 'home_promo_url' ) as $key ) {
			$clean[ $key ] = esc_url_raw( $input[ $key ] ?? '' );
		}
		$clean['accent_color'] = sanitize_hex_color( $input['accent_color'] ?? '' ) ?: $defaults['accent_color'];
		$site_type = sanitize_key( $input['site_type'] ?? $defaults['site_type'] );
		$clean['site_type'] = array_key_exists( $site_type, self::site_types() ) ? $site_type : $defaults['site_type'];
		$preload = sanitize_key( $input['default_preload'] ?? 'metadata' );
		$clean['default_preload'] = in_array( $preload, array( 'none', 'metadata', 'auto' ), true ) ? $preload : 'metadata';
		$provider = sanitize_key( $input['storage_provider'] ?? 'direct' );
		$clean['storage_provider'] = in_array( $provider, array( 'direct', 'r2', 's3', 's3_compatible' ), true ) ? $provider : 'direct';
		$ad_provider = sanitize_key( $input['ad_provider'] ?? 'custom_html' );
		$clean['ad_provider'] = in_array( $ad_provider, array( 'adsense', 'ad_manager', 'affiliate', 'custom_html', 'custom_javascript' ), true ) ? $ad_provider : 'custom_html';
		foreach ( array_keys( CPWP_Monetization::slots() ) as $slot ) $clean['ad_' . $slot] = CPWP_Monetization::sanitize_code( $input['ad_' . $slot] ?? '' );
		foreach ( array( 'ad_preroll_url', 'ad_postroll_url' ) as $key ) $clean[ $key ] = esc_url_raw( $input[ $key ] ?? '' );
		$existing = self::get();
		$clean['storage_secret_key'] = ! empty( $input['storage_secret_key'] ) ? sanitize_text_field( $input['storage_secret_key'] ) : $existing['storage_secret_key'];
		$clean['home_featured_video'] = absint( $input['home_featured_video'] ?? 0 );
		if ( isset( $input['home_category_ids_array'] ) && is_array( $input['home_category_ids_array'] ) ) $clean['home_category_ids'] = implode( ',', array_map( 'absint', $input['home_category_ids_array'] ) );
		$allowed_sections = array( 'categories', 'trending', 'latest', 'most_viewed', 'category_rows', 'promo' );
		$order = array_values( array_intersect( array_map( 'sanitize_key', explode( ',', $clean['home_section_order'] ) ), $allowed_sections ) );
		$clean['home_section_order'] = implode( ',', array_unique( array_merge( $order, array_diff( $allowed_sections, $order ) ) ) );
		$clean['home_videos_per_section'] = min( 24, max( 1, absint( $input['home_videos_per_section'] ?? 8 ) ) );
		$clean['home_hero_description'] = sanitize_textarea_field( $input['home_hero_description'] ?? '' );
		$clean['home_promo_content'] = sanitize_textarea_field( $input['home_promo_content'] ?? '' );
		foreach ( array( 'default_muted', 'show_sharing', 'show_transcript', 'show_related', 'enable_comments', 'enable_analytics', 'enable_login', 'enable_registration', 'comments_login_only', 'enable_password_recovery', 'enable_email_verification', 'enable_password_confirmation', 'enable_login_rate_limit', 'enable_auth_captcha', 'enable_account_deletion', 'enable_creator_channels', 'enable_reactions', 'enable_favorites_watch_later', 'enable_playlists', 'enable_continue_watching', 'enable_comment_reactions', 'enable_monetization', 'enable_creator_monetization', 'home_show_categories', 'home_show_trending', 'home_show_latest', 'home_show_most_viewed', 'home_show_category_rows', 'home_show_promo' ) as $key ) {
			$clean[ $key ] = ! empty( $input[ $key ] );
		}
		if ( ! empty( $input['apply_site_type_preset'] ) ) $clean = array_merge( $clean, self::site_type_presets()[ $clean['site_type'] ] );
		return $clean;
	}

	public static function render() {
		$options = self::get();
		?>
		<div class="wrap cpwp-settings"><h1><?php esc_html_e( 'CP Platform Settings', 'cp-wp-plugin' ); ?></h1><form method="post" action="options.php">
			<?php settings_fields( 'cpwp_settings_group' ); ?>
			<nav class="cpwp-tabs" role="tablist">
				<?php foreach ( array( 'site-type' => 'Site Type', 'branding' => 'Branding', 'player' => 'Player', 'features' => 'Video Features', 'users' => 'Users', 'monetization' => 'Monetization', 'storage' => 'Storage', 'homepage' => 'Homepage', 'social' => 'Social', 'tools' => 'Tools' ) as $id => $label ) : ?>
					<button type="button" role="tab" class="cpwp-tab <?php echo 'branding' === $id ? 'is-active' : ''; ?>" data-tab="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></button>
				<?php endforeach; ?>
			</nav>
			<div class="cpwp-tab-panel" data-panel="site-type"><div class="cpwp-settings-section"><h2><?php esc_html_e( 'Choose your video website type', 'cp-wp-plugin' ); ?></h2><p><?php esc_html_e( 'Apply a preset to configure recommended features. After applying it, you can freely enable or disable any individual feature.', 'cp-wp-plugin' ); ?></p><label><span><?php esc_html_e( 'Site type', 'cp-wp-plugin' ); ?></span><select name="cpwp_settings[site_type]"><?php foreach ( self::site_types() as $value => $label ) printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $options['site_type'], $value, false ), esc_html( $label ) ); ?></select></label><label><span><?php esc_html_e( 'Apply recommended settings', 'cp-wp-plugin' ); ?></span><input type="checkbox" name="cpwp_settings[apply_site_type_preset]" value="1"> <?php esc_html_e( 'Apply this preset when settings are saved', 'cp-wp-plugin' ); ?></label></div></div>
			<div class="cpwp-tab-panel is-active" data-panel="branding"><?php self::section( __( 'Branding', 'cp-wp-plugin' ), array( 'platform_name' => 'Platform name', 'tagline' => 'Tagline', 'logo_url' => 'Logo URL', 'accent_color' => 'Accent color', 'footer_text' => 'Footer text' ), $options ); ?></div>
			<div class="cpwp-tab-panel" data-panel="player"><?php self::section( __( 'Player defaults and CDN', 'cp-wp-plugin' ), array( 'player_version' => 'ChatyPlayer version', 'custom_cdn' => 'Custom CDN base URL', 'default_preload' => 'Default preload', 'default_muted' => 'Muted by default' ), $options ); ?></div>
			<div class="cpwp-tab-panel" data-panel="features"><?php self::section( __( 'Video page features', 'cp-wp-plugin' ), array( 'show_sharing' => 'Show sharing', 'show_transcript' => 'Show transcript', 'show_related' => 'Show related videos', 'enable_comments' => 'Enable comments', 'enable_comment_reactions' => 'Enable database-backed comment likes and dislikes', 'enable_analytics' => 'Enable analytics', 'enable_reactions' => 'Enable real video likes and dislikes', 'enable_favorites_watch_later' => 'Enable Favorites and Watch Later', 'enable_playlists' => 'Enable user playlists', 'enable_continue_watching' => 'Enable continue-watching progress' ), $options ); ?></div>
			<div class="cpwp-tab-panel" data-panel="users"><?php self::section( __( 'User access and security', 'cp-wp-plugin' ), array( 'enable_login' => 'Enable login page', 'enable_registration' => 'Enable registration page', 'enable_creator_channels' => 'Allow users to create creator channels with their own storage bucket', 'comments_login_only' => 'Only logged-in users can comment', 'enable_password_recovery' => 'Enable password recovery and reset emails', 'enable_email_verification' => 'Require email verification after registration', 'enable_password_confirmation' => 'Require current password before changing password', 'enable_login_rate_limit' => 'Enable login rate limiting', 'enable_auth_captcha' => 'Enable CAPTCHA on login, registration, and recovery', 'enable_account_deletion' => 'Allow users to delete their account' ), $options ); ?></div>
			<div class="cpwp-tab-panel" data-panel="monetization"><?php self::section( __( 'Monetization connections and ad placements', 'cp-wp-plugin' ), array( 'enable_monetization' => 'Enable monetization', 'enable_creator_monetization' => 'Allow approved creators to monetize below-player and description placements', 'ad_provider' => 'Ad provider/code type', 'ad_publisher_id' => 'Publisher/account ID', 'ad_home_hero' => 'Homepage hero ad code', 'ad_home_grid' => 'Homepage grid ad code', 'ad_home_sidebar' => 'Homepage sidebar ad code', 'ad_video_above' => 'Above player ad code', 'ad_video_below' => 'Below player ad code', 'ad_video_description' => 'Description ad code', 'ad_player_overlay' => 'Player overlay ad code', 'ad_preroll_url' => 'Pre-roll video URL', 'ad_postroll_url' => 'Post-roll video URL' ), $options ); ?><p class="description"><?php esc_html_e( 'Ad code can include AdSense, Ad Manager, affiliate banners, custom HTML, or custom JavaScript. Only trusted administrators should edit these fields.', 'cp-wp-plugin' ); ?></p></div>
			<div class="cpwp-tab-panel" data-panel="storage">
				<?php self::section( __( 'Storage connection', 'cp-wp-plugin' ), array( 'storage_provider' => 'Provider', 'storage_endpoint' => 'Endpoint URL', 'storage_bucket' => 'Bucket name', 'storage_region' => 'Region', 'storage_public_url' => 'Public/base URL', 'storage_access_key' => 'Access key', 'storage_secret_key' => 'Secret key' ), $options ); ?>
				<p class="description"><?php esc_html_e( 'For direct uploads, configure bucket CORS to allow PUT requests from this WordPress site. Never make access keys public.', 'cp-wp-plugin' ); ?></p>
				<p><button type="button" class="button" id="cpwp-test-storage"><?php esc_html_e( 'Save settings, then test storage connection', 'cp-wp-plugin' ); ?></button> <span id="cpwp-storage-result"></span></p>
				<div class="cpwp-settings-section"><h2><?php esc_html_e( 'Storage manager', 'cp-wp-plugin' ); ?></h2><p><button type="button" class="button" id="cpwp-list-storage"><?php esc_html_e( 'Load storage files', 'cp-wp-plugin' ); ?></button></p><div id="cpwp-storage-files"></div></div>
			</div>
			<div class="cpwp-tab-panel" data-panel="homepage"><?php self::section( __( 'Homepage builder', 'cp-wp-plugin' ), array( 'home_section_order' => 'Section order', 'home_featured_video' => 'Featured video', 'home_videos_per_section' => 'Videos per section', 'home_show_categories' => 'Show categories', 'home_show_trending' => 'Show trending', 'home_show_latest' => 'Show latest', 'home_show_most_viewed' => 'Show most viewed', 'home_show_category_rows' => 'Show category rows', 'home_show_promo' => 'Show promotion', 'home_trending_title' => 'Trending title', 'home_latest_title' => 'Latest title', 'home_most_viewed_title' => 'Most-viewed title', 'home_category_ids' => 'Categories', 'home_hero_title' => 'Hero title override', 'home_hero_description' => 'Hero description override', 'home_hero_button' => 'Hero button text', 'home_promo_title' => 'Promotion title', 'home_promo_content' => 'Promotion content', 'home_promo_button' => 'Promotion button text', 'home_promo_url' => 'Promotion URL' ), $options ); ?></div>
			<div class="cpwp-tab-panel" data-panel="social"><?php self::section( __( 'Social links', 'cp-wp-plugin' ), array( 'facebook_url' => 'Facebook URL', 'x_url' => 'X URL' ), $options ); ?></div>
			<div class="cpwp-tab-panel" data-panel="tools"><div class="cpwp-settings-section"><h2><?php esc_html_e( 'Import, export, and reset', 'cp-wp-plugin' ); ?></h2><p><button type="button" class="button" id="cpwp-export-settings"><?php esc_html_e( 'Export settings', 'cp-wp-plugin' ); ?></button> <button type="button" class="button" id="cpwp-import-settings"><?php esc_html_e( 'Import settings', 'cp-wp-plugin' ); ?></button> <button type="button" class="button button-link-delete" id="cpwp-reset-settings"><?php esc_html_e( 'Reset settings', 'cp-wp-plugin' ); ?></button></p></div></div>
			<div class="cpwp-settings-save"><?php submit_button(); ?></div>
		</form></div>
		<?php
	}

	private static function section( $title, $fields, $options ) {
		echo '<div class="cpwp-settings-section"><h2>' . esc_html( $title ) . '</h2>';
		foreach ( $fields as $key => $label ) {
			echo '<label><span>' . esc_html( $label ) . '</span>';
			if ( in_array( $key, array( 'default_muted', 'show_sharing', 'show_transcript', 'show_related', 'enable_comments', 'enable_analytics', 'enable_login', 'enable_registration', 'comments_login_only', 'enable_password_recovery', 'enable_email_verification', 'enable_password_confirmation', 'enable_login_rate_limit', 'enable_auth_captcha', 'enable_account_deletion', 'enable_creator_channels', 'enable_reactions', 'enable_favorites_watch_later', 'enable_playlists', 'enable_continue_watching', 'enable_comment_reactions', 'enable_monetization', 'enable_creator_monetization', 'home_show_categories', 'home_show_trending', 'home_show_latest', 'home_show_most_viewed', 'home_show_category_rows', 'home_show_promo' ), true ) ) {
				printf( '<input type="checkbox" name="cpwp_settings[%s]" value="1" %s>', esc_attr( $key ), checked( ! empty( $options[ $key ] ), true, false ) );
			} elseif ( 'accent_color' === $key ) {
				printf( '<input type="color" name="cpwp_settings[%s]" value="%s">', esc_attr( $key ), esc_attr( $options[ $key ] ) );
			} elseif ( 'default_preload' === $key ) {
				echo '<select name="cpwp_settings[default_preload]">';
				foreach ( array( 'metadata', 'auto', 'none' ) as $value ) printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $options[ $key ], $value, false ), esc_html( ucfirst( $value ) ) );
				echo '</select>';
			} elseif ( 'storage_provider' === $key ) {
				echo '<select name="cpwp_settings[storage_provider]">';
				foreach ( array( 'direct' => 'Direct public URL', 'r2' => 'Cloudflare R2', 's3' => 'Amazon S3', 's3_compatible' => 'S3-compatible' ) as $value => $name ) printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $options[ $key ], $value, false ), esc_html( $name ) );
				echo '</select>';
			} elseif ( 'ad_provider' === $key ) {
				echo '<select name="cpwp_settings[ad_provider]">';
				foreach ( array( 'adsense' => 'Google AdSense', 'ad_manager' => 'Google Ad Manager', 'affiliate' => 'Affiliate banners', 'custom_html' => 'Custom HTML', 'custom_javascript' => 'Custom JavaScript' ) as $value => $name ) printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $options[ $key ], $value, false ), esc_html( $name ) );
				echo '</select>';
			} elseif ( 'storage_secret_key' === $key ) {
				printf( '<input type="password" name="cpwp_settings[%s]" value="" placeholder="%s" autocomplete="new-password">', esc_attr( $key ), esc_attr( $options[ $key ] ? __( 'Secret saved; leave blank to keep it', 'cp-wp-plugin' ) : '' ) );
			} elseif ( 'home_section_order' === $key ) {
				$order = array_filter( array_map( 'sanitize_key', explode( ',', $options[ $key ] ) ) );
				echo '<ul class="cpwp-sortable" id="cpwp-section-order">';
				foreach ( $order as $item ) printf( '<li draggable="true" data-value="%s">%s</li>', esc_attr( $item ), esc_html( ucwords( str_replace( '_', ' ', $item ) ) ) );
				echo '</ul><input type="hidden" name="cpwp_settings[home_section_order]" value="' . esc_attr( $options[ $key ] ) . '">';
			} elseif ( 'home_featured_video' === $key ) {
				echo '<select name="cpwp_settings[home_featured_video]"><option value="0">' . esc_html__( 'Automatic most viewed', 'cp-wp-plugin' ) . '</option>';
				foreach ( get_posts( array( 'post_type' => 'cp_video', 'posts_per_page' => 100 ) ) as $video ) printf( '<option value="%d" %s>%s</option>', $video->ID, selected( $options[ $key ], $video->ID, false ), esc_html( get_the_title( $video ) ) );
				echo '</select>';
			} elseif ( 'home_category_ids' === $key ) {
				echo '<select multiple name="cpwp_settings[home_category_ids_array][]">';
				$selected = array_map( 'absint', explode( ',', $options[ $key ] ) );
				foreach ( get_categories( array( 'hide_empty' => false ) ) as $category ) printf( '<option value="%d" %s>%s</option>', $category->term_id, selected( in_array( $category->term_id, $selected, true ), true, false ), esc_html( $category->name ) );
				echo '</select>';
			} elseif ( in_array( $key, array_merge( array( 'home_hero_description', 'home_promo_content' ), array_map( function ( $slot ) { return 'ad_' . $slot; }, array_keys( CPWP_Monetization::slots() ) ) ), true ) ) {
				printf( '<textarea name="cpwp_settings[%s]" rows="4">%s</textarea>', esc_attr( $key ), esc_textarea( $options[ $key ] ) );
			} else {
				printf( '<input type="text" name="cpwp_settings[%s]" value="%s">', esc_attr( $key ), esc_attr( $options[ $key ] ) );
			}
			echo '</label>';
		}
		echo '</div>';
	}

	private static function verify_ajax( $action ) {
		check_ajax_referer( $action, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cp-wp-plugin' ) ), 403 );
	}
}
