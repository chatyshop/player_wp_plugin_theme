<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Site_Modules {
	public static function types() {
		return array(
			'cp_collection' => array( 'Collections', 'Collection', array( 'streaming', 'video_library', 'creator_platform' ) ),
			'cp_series' => array( 'Series', 'Series', array( 'streaming', 'podcast', 'news' ) ),
			'cp_course' => array( 'Courses', 'Course', array( 'courses', 'business_training' ) ),
			'cp_lesson' => array( 'Lessons', 'Lesson', array( 'courses', 'business_training' ) ),
			'cp_quiz' => array( 'Quizzes', 'Quiz', array( 'courses', 'business_training' ) ),
			'cp_community' => array( 'Community Posts', 'Community Post', array( 'creator_platform', 'membership', 'gaming' ) ),
			'cp_news' => array( 'News Articles', 'News Article', array( 'news' ) ),
			'cp_person' => array( 'People and Guests', 'Person or Guest', array( 'podcast', 'news' ) ),
			'cp_event' => array( 'Events and Tournaments', 'Event or Tournament', array( 'gaming' ) ),
			'cp_product' => array( 'Affiliate Products', 'Affiliate Product', array( 'affiliate' ) ),
			'cp_group' => array( 'Groups and Departments', 'Group or Department', array( 'membership', 'business_training', 'video_library' ) ),
		);
	}

	public static function enabled( $post_type ) {
		$type = CPWP_Settings::get( 'site_type' );
		$types = self::types();
		return isset( $types[ $post_type ] ) && in_array( $type, $types[ $post_type ][2], true );
	}

	public static function register() {
		foreach ( self::types() as $type => $config ) {
			if ( ! self::enabled( $type ) ) continue;
			register_post_type( $type, array(
				'labels' => array( 'name' => __( $config[0], 'cp-wp-plugin' ), 'singular_name' => __( $config[1], 'cp-wp-plugin' ), 'add_new_item' => sprintf( __( 'Add New %s', 'cp-wp-plugin' ), $config[1] ) ),
				'public' => true, 'show_in_rest' => true, 'show_in_menu' => 'edit.php?post_type=cp_video',
				'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields', 'comments' ),
				'has_archive' => true, 'rewrite' => array( 'slug' => str_replace( 'cp_', '', $type ) ),
			) );
		}
		foreach ( array( 'cp_genre' => 'Genres', 'cp_game' => 'Games', 'cp_topic' => 'Topics', 'cp_location' => 'Locations', 'cp_tag' => 'Video Tags' ) as $taxonomy => $label ) {
			register_taxonomy( $taxonomy, array_merge( array( 'cp_video' ), array_keys( self::types() ) ), array( 'label' => __( $label, 'cp-wp-plugin' ), 'public' => true, 'show_in_rest' => true, 'hierarchical' => 'cp_tag' !== $taxonomy ) );
		}
	}

	public static function add_meta_boxes() {
		add_meta_box( 'cpwp-module-video', __( 'Site Type and Access Details', 'cp-wp-plugin' ), array( __CLASS__, 'render_video_fields' ), 'cp_video', 'side', 'default' );
		foreach ( array_keys( self::types() ) as $type ) if ( self::enabled( $type ) ) add_meta_box( 'cpwp-module-details', __( 'Module Details', 'cp-wp-plugin' ), array( __CLASS__, 'render_module_fields' ), $type, 'side', 'default' );
	}

	public static function render_video_fields( $post ) {
		wp_nonce_field( 'cpwp_save_module_fields', 'cpwp_module_nonce' );
		self::select( $post->ID, '_cpwp_visibility', 'Visibility', array( 'public' => 'Public', 'members' => 'Logged-in users', 'roles' => 'Selected roles', 'unlisted' => 'Unlisted' ) );
		self::input( $post->ID, '_cpwp_access_roles', 'Allowed roles (comma separated)' );
		self::input( $post->ID, '_cpwp_release_date', 'Release/drip date', 'datetime-local' );
		if ( class_exists( 'CPWP_Streaming' ) && 'streaming' === CPWP_Settings::get( 'site_type' ) ) CPWP_Streaming::render_fields( $post );
		self::input( $post->ID, '_cpwp_series_name', 'Series/course/show name' );
		self::input( $post->ID, '_cpwp_season', 'Season/section number', 'number' );
		self::input( $post->ID, '_cpwp_episode', 'Episode/lesson number', 'number' );
		self::input( $post->ID, '_cpwp_age_rating', 'Age rating' );
		self::input( $post->ID, '_cpwp_geo_allow', 'Allowed country codes' );
		self::input( $post->ID, '_cpwp_download_url', 'Download URL', 'url' );
		self::input( $post->ID, '_cpwp_affiliate_url', 'Affiliate URL', 'url' );
		self::input( $post->ID, '_cpwp_correction', 'Correction/fact-check note' );
		echo '<label><input type="checkbox" name="_cpwp_vertical" value="1" ' . checked( get_post_meta( $post->ID, '_cpwp_vertical', true ), '1', false ) . '> ' . esc_html__( 'Vertical/short video', 'cp-wp-plugin' ) . '</label>';
	}

	public static function render_module_fields( $post ) {
		wp_nonce_field( 'cpwp_save_module_fields', 'cpwp_module_nonce' );
		self::input( $post->ID, '_cpwp_parent_item', 'Parent/related item ID', 'number' );
		self::input( $post->ID, '_cpwp_order', 'Display order', 'number' );
		self::input( $post->ID, '_cpwp_deadline', 'Deadline or publish date', 'datetime-local' );
		self::input( $post->ID, '_cpwp_external_url', 'External, affiliate, or RSS URL', 'url' );
		self::input( $post->ID, '_cpwp_badge', 'Badge, rating, or label' );
	}

	private static function input( $post_id, $key, $label, $type = 'text' ) {
		printf( '<p><label><strong>%s</strong><br><input style="width:100%%" type="%s" name="%s" value="%s"></label></p>', esc_html( $label ), esc_attr( $type ), esc_attr( $key ), esc_attr( get_post_meta( $post_id, $key, true ) ) );
	}

	private static function select( $post_id, $key, $label, $options ) {
		echo '<p><label><strong>' . esc_html( $label ) . '</strong><br><select style="width:100%" name="' . esc_attr( $key ) . '">';
		$current = get_post_meta( $post_id, $key, true ) ?: 'public';
		foreach ( $options as $value => $name ) printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $current, $value, false ), esc_html( $name ) );
		echo '</select></label></p>';
	}

	public static function save( $post_id ) {
		if ( ! isset( $_POST['cpwp_module_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpwp_module_nonce'] ) ), 'cpwp_save_module_fields' ) || ! current_user_can( 'edit_post', $post_id ) ) return;
		$urls = array( '_cpwp_download_url', '_cpwp_affiliate_url', '_cpwp_external_url' );
		$numbers = array( '_cpwp_season', '_cpwp_episode', '_cpwp_parent_item', '_cpwp_order' );
		$fields = array( '_cpwp_visibility', '_cpwp_access_roles', '_cpwp_release_date', '_cpwp_series_name', '_cpwp_season', '_cpwp_episode', '_cpwp_age_rating', '_cpwp_geo_allow', '_cpwp_download_url', '_cpwp_affiliate_url', '_cpwp_correction', '_cpwp_parent_item', '_cpwp_order', '_cpwp_deadline', '_cpwp_external_url', '_cpwp_badge' );
		foreach ( $fields as $key ) {
			$value = wp_unslash( $_POST[ $key ] ?? '' );
			$value = in_array( $key, $urls, true ) ? esc_url_raw( $value ) : ( in_array( $key, $numbers, true ) ? absint( $value ) : sanitize_text_field( $value ) );
			$value ? update_post_meta( $post_id, $key, $value ) : delete_post_meta( $post_id, $key );
		}
		! empty( $_POST['_cpwp_vertical'] ) ? update_post_meta( $post_id, '_cpwp_vertical', '1' ) : delete_post_meta( $post_id, '_cpwp_vertical' );
		if ( class_exists( 'CPWP_Streaming' ) ) CPWP_Streaming::save( $post_id );
	}

	public static function protect_video() {
		if ( ! is_singular( 'cp_video' ) ) return;
		if ( ! self::can_access_video( get_queried_object_id() ) ) {
			set_query_var( 'cpwp_unavailable_message', __( 'Your account or location cannot access this video.', 'cp-wp-plugin' ) );
			status_header( 403 ); nocache_headers();
			$template = locate_template( 'cpwp-unavailable.php' );
			if ( $template ) { include $template; exit; }
			self::deny( __( 'Your account or location cannot access this video.', 'cp-wp-plugin' ) );
		}
	}

	public static function can_access_video( $post_id ) {
		if ( current_user_can( 'edit_post', $post_id ) ) return true;
		$release = get_post_meta( $post_id, '_cpwp_release_date', true );
		if ( $release && strtotime( $release ) > current_time( 'timestamp' ) ) return false;
		$visibility = get_post_meta( $post_id, '_cpwp_visibility', true );
		if ( 'members' === $visibility && ! is_user_logged_in() ) return false;
		if ( 'roles' === $visibility ) {
			$allowed = array_filter( array_map( 'sanitize_key', explode( ',', get_post_meta( $post_id, '_cpwp_access_roles', true ) ) ) );
			if ( ! array_intersect( $allowed, wp_get_current_user()->roles ) ) return false;
		}
		$countries = array_filter( array_map( 'strtoupper', array_map( 'trim', explode( ',', get_post_meta( $post_id, '_cpwp_geo_allow', true ) ) ) ) );
		$country = CPWP_Security::country_code();
		return ! $countries || ! $country || in_array( $country, $countries, true );
	}

	private static function deny( $message ) { wp_die( esc_html( $message ), esc_html__( 'Video unavailable', 'cp-wp-plugin' ), array( 'response' => 403 ) ); }

	public static function hide_unlisted( $query ) {
		if ( is_admin() || ! $query->is_main_query() || $query->is_singular() ) return;
		$post_type = $query->get( 'post_type' );
		if ( 'cp_video' !== $post_type && ! $query->is_post_type_archive( 'cp_video' ) ) return;
		$query->set( 'meta_query', array( 'relation' => 'OR', array( 'key' => '_cpwp_visibility', 'compare' => 'NOT EXISTS' ), array( 'key' => '_cpwp_visibility', 'value' => 'unlisted', 'compare' => '!=' ) ) );
	}

	public static function navigation() {
		$links = array();
		foreach ( self::types() as $type => $config ) if ( self::enabled( $type ) ) $links[] = array( 'label' => $config[0], 'url' => get_post_type_archive_link( $type ) );
		return $links;
	}

	public static function children( $parent_id, $types ) {
		return get_posts( array( 'post_type' => (array) $types, 'post_status' => 'publish', 'posts_per_page' => 100, 'meta_key' => '_cpwp_parent_item', 'meta_value' => absint( $parent_id ), 'orderby' => 'meta_value_num', 'order' => 'ASC' ) );
	}

	public static function related_videos( $name ) {
		$series = get_page_by_title( $name, OBJECT, 'cp_series' );
		if ( $series && class_exists( 'CPWP_Streaming' ) ) { $episodes = CPWP_Streaming::episodes( $series->ID ); if ( $episodes ) return $episodes; }
		return get_posts( array( 'post_type' => 'cp_video', 'post_status' => 'publish', 'posts_per_page' => 100, 'meta_key' => '_cpwp_series_name', 'meta_value' => sanitize_text_field( $name ), 'orderby' => array( 'meta_value_num' => 'ASC', 'date' => 'ASC' ) ) );
	}

	public static function channels() {
		$result = array();
		foreach ( get_users( array( 'meta_key' => CPWP_Channels::META ) ) as $user ) { $channel = CPWP_Channels::get( $user->ID ); if ( $channel ) $result[] = array( 'user' => $user, 'channel' => $channel ); }
		return $result;
	}
}
