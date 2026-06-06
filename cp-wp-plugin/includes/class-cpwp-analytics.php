<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_Analytics {
	public static function repair_legacy_data() {
		$videos = get_posts( array( 'post_type' => 'cp_video', 'posts_per_page' => -1, 'fields' => 'ids' ) );
		foreach ( $videos as $post_id ) {
			$watch_time = absint( get_post_meta( $post_id, '_cpwp_watch_time', true ) );
			$views      = max( 1, absint( get_post_meta( $post_id, '_cpwp_views', true ) ) );
			if ( $watch_time > $views * HOUR_IN_SECONDS * 12 || $watch_time > YEAR_IN_SECONDS ) {
				update_post_meta( $post_id, '_cpwp_watch_time', 0 );
			}
			$daily = get_post_meta( $post_id, '_cpwp_daily_analytics', true );
			if ( is_array( $daily ) ) {
				$daily = array_filter( $daily, 'is_array' );
				update_post_meta( $post_id, '_cpwp_daily_analytics', $daily );
			}
		}
	}

	public static function register_routes() {
		register_rest_route(
			'cpwp/v1',
			'/analytics',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'record' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id'    => array( 'required' => true, 'sanitize_callback' => 'absint' ),
					'event'      => array( 'required' => true, 'sanitize_callback' => 'sanitize_key' ),
					'watch_time' => array( 'sanitize_callback' => 'absint' ),
					'percent'    => array( 'sanitize_callback' => 'absint' ),
					'session'    => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
					'token'      => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
				),
			)
		);
	}

	public static function record( WP_REST_Request $request ) {
		$post_id = absint( $request['post_id'] );
		$event   = sanitize_key( $request['event'] );
		if ( 'cp_video' !== get_post_type( $post_id ) || ! hash_equals( wp_hash( 'cpwp-analytics-' . $post_id ), (string) $request['token'] ) || ! in_array( $event, array( 'play', 'progress', 'complete' ), true ) ) {
			return new WP_Error( 'invalid_video', __( 'Invalid analytics event.', 'cp-wp-plugin' ), array( 'status' => 400 ) );
		}

		$session_hash = hash_hmac( 'sha256', $post_id . '|' . $request['session'], wp_salt( 'nonce' ) );
		$session_key  = 'cpwp_' . substr( $session_hash, 0, 28 );
		$rate_key     = 'cpwp_rate_' . substr( $session_hash, 0, 24 );
		if ( get_transient( $rate_key ) ) return new WP_Error( 'rate_limited', __( 'Analytics event rate limited.', 'cp-wp-plugin' ), array( 'status' => 429 ) );
		set_transient( $rate_key, 1, 5 );
		$is_new_view  = false === get_transient( $session_key );
		if ( 'play' === $event && $is_new_view ) {
			set_transient( $session_key, 1, DAY_IN_SECONDS );
			update_post_meta( $post_id, '_cpwp_views', absint( get_post_meta( $post_id, '_cpwp_views', true ) ) + 1 );
			self::increment_daily( $post_id, 'views', 1 );
		}

		$watch_time = min( 60, absint( $request['watch_time'] ) );
		$percent    = min( 100, absint( $request['percent'] ) );
		if ( $watch_time > 0 ) {
			update_post_meta( $post_id, '_cpwp_watch_time', absint( get_post_meta( $post_id, '_cpwp_watch_time', true ) ) + $watch_time );
			self::increment_daily( $post_id, 'watch_time', $watch_time );
		}
		if ( 'complete' === $event && $percent >= 90 ) {
			update_post_meta( $post_id, '_cpwp_completions', absint( get_post_meta( $post_id, '_cpwp_completions', true ) ) + 1 );
			self::increment_daily( $post_id, 'completions', 1 );
		}

		return rest_ensure_response( array( 'recorded' => true, 'new_view' => $is_new_view ) );
	}

	public static function add_views_column( $columns ) {
		$columns['cpwp_views'] = __( 'Views', 'cp-wp-plugin' );
		$columns['cpwp_watch_time'] = __( 'Watch time', 'cp-wp-plugin' );
		return $columns;
	}

	public static function render_views_column( $column, $post_id ) {
		if ( 'cpwp_views' === $column ) {
			echo esc_html( number_format_i18n( absint( get_post_meta( $post_id, '_cpwp_views', true ) ) ) );
		}
		if ( 'cpwp_watch_time' === $column ) {
			echo esc_html( self::format_duration( absint( get_post_meta( $post_id, '_cpwp_watch_time', true ) ) ) );
		}
	}

	private static function format_duration( $seconds ) {
		$seconds = absint( $seconds );
		if ( $seconds < MINUTE_IN_SECONDS ) {
			return sprintf( __( '%s sec', 'cp-wp-plugin' ), number_format_i18n( $seconds ) );
		}
		if ( $seconds < HOUR_IN_SECONDS ) {
			return sprintf( __( '%s min', 'cp-wp-plugin' ), number_format_i18n( round( $seconds / MINUTE_IN_SECONDS ) ) );
		}
		return sprintf( __( '%s hr', 'cp-wp-plugin' ), number_format_i18n( round( $seconds / HOUR_IN_SECONDS, 1 ) ) );
	}

	private static function increment_daily( $post_id, $key, $amount ) {
		$daily = get_post_meta( $post_id, '_cpwp_daily_analytics', true );
		$daily = is_array( $daily ) ? $daily : array();
		$date  = wp_date( 'Y-m-d' );
		foreach ( $daily as $stored_date => $values ) {
			if ( ! is_array( $values ) ) {
				unset( $daily[ $stored_date ] );
			}
		}
		$daily[ $date ] = isset( $daily[ $date ] ) && is_array( $daily[ $date ] ) ? $daily[ $date ] : array();
		$daily[ $date ][ $key ] = absint( $daily[ $date ][ $key ] ?? 0 ) + absint( $amount );
		if ( count( $daily ) > 90 ) {
			$daily = array_slice( $daily, -90, null, true );
		}
		update_post_meta( $post_id, '_cpwp_daily_analytics', $daily );
	}
}
