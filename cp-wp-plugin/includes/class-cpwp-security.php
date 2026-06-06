<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Security {
	public static function encrypt( $value ) {
		if ( ! $value || 0 === strpos( $value, 'cpenc:' ) ) return $value;
		if ( ! function_exists( 'openssl_encrypt' ) ) return $value;
		$key = hash( 'sha256', wp_salt( 'auth' ), true ); $iv = random_bytes( 16 );
		$cipher = openssl_encrypt( $value, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
		return $cipher ? 'cpenc:' . base64_encode( $iv . $cipher ) : $value;
	}

	public static function decrypt( $value ) {
		if ( ! is_string( $value ) || 0 !== strpos( $value, 'cpenc:' ) || ! function_exists( 'openssl_decrypt' ) ) return $value;
		$data = base64_decode( substr( $value, 6 ), true );
		if ( false === $data || strlen( $data ) <= 16 ) return '';
		return (string) openssl_decrypt( substr( $data, 16 ), 'aes-256-cbc', hash( 'sha256', wp_salt( 'auth' ), true ), OPENSSL_RAW_DATA, substr( $data, 0, 16 ) );
	}

	public static function download_url( $post_id ) {
		return add_query_arg( array( 'cpwp_download' => absint( $post_id ), 'expires' => time() + 10 * MINUTE_IN_SECONDS, 'token' => self::download_token( $post_id, time() + 10 * MINUTE_IN_SECONDS ) ), home_url( '/' ) );
	}

	private static function download_token( $post_id, $expires ) { return hash_hmac( 'sha256', $post_id . '|' . $expires, wp_salt( 'nonce' ) ); }

	public static function handle_download() {
		$post_id = absint( $_GET['cpwp_download'] ?? 0 ); if ( ! $post_id ) return;
		$expires = absint( $_GET['expires'] ?? 0 ); $token = sanitize_text_field( wp_unslash( $_GET['token'] ?? '' ) );
		if ( time() > $expires || ! hash_equals( self::download_token( $post_id, $expires ), $token ) ) wp_die( esc_html__( 'This download link has expired.', 'cp-wp-plugin' ), '', array( 'response' => 403 ) );
		if ( ! CPWP_Site_Modules::can_access_video( $post_id ) ) wp_die( esc_html__( 'You cannot download this video.', 'cp-wp-plugin' ), '', array( 'response' => 403 ) );
		$url = esc_url_raw( get_post_meta( $post_id, '_cpwp_download_url', true ) );
		if ( ! $url ) wp_die( esc_html__( 'Download unavailable.', 'cp-wp-plugin' ), '', array( 'response' => 404 ) );
		wp_redirect( $url ); exit;
	}

	public static function country_code() {
		foreach ( array( 'HTTP_CF_IPCOUNTRY', 'HTTP_X_COUNTRY_CODE', 'GEOIP_COUNTRY_CODE' ) as $key ) if ( ! empty( $_SERVER[ $key ] ) ) return strtoupper( sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
		return '';
	}
}
