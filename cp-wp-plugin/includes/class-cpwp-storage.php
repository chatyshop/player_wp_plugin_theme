<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_Storage {
	public static function ajax_list() {
		self::verify_manage_request( 'cpwp_manage_storage' );
		$response = self::signed_request( CPWP_Settings::get(), 'GET', '', 'list-type=2&max-keys=100&prefix=cp-videos%2F' );
		if ( is_wp_error( $response ) ) wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		$status = wp_remote_retrieve_response_code( $response );
		if ( $status < 200 || $status >= 300 ) wp_send_json_error( array( 'message' => sprintf( 'Storage returned HTTP %d.', $status ) ) );
		if ( ! function_exists( 'simplexml_load_string' ) ) wp_send_json_error( array( 'message' => __( 'The PHP SimpleXML extension is required to browse storage files.', 'cp-wp-plugin' ) ) );
		$xml = simplexml_load_string( wp_remote_retrieve_body( $response ) );
		$files = array();
		if ( $xml && isset( $xml->Contents ) ) foreach ( $xml->Contents as $item ) {
			$key = (string) $item->Key;
			$files[] = array( 'key' => $key, 'size' => size_format( (int) $item->Size ), 'modified' => (string) $item->LastModified, 'url' => self::public_url( CPWP_Settings::get(), $key ) );
		}
		wp_send_json_success( array( 'files' => $files ) );
	}

	public static function ajax_delete() {
		self::verify_manage_request( 'cpwp_manage_storage' );
		$key = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );
		if ( 0 !== strpos( $key, 'cp-videos/' ) || false !== strpos( $key, '..' ) ) wp_send_json_error( array( 'message' => __( 'Invalid storage key.', 'cp-wp-plugin' ) ) );
		$response = self::signed_request( CPWP_Settings::get(), 'DELETE', $key );
		if ( is_wp_error( $response ) ) wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		$status = wp_remote_retrieve_response_code( $response );
		$status >= 200 && $status < 300 ? wp_send_json_success() : wp_send_json_error( array( 'message' => sprintf( 'Delete failed: HTTP %d.', $status ) ) );
	}

	public static function ajax_presign_upload() {
		check_ajax_referer( 'cpwp_upload_storage', 'nonce' );
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cp-wp-plugin' ) ), 403 );
		}
		$settings = CPWP_Settings::get();
		if ( 'direct' === $settings['storage_provider'] ) {
			wp_send_json_error( array( 'message' => __( 'Select R2, S3, or S3-compatible storage for uploads.', 'cp-wp-plugin' ) ) );
		}
		$name = sanitize_file_name( wp_unslash( $_POST['filename'] ?? '' ) );
		$type = sanitize_mime_type( wp_unslash( $_POST['content_type'] ?? 'application/octet-stream' ) );
		if ( ! $name || ! self::allowed_type( $type ) ) {
			wp_send_json_error( array( 'message' => __( 'Unsupported file type.', 'cp-wp-plugin' ) ) );
		}
		$key = 'cp-videos/' . wp_date( 'Y/m' ) . '/' . wp_generate_uuid4() . '-' . $name;
		$url = self::presigned_put_url( $settings, $key, $type );
		if ( is_wp_error( $url ) ) wp_send_json_error( array( 'message' => $url->get_error_message() ) );
		$public = self::public_url( $settings, $key );
		wp_send_json_success( array( 'upload_url' => $url, 'public_url' => $public, 'content_type' => $type ) );
	}

	public static function ajax_test() {
		check_ajax_referer( 'cpwp_test_storage', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cp-wp-plugin' ) ), 403 );
		}

		$settings = CPWP_Settings::get();
		if ( 'direct' === $settings['storage_provider'] ) {
			$url = $settings['storage_public_url'];
			if ( ! $url ) wp_send_json_error( array( 'message' => __( 'Enter a public base URL.', 'cp-wp-plugin' ) ) );
			if ( ! self::safe_public_url( $url ) ) wp_send_json_error( array( 'message' => __( 'Public URL must use HTTPS and cannot target a private network.', 'cp-wp-plugin' ) ) );
			$response = wp_safe_remote_head( $url, array( 'timeout' => 12, 'redirection' => 2 ) );
		} else {
			$response = self::signed_request( $settings );
		}

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}
		$status = wp_remote_retrieve_response_code( $response );
		if ( $status >= 200 && $status < 400 ) {
			wp_send_json_success( array( 'message' => sprintf( __( 'Connection successful. HTTP %d', 'cp-wp-plugin' ), $status ) ) );
		}
		wp_send_json_error( array( 'message' => sprintf( __( 'Connection failed. HTTP %d: %s', 'cp-wp-plugin' ), $status, wp_strip_all_tags( wp_remote_retrieve_body( $response ) ) ) ) );
	}

	private static function signed_request( $settings, $method = 'GET', $key = '', $query = 'list-type=2&max-keys=1' ) {
		$endpoint = untrailingslashit( $settings['storage_endpoint'] );
		$bucket   = $settings['storage_bucket'];
		$region   = $settings['storage_region'] ?: 'auto';
		$access   = $settings['storage_access_key'];
		$secret   = $settings['storage_secret_key'];
		if ( ! $endpoint || ! $bucket || ! $access || ! $secret ) {
			return new WP_Error( 'missing_storage_settings', __( 'Endpoint, bucket, access key, and secret key are required.', 'cp-wp-plugin' ) );
		}
		if ( ! self::safe_public_url( $endpoint ) ) {
			return new WP_Error( 'unsafe_storage_endpoint', __( 'Storage endpoint must use HTTPS.', 'cp-wp-plugin' ) );
		}

		$path    = '/' . rawurlencode( $bucket ) . ( $key ? '/' . str_replace( '%2F', '/', rawurlencode( $key ) ) : '' );
		$url     = $endpoint . $path . ( $query ? '?' . $query : '' );
		$host    = wp_parse_url( $endpoint, PHP_URL_HOST );
		$now     = gmdate( 'Ymd\THis\Z' );
		$date    = gmdate( 'Ymd' );
		$payload = hash( 'sha256', '' );
		$headers = "host:{$host}\nx-amz-content-sha256:{$payload}\nx-amz-date:{$now}\n";
		$signed  = 'host;x-amz-content-sha256;x-amz-date';
		$request = "{$method}\n{$path}\n{$query}\n{$headers}\n{$signed}\n{$payload}";
		$scope   = "{$date}/{$region}/s3/aws4_request";
		$string  = "AWS4-HMAC-SHA256\n{$now}\n{$scope}\n" . hash( 'sha256', $request );
		$key     = hash_hmac( 'sha256', 'aws4_request', hash_hmac( 'sha256', 's3', hash_hmac( 'sha256', $region, hash_hmac( 'sha256', $date, 'AWS4' . $secret, true ), true ), true ), true );
		$auth    = 'AWS4-HMAC-SHA256 Credential=' . $access . '/' . $scope . ', SignedHeaders=' . $signed . ', Signature=' . hash_hmac( 'sha256', $string, $key );

		return wp_safe_remote_request( $url, array( 'method' => $method, 'timeout' => 15, 'headers' => array( 'Host' => $host, 'x-amz-date' => $now, 'x-amz-content-sha256' => $payload, 'Authorization' => $auth ) ) );
	}

	private static function presigned_put_url( $settings, $key, $content_type ) {
		$endpoint = untrailingslashit( $settings['storage_endpoint'] );
		$bucket = $settings['storage_bucket'];
		$region = $settings['storage_region'] ?: 'auto';
		$access = $settings['storage_access_key'];
		$secret = $settings['storage_secret_key'];
		if ( ! self::safe_public_url( $endpoint ) || ! $bucket || ! $access || ! $secret ) return new WP_Error( 'invalid_storage', __( 'Complete and save safe storage settings first.', 'cp-wp-plugin' ) );
		$host = wp_parse_url( $endpoint, PHP_URL_HOST );
		$now = gmdate( 'Ymd\THis\Z' );
		$date = gmdate( 'Ymd' );
		$scope = "{$date}/{$region}/s3/aws4_request";
		$path = '/' . rawurlencode( $bucket ) . '/' . str_replace( '%2F', '/', rawurlencode( $key ) );
		$query = array(
			'X-Amz-Algorithm' => 'AWS4-HMAC-SHA256',
			'X-Amz-Credential' => $access . '/' . $scope,
			'X-Amz-Date' => $now,
			'X-Amz-Expires' => '900',
			'X-Amz-SignedHeaders' => 'content-type;host',
		);
		ksort( $query );
		$canonical_query = http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );
		$canonical_headers = 'content-type:' . $content_type . "\nhost:" . $host . "\n";
		$request = "PUT\n{$path}\n{$canonical_query}\n{$canonical_headers}\ncontent-type;host\nUNSIGNED-PAYLOAD";
		$string = "AWS4-HMAC-SHA256\n{$now}\n{$scope}\n" . hash( 'sha256', $request );
		$signing_key = hash_hmac( 'sha256', 'aws4_request', hash_hmac( 'sha256', 's3', hash_hmac( 'sha256', $region, hash_hmac( 'sha256', $date, 'AWS4' . $secret, true ), true ), true ), true );
		$query['X-Amz-Signature'] = hash_hmac( 'sha256', $string, $signing_key );
		return $endpoint . $path . '?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );
	}

	private static function allowed_type( $type ) {
		return 0 === strpos( $type, 'video/' ) || 0 === strpos( $type, 'image/' ) || in_array( $type, array( 'text/vtt', 'text/plain', 'application/x-subrip' ), true );
	}

	private static function safe_public_url( $url ) {
		if ( 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) ) return false;
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! $host || 'localhost' === strtolower( $host ) || filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false && filter_var( $host, FILTER_VALIDATE_IP ) ) return false;
		return true;
	}

	private static function public_url( $settings, $key ) {
		return $settings['storage_public_url'] ? trailingslashit( $settings['storage_public_url'] ) . $key : untrailingslashit( $settings['storage_endpoint'] ) . '/' . rawurlencode( $settings['storage_bucket'] ) . '/' . str_replace( '%2F', '/', rawurlencode( $key ) );
	}

	private static function verify_manage_request( $action ) {
		check_ajax_referer( $action, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cp-wp-plugin' ) ), 403 );
	}
}
