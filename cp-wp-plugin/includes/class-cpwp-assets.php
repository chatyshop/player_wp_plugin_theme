<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_Assets {
	const PLAYER_VERSION = '1.0.7';

	public static function register_public_assets() {
		$version = CPWP_Settings::get( 'player_version' ) ?: self::PLAYER_VERSION;
		$base    = untrailingslashit( CPWP_Settings::get( 'custom_cdn' ) ?: 'https://cdn.jsdelivr.net/gh/chatyshop/chatyplayer@' . $version . '/dist' );
		wp_register_style(
			'chatyplayer',
			$base . '/index.css',
			array(),
			$version
		);
		wp_register_script(
			'chatyplayer',
			$base . '/chatyplayer.umd.min.js',
			array(),
			$version,
			true
		);
		wp_register_style( 'cpwp-public', CPWP_URL . 'public/css/public.css', array( 'chatyplayer' ), CPWP_VERSION );
		wp_register_script( 'cpwp-public', CPWP_URL . 'public/js/public.js', array( 'chatyplayer' ), CPWP_VERSION, true );
	}

	public static function enqueue_player_assets() {
		wp_enqueue_style( 'cpwp-public' );
		wp_enqueue_script( 'cpwp-public' );
		wp_localize_script( 'cpwp-public', 'cpwpEngagement', array(
			'base' => esc_url_raw( rest_url( 'cpwp/v1' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'loggedIn' => is_user_logged_in(),
			'loginUrl' => CPWP_Users::login_url( is_singular() ? get_permalink() : home_url( '/' ) ),
			'features' => array(
				'reactions' => (bool) CPWP_Settings::get( 'enable_reactions' ),
				'favorites' => (bool) CPWP_Settings::get( 'enable_favorites_watch_later' ),
				'playlists' => (bool) CPWP_Settings::get( 'enable_playlists' ),
				'progress' => (bool) CPWP_Settings::get( 'enable_continue_watching' ),
			),
		) );
		wp_localize_script( 'cpwp-public', 'cpwpCommentReactions', array(
			'base' => esc_url_raw( rest_url( 'cpwp/v1/comment-reactions/' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'enabled' => (bool) CPWP_Settings::get( 'enable_comment_reactions' ),
			'loggedIn' => is_user_logged_in(),
			'loginUrl' => CPWP_Users::login_url( is_singular() ? get_permalink() : home_url( '/' ) ),
		) );
		wp_localize_script( 'cpwp-public', 'cpwpPublic', array( 'restUrl' => esc_url_raw( rest_url( 'cpwp/v1/' ) ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
		if ( CPWP_Settings::get( 'enable_analytics' ) ) wp_localize_script(
			'cpwp-public',
			'cpwpAnalytics',
			array(
				'endpoint' => esc_url_raw( rest_url( 'cpwp/v1/analytics' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	public static function enqueue_admin_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || ( 'cp_video' !== $screen->post_type && ! in_array( $screen->id, array( 'cp_video_page_cpwp-dashboard', 'cp_video_page_cpwp-settings', 'cp_video_page_cpwp-users', 'cp_video_page_cpwp-comment-reactions', 'cp_video_page_cpwp-moderation', 'cp_video_page_cpwp-engagement', 'cp_video_page_cpwp-bulk-videos', 'cp_video_page_cpwp-creator-monetization' ), true ) ) ) {
			return;
		}

		wp_enqueue_style( 'cpwp-admin', CPWP_URL . 'admin/css/admin.css', array(), CPWP_VERSION );
		if ( 'cp_video_page_cpwp-settings' === $screen->id ) {
			wp_enqueue_script( 'cpwp-settings', CPWP_URL . 'admin/js/settings.js', array(), CPWP_VERSION, true );
			wp_localize_script( 'cpwp-settings', 'cpwpStorage', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'cpwp_test_storage' ), 'manageNonce' => wp_create_nonce( 'cpwp_manage_storage' ), 'settingsNonce' => wp_create_nonce( 'cpwp_manage_settings' ) ) );
		}
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'cpwp-video-fields', CPWP_URL . 'admin/js/video-fields.js', array(), CPWP_VERSION, true );
		wp_localize_script( 'cpwp-video-fields', 'cpwpVideoStorage', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'cpwp_upload_storage' ) ) );
	}
}
