<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Page_Suites {
	public static function pages() {
		return array(
			'streaming' => array( 'movies' => 'Movies', 'tv-shows' => 'TV Shows', 'seasons' => 'Seasons', 'watch-history' => 'Watch History' ),
			'courses' => array( 'my-courses' => 'My Courses', 'student-progress' => 'Student Progress', 'quiz-results' => 'Quiz Results', 'certificates' => 'Certificates' ),
			'podcast' => array( 'shows' => 'All Shows', 'guests' => 'Guests', 'downloads' => 'Downloadable Episodes' ),
			'news' => array( 'breaking-news' => 'Breaking News', 'topics' => 'Topics', 'locations' => 'Locations', 'corrections' => 'Corrections' ),
			'affiliate' => array( 'comparisons' => 'Comparisons', 'coupons' => 'Coupons', 'product-categories' => 'Product Categories' ),
			'gaming' => array( 'games' => 'Games Directory', 'tournaments' => 'Tournament Brackets' ),
			'creator_platform' => array( 'channels' => 'Channels Directory', 'following' => 'Following', 'community' => 'Community Posts', 'studio' => 'Creator Studio', 'upload' => 'Upload Video' ),
			'business_training' => array( 'assignments' => 'My Assignments', 'deadlines' => 'Deadlines', 'certificates' => 'Certificates', 'manager-reports' => 'Manager Reports' ),
			'video_library' => array( 'folders' => 'Folders', 'collections' => 'Collections Browser', 'download-history' => 'Download History' ),
			'membership' => array( 'groups' => 'Groups Directory', 'community-feed' => 'Community Feed' ),
		);
	}

	public static function current_pages() { return self::pages()[ CPWP_Settings::get( 'site_type' ) ] ?? array(); }
	public static function url( $slug ) { return home_url( '/discover/' . sanitize_title( $slug ) . '/' ); }

	public static function register_routes() {
		add_rewrite_rule( '^discover/([^/]+)/?$', 'index.php?cpwp_suite=$matches[1]', 'top' );
		add_rewrite_tag( '%cpwp_suite%', '([^&]+)' );
	}

	public static function render() {
		$slug = sanitize_title( get_query_var( 'cpwp_suite' ) ); $pages = self::current_pages();
		if ( ! $slug || ! isset( $pages[ $slug ] ) ) return;
		if ( self::requires_login( $slug ) && ! is_user_logged_in() ) { wp_safe_redirect( CPWP_Users::login_url( self::url( $slug ) ) ); exit; }
		if ( 'manager-reports' === $slug && ! current_user_can( 'edit_others_posts' ) ) wp_die( esc_html__( 'You do not have permission to view manager reports.', 'cp-wp-plugin' ), '', array( 'response' => 403 ) );
		set_query_var( 'cpwp_suite_slug', $slug ); set_query_var( 'cpwp_suite_title', $pages[ $slug ] ); status_header( 200 ); nocache_headers();
		$template = locate_template( 'page-suite.php' ); if ( $template ) { include $template; exit; }
	}

	private static function requires_login( $slug ) { return in_array( $slug, array( 'watch-history', 'my-courses', 'student-progress', 'quiz-results', 'certificates', 'following', 'assignments', 'deadlines', 'manager-reports', 'download-history', 'studio', 'upload' ), true ); }

	public static function data( $slug ) {
		switch ( $slug ) {
			case 'movies': return self::videos( array( array( 'key' => CPWP_Streaming::TYPE, 'value' => 'movie' ) ) );
			case 'tv-shows': case 'shows': return get_posts( array( 'post_type' => 'cp_series', 'posts_per_page' => 100 ) );
			case 'seasons': return self::videos( array( array( 'key' => CPWP_Streaming::TYPE, 'value' => 'episode' ), array( 'key' => '_cpwp_season', 'compare' => 'EXISTS' ) ) );
			case 'watch-history': return self::history_videos();
			case 'student-progress': return self::progress_videos();
			case 'my-courses': return get_posts( array( 'post_type' => 'cp_course', 'posts_per_page' => 100, 'post__in' => CPWP_Learning::enrolled_courses() ?: array( 0 ) ) );
			case 'quiz-results': return CPWP_Learning::attempts();
			case 'certificates': return CPWP_Learning::certificates();
			case 'guests': return get_posts( array( 'post_type' => 'cp_person', 'posts_per_page' => 100 ) );
			case 'downloads': return self::videos( array( array( 'key' => '_cpwp_download_url', 'compare' => 'EXISTS' ) ) );
			case 'breaking-news': return get_posts( array( 'post_type' => 'cp_news', 'posts_per_page' => 100, 'orderby' => 'date', 'order' => 'DESC' ) );
			case 'corrections': return self::videos( array( array( 'key' => '_cpwp_correction', 'compare' => 'EXISTS' ) ) );
			case 'topics': return get_terms( array( 'taxonomy' => 'cp_topic', 'hide_empty' => false ) );
			case 'locations': return get_terms( array( 'taxonomy' => 'cp_location', 'hide_empty' => false ) );
			case 'product-categories': return get_terms( array( 'taxonomy' => 'cp_genre', 'hide_empty' => false ) );
			case 'comparisons': return get_posts( array( 'post_type' => 'cp_product', 'posts_per_page' => 100, 'meta_key' => CPWP_Affiliate::COMPARE, 'meta_value' => '1' ) );
			case 'coupons': return get_posts( array( 'post_type' => 'cp_product', 'posts_per_page' => 100, 'meta_key' => CPWP_Affiliate::COUPON, 'meta_compare' => 'EXISTS' ) );
			case 'games': return get_terms( array( 'taxonomy' => 'cp_game', 'hide_empty' => false ) );
			case 'tournaments': return get_posts( array( 'post_type' => 'cp_event', 'posts_per_page' => 100 ) );
			case 'channels': return CPWP_Creator_Platform::channels( sanitize_text_field( wp_unslash( $_GET['channel_search'] ?? '' ) ), sanitize_text_field( wp_unslash( $_GET['channel_category'] ?? '' ) ) );
			case 'following': return CPWP_Channels::followed_channels();
			case 'community': case 'community-feed': return CPWP_Community::visible_posts();
			case 'assignments': case 'deadlines': return get_posts( array( 'post_type' => 'cp_course', 'posts_per_page' => 100, 'post__in' => CPWP_Learning::assigned_courses() ?: array( 0 ), 'meta_key' => '_cpwp_deadline', 'orderby' => 'meta_value', 'order' => 'ASC' ) );
			case 'manager-reports': return CPWP_Learning::manager_report();
			case 'folders': case 'groups': return get_posts( array( 'post_type' => 'cp_group', 'posts_per_page' => 100 ) );
			case 'collections': return get_posts( array( 'post_type' => 'cp_collection', 'posts_per_page' => 100 ) );
			case 'download-history': return self::progress_videos();
		}
		return array();
	}

	private static function videos( $meta_query ) { return get_posts( array( 'post_type' => 'cp_video', 'posts_per_page' => 100, 'meta_query' => $meta_query ) ); }
	private static function progress_videos() { $items = array(); foreach ( (array) get_user_meta( get_current_user_id(), '_cpwp_progress', true ) as $id => $progress ) if ( 'cp_video' === get_post_type( $id ) ) { $post = get_post( $id ); $post->cpwp_progress = $progress; $items[] = $post; } return $items; }
	private static function history_videos() { $items = array(); foreach ( (array) get_user_meta( get_current_user_id(), '_cpwp_watch_history', true ) as $id => $progress ) if ( 'cp_video' === get_post_type( $id ) ) { $post = get_post( $id ); $post->cpwp_progress = $progress; $items[] = $post; } return $items; }
}
