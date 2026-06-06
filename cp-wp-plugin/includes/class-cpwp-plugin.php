<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_Plugin {
	private static $instance;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function activate() {
		require_once CPWP_DIR . 'post-types/class-cpwp-video-post-type.php';
		CPWP_Video_Post_Type::register();
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}

	public function run() {
		$this->load_dependencies();

		add_action( 'init', array( 'CPWP_Video_Post_Type', 'register' ) );
		add_action( 'wp_enqueue_scripts', array( 'CPWP_Assets', 'register_public_assets' ) );
		add_action( 'admin_enqueue_scripts', array( 'CPWP_Assets', 'enqueue_admin_assets' ) );
		add_action( 'admin_menu', array( 'CPWP_Dashboard', 'register' ) );
		add_action( 'admin_init', array( 'CPWP_Settings', 'register_settings' ) );
		add_action( 'admin_init', array( 'CPWP_Analytics', 'repair_legacy_data' ) );
		add_action( 'template_redirect', array( 'CPWP_Users', 'handle_authentication' ) );
		add_action( 'admin_init', array( 'CPWP_Users', 'block_user_admin' ) );
		add_action( 'wp_head', array( 'CPWP_SEO', 'render_meta' ), 5 );
		add_action( 'add_meta_boxes', array( 'CPWP_Video_Fields', 'add_meta_boxes' ) );
		add_action( 'save_post_cp_video', array( 'CPWP_Video_Fields', 'save' ) );
		add_filter( 'the_content', array( 'CPWP_Player_Renderer', 'prepend_to_video_content' ) );
		add_filter( 'the_content', array( 'CPWP_Video_Archive', 'render_archive_card' ), 20 );
		add_filter( 'posts_join', array( 'CPWP_Transcript', 'search_join' ), 10, 2 );
		add_filter( 'posts_search', array( 'CPWP_Transcript', 'search_content' ), 10, 2 );
		add_filter( 'posts_distinct', array( 'CPWP_Transcript', 'search_distinct' ), 10, 2 );
		add_action( 'rest_api_init', array( 'CPWP_Analytics', 'register_routes' ) );
		add_action( 'rest_api_init', array( 'CPWP_Engagement', 'register_routes' ) );
		add_action( 'wp_ajax_cpwp_test_storage', array( 'CPWP_Storage', 'ajax_test' ) );
		add_action( 'wp_ajax_cpwp_presign_upload', array( 'CPWP_Storage', 'ajax_presign_upload' ) );
		add_action( 'wp_ajax_cpwp_list_storage', array( 'CPWP_Storage', 'ajax_list' ) );
		add_action( 'wp_ajax_cpwp_delete_storage', array( 'CPWP_Storage', 'ajax_delete' ) );
		add_action( 'wp_ajax_cpwp_export_settings', array( 'CPWP_Settings', 'ajax_export' ) );
		add_action( 'wp_ajax_cpwp_import_settings', array( 'CPWP_Settings', 'ajax_import' ) );
		add_action( 'wp_ajax_cpwp_reset_settings', array( 'CPWP_Settings', 'ajax_reset' ) );
		add_filter( 'comments_open', array( 'CPWP_Plugin', 'comments_open' ), 10, 2 );
		add_filter( 'preprocess_comment', array( 'CPWP_Users', 'require_login_for_comment' ) );
		add_filter( 'show_admin_bar', array( 'CPWP_Users', 'show_admin_bar' ) );
		add_filter( 'manage_cp_video_posts_columns', array( 'CPWP_Analytics', 'add_views_column' ) );
		add_action( 'manage_cp_video_posts_custom_column', array( 'CPWP_Analytics', 'render_views_column' ), 10, 2 );
		add_shortcode( 'cp_player', array( 'CPWP_Shortcode', 'render' ) );
		add_shortcode( 'cp_video_grid', array( 'CPWP_Video_Archive', 'shortcode' ) );
	}

	private function load_dependencies() {
		require_once CPWP_DIR . 'post-types/class-cpwp-video-post-type.php';
		require_once CPWP_DIR . 'includes/class-cpwp-assets.php';
		require_once CPWP_DIR . 'includes/class-cpwp-player-renderer.php';
		require_once CPWP_DIR . 'includes/class-cpwp-shortcode.php';
		require_once CPWP_DIR . 'includes/class-cpwp-video-archive.php';
		require_once CPWP_DIR . 'includes/class-cpwp-analytics.php';
		require_once CPWP_DIR . 'includes/class-cpwp-transcript.php';
		require_once CPWP_DIR . 'includes/class-cpwp-seo.php';
		require_once CPWP_DIR . 'includes/class-cpwp-storage.php';
		require_once CPWP_DIR . 'includes/class-cpwp-users.php';
		require_once CPWP_DIR . 'includes/class-cpwp-engagement.php';
		require_once CPWP_DIR . 'admin/class-cpwp-video-fields.php';
		require_once CPWP_DIR . 'admin/class-cpwp-dashboard.php';
		require_once CPWP_DIR . 'admin/class-cpwp-settings.php';
	}

	public static function comments_open( $open, $post_id ) {
		return 'cp_video' === get_post_type( $post_id ) && ! CPWP_Settings::get( 'enable_comments' ) ? false : $open;
	}
}
