<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Channels {
	const META = '_cpwp_creator_channel';
	const FOLLOWING_META = '_cpwp_followed_channels';
	const FOLLOWERS_META = '_cpwp_channel_followers';

	public static function register_routes() {
		add_rewrite_rule( '^channel/([^/]+)/?$', 'index.php?cpwp_channel=$matches[1]', 'top' );
		add_rewrite_tag( '%cpwp_channel%', '([^&]+)' );
	}

	public static function register_rest_routes() {
		register_rest_route( 'cpwp/v1', '/channels/(?P<owner_id>\d+)/follow', array(
			array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'follow_state' ), 'permission_callback' => '__return_true' ),
			array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'toggle_follow' ), 'permission_callback' => 'is_user_logged_in' ),
		) );
	}

	public static function follow_state( WP_REST_Request $request ) {
		$owner = absint( $request['owner_id'] ); if ( ! self::get( $owner ) ) return new WP_Error( 'invalid_channel', 'Invalid channel.', array( 'status' => 404 ) );
		return rest_ensure_response( array( 'following' => is_user_logged_in() && in_array( $owner, self::following(), true ), 'subscribers' => count( self::followers( $owner ) ), 'loginUrl' => CPWP_Users::login_url( self::public_url( self::get( $owner ) ) ) ) );
	}

	public static function toggle_follow( WP_REST_Request $request ) {
		$owner = absint( $request['owner_id'] ); $viewer = get_current_user_id();
		if ( ! self::get( $owner ) || $owner === $viewer ) return new WP_Error( 'invalid_channel', 'This channel cannot be followed.', array( 'status' => 400 ) );
		$following = self::following( $viewer ); $followers = self::followers( $owner );
		if ( in_array( $owner, $following, true ) ) { $following = array_values( array_diff( $following, array( $owner ) ) ); $followers = array_values( array_diff( $followers, array( $viewer ) ) ); }
		else { array_unshift( $following, $owner ); array_unshift( $followers, $viewer ); }
		update_user_meta( $viewer, self::FOLLOWING_META, array_values( array_unique( $following ) ) ); update_user_meta( $owner, self::FOLLOWERS_META, array_values( array_unique( $followers ) ) );
		CPWP_Moderation::log( in_array( $owner, $following, true ) ? 'channel_followed' : 'channel_unfollowed', $viewer, $owner );
		return self::follow_state( $request );
	}

	public static function following( $user_id = 0 ) { return array_values( array_filter( array_map( 'absint', (array) get_user_meta( $user_id ?: get_current_user_id(), self::FOLLOWING_META, true ) ) ) ); }
	public static function followers( $owner_id = 0 ) { return array_values( array_filter( array_map( 'absint', (array) get_user_meta( $owner_id ?: get_current_user_id(), self::FOLLOWERS_META, true ) ) ) ); }

	public static function followed_channels() {
		$result = array(); foreach ( self::following() as $owner ) { $channel = self::get( $owner ); $user = get_userdata( $owner ); if ( $channel && $user ) $result[] = array( 'user' => $user, 'channel' => $channel ); } return $result;
	}

	public static function render_followers() {
		$channel = self::get(); if ( ! $channel ) return; $followers = self::followers();
		echo '<section class="cpwp-channel-panel"><h2>' . esc_html__( 'Channel Followers', 'cp-wp-plugin' ) . '</h2><p>' . esc_html( sprintf( _n( '%d subscriber', '%d subscribers', count( $followers ), 'cp-wp-plugin' ), count( $followers ) ) ) . '</p>';
		if ( $followers ) { echo '<div class="cpwp-follower-list">'; foreach ( $followers as $id ) { $prefs = (array) get_user_meta( $id, CPWP_Creator_Platform::SUB_PREFS, true ); if ( ! empty( $prefs[ get_current_user_id() ]['private'] ) ) continue; $user = get_userdata( $id ); if ( $user ) echo '<div>' . get_avatar( $id, 36 ) . '<span>' . esc_html( $user->display_name ) . '</span></div>'; } echo '</div>'; } else echo '<p>' . esc_html__( 'Your channel has no followers yet.', 'cp-wp-plugin' ) . '</p>';
		echo '</section>';
	}

	public static function render_public() {
		$slug = sanitize_title( get_query_var( 'cpwp_channel' ) ); if ( ! $slug ) return;
		$owner = 0; $channel = array();
		foreach ( get_users( array( 'meta_key' => self::META ) ) as $user ) { $candidate = self::get( $user->ID ); if ( ( $candidate['slug'] ?? '' ) === $slug ) { $owner = $user->ID; $channel = $candidate; break; } }
		if ( ! $owner ) return;
		set_query_var( 'cpwp_public_channel', $channel ); set_query_var( 'cpwp_channel_owner', $owner ); status_header( 200 ); nocache_headers();
		$template = locate_template( 'channel.php' ); if ( $template ) { include $template; exit; }
	}

	public static function public_url( $channel ) { return home_url( '/channel/' . sanitize_title( $channel['slug'] ?? $channel['name'] ?? '' ) . '/' ); }

	public static function get( $user_id = 0 ) {
		$value = get_user_meta( $user_id ?: get_current_user_id(), self::META, true );
		if ( is_array( $value ) ) {
			foreach ( array( 'storage_access_key', 'storage_secret_key' ) as $key ) $value[ $key ] = CPWP_Security::decrypt( $value[ $key ] ?? '' );
		}
		return is_array( $value ) ? $value : array();
	}

	public static function save_from_request() {
		if ( ! CPWP_Settings::get( 'enable_creator_channels' ) || ! is_user_logged_in() ) return array( __( 'Creator channels are unavailable.', 'cp-wp-plugin' ), '' );
		$existing = self::get();
		$channel = array(
			'name' => sanitize_text_field( wp_unslash( $_POST['channel_name'] ?? '' ) ),
			'slug' => sanitize_title( wp_unslash( $_POST['channel_name'] ?? '' ) ),
			'description' => sanitize_textarea_field( wp_unslash( $_POST['channel_description'] ?? '' ) ),
			'logo_url' => esc_url_raw( wp_unslash( $_POST['channel_logo_url'] ?? '' ) ),
			'banner_url' => esc_url_raw( wp_unslash( $_POST['channel_banner_url'] ?? '' ) ),
			'accent_color' => sanitize_hex_color( wp_unslash( $_POST['channel_accent_color'] ?? '' ) ) ?: '#6d5dfc',
			'featured_video' => absint( $_POST['channel_featured_video'] ?? 0 ),
			'sections' => sanitize_text_field( wp_unslash( $_POST['channel_sections'] ?? 'featured,latest,community' ) ),
			'category' => sanitize_text_field( wp_unslash( $_POST['channel_category'] ?? '' ) ),
			'storage_endpoint' => esc_url_raw( wp_unslash( $_POST['channel_storage_endpoint'] ?? '' ) ),
			'storage_bucket' => sanitize_text_field( wp_unslash( $_POST['channel_storage_bucket'] ?? '' ) ),
			'storage_region' => sanitize_text_field( wp_unslash( $_POST['channel_storage_region'] ?? 'auto' ) ),
			'storage_public_url' => esc_url_raw( wp_unslash( $_POST['channel_storage_public_url'] ?? '' ) ),
			'storage_access_key' => sanitize_text_field( wp_unslash( $_POST['channel_storage_access_key'] ?? '' ) ),
			'storage_secret_key' => sanitize_text_field( wp_unslash( $_POST['channel_storage_secret_key'] ?? '' ) ),
			'created' => $existing['created'] ?? time(),
		);
		if ( ! $channel['storage_access_key'] ) $channel['storage_access_key'] = $existing['storage_access_key'] ?? '';
		if ( ! $channel['storage_secret_key'] ) $channel['storage_secret_key'] = $existing['storage_secret_key'] ?? '';
		if ( ! $channel['name'] || ! $channel['storage_endpoint'] || ! $channel['storage_bucket'] || ! $channel['storage_public_url'] || ! $channel['storage_access_key'] || ! $channel['storage_secret_key'] ) return array( __( 'Channel name and complete storage settings are required.', 'cp-wp-plugin' ), '' );
		if ( ! self::safe_url( $channel['storage_endpoint'] ) || ! self::safe_url( $channel['storage_public_url'] ) ) return array( __( 'Storage endpoint and public URL must use safe HTTPS URLs.', 'cp-wp-plugin' ), '' );
		$stored = $channel;
		foreach ( array( 'storage_access_key', 'storage_secret_key' ) as $key ) $stored[ $key ] = CPWP_Security::encrypt( $stored[ $key ] );
		update_user_meta( get_current_user_id(), self::META, $stored );
		$user = get_userdata( get_current_user_id() );
		$user->add_cap( 'upload_files' );
		$user->add_cap( 'edit_cp_videos' );
		$user->add_cap( 'publish_cp_videos' );
		return array( '', __( 'Your creator channel has been saved.', 'cp-wp-plugin' ) );
	}

	public static function render_form() {
		if ( ! CPWP_Settings::get( 'enable_creator_channels' ) ) return;
		$channel = self::get();
		?>
		<section class="cpwp-channel-panel"><h2><?php esc_html_e( 'Creator Channel', 'cp-wp-plugin' ); ?></h2><p><?php esc_html_e( 'Create your channel and connect your own S3-compatible storage bucket. Secret keys are never displayed after saving.', 'cp-wp-plugin' ); ?></p>
		<form method="post" class="cp-auth-form"><?php wp_nonce_field( 'cpwp_profile', 'cpwp_auth_nonce' ); ?><?php wp_nonce_field( 'cpwp_channel', 'cpwp_channel_nonce' ); ?>
		<label><span><?php esc_html_e( 'Channel name', 'cp-wp-plugin' ); ?></span><input name="channel_name" type="text" value="<?php echo esc_attr( $channel['name'] ?? '' ); ?>" required></label>
		<label><span><?php esc_html_e( 'Description', 'cp-wp-plugin' ); ?></span><textarea name="channel_description" rows="4"><?php echo esc_textarea( $channel['description'] ?? '' ); ?></textarea></label>
		<label><span><?php esc_html_e( 'Logo URL', 'cp-wp-plugin' ); ?></span><input name="channel_logo_url" type="url" value="<?php echo esc_attr( $channel['logo_url'] ?? '' ); ?>"></label>
		<label><span><?php esc_html_e( 'Banner URL', 'cp-wp-plugin' ); ?></span><input name="channel_banner_url" type="url" value="<?php echo esc_attr( $channel['banner_url'] ?? '' ); ?>"></label>
		<label><span><?php esc_html_e( 'Channel color', 'cp-wp-plugin' ); ?></span><input name="channel_accent_color" type="color" value="<?php echo esc_attr( $channel['accent_color'] ?? '#6d5dfc' ); ?>"></label>
		<label><span><?php esc_html_e( 'Category', 'cp-wp-plugin' ); ?></span><input name="channel_category" value="<?php echo esc_attr( $channel['category'] ?? '' ); ?>"></label>
		<label><span><?php esc_html_e( 'Featured video ID', 'cp-wp-plugin' ); ?></span><input name="channel_featured_video" type="number" value="<?php echo esc_attr( $channel['featured_video'] ?? '' ); ?>"></label>
		<label><span><?php esc_html_e( 'Sections (featured,latest,community)', 'cp-wp-plugin' ); ?></span><input name="channel_sections" value="<?php echo esc_attr( $channel['sections'] ?? 'featured,latest,community' ); ?>"></label>
		<label><span><?php esc_html_e( 'Storage endpoint', 'cp-wp-plugin' ); ?></span><input name="channel_storage_endpoint" type="url" value="<?php echo esc_attr( $channel['storage_endpoint'] ?? '' ); ?>" required></label>
		<label><span><?php esc_html_e( 'Bucket', 'cp-wp-plugin' ); ?></span><input name="channel_storage_bucket" type="text" value="<?php echo esc_attr( $channel['storage_bucket'] ?? '' ); ?>" required></label>
		<label><span><?php esc_html_e( 'Region', 'cp-wp-plugin' ); ?></span><input name="channel_storage_region" type="text" value="<?php echo esc_attr( $channel['storage_region'] ?? 'auto' ); ?>" required></label>
		<label><span><?php esc_html_e( 'Public/base URL', 'cp-wp-plugin' ); ?></span><input name="channel_storage_public_url" type="url" value="<?php echo esc_attr( $channel['storage_public_url'] ?? '' ); ?>" required></label>
		<label><span><?php esc_html_e( 'Access key', 'cp-wp-plugin' ); ?></span><input name="channel_storage_access_key" type="password" placeholder="<?php echo esc_attr( ! empty( $channel['storage_access_key'] ) ? __( 'Saved; leave blank to keep', 'cp-wp-plugin' ) : '' ); ?>"></label>
		<label><span><?php esc_html_e( 'Secret key', 'cp-wp-plugin' ); ?></span><input name="channel_storage_secret_key" type="password" placeholder="<?php echo esc_attr( ! empty( $channel['storage_secret_key'] ) ? __( 'Saved; leave blank to keep', 'cp-wp-plugin' ) : '' ); ?>"></label>
		<button class="cp-button" name="cpwp_save_channel" value="1" type="submit"><?php echo esc_html( $channel ? __( 'Update channel', 'cp-wp-plugin' ) : __( 'Create channel', 'cp-wp-plugin' ) ); ?></button></form></section>
		<?php
	}

	public static function ajax_presign_upload() {
		check_ajax_referer( 'cpwp_channel_upload', 'nonce' );
		if ( ! CPWP_Settings::get( 'enable_creator_channels' ) || ! is_user_logged_in() ) wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cp-wp-plugin' ) ), 403 );
		$channel = self::get();
		$name = sanitize_file_name( wp_unslash( $_POST['filename'] ?? '' ) );
		$type = sanitize_mime_type( wp_unslash( $_POST['content_type'] ?? 'application/octet-stream' ) );
		$allowed = false;
		if ( 0 === strpos( $type, 'video/' ) || 0 === strpos( $type, 'image/' ) ) {
			$allowed = true;
		} else {
			$ext = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
			if ( in_array( $ext, array( 'vtt', 'srt' ), true ) ) {
				$allowed = true;
			}
		}
		if ( ! $channel || ! $name || ! $allowed ) wp_send_json_error( array( 'message' => __( 'Create a channel first and upload a supported video, image, or subtitle file.', 'cp-wp-plugin' ) ) );
		$key = 'channels/' . get_current_user_id() . '/' . wp_date( 'Y/m' ) . '/' . wp_generate_uuid4() . '-' . $name;
		$url = self::presigned_put_url( $channel, $key, $type );
		if ( is_wp_error( $url ) ) wp_send_json_error( array( 'message' => $url->get_error_message() ) );
		wp_send_json_success( array( 'upload_url' => $url, 'public_url' => trailingslashit( $channel['storage_public_url'] ) . $key, 'content_type' => $type ) );
	}

	private static function presigned_put_url( $s, $key, $type ) {
		if ( ! self::safe_url( $s['storage_endpoint'] ?? '' ) ) return new WP_Error( 'invalid_storage', __( 'Invalid channel storage.', 'cp-wp-plugin' ) );
		$endpoint=untrailingslashit($s['storage_endpoint']); $bucket=$s['storage_bucket']; $region=$s['storage_region']?:'auto'; $access=$s['storage_access_key']; $secret=$s['storage_secret_key']; $host=wp_parse_url($endpoint,PHP_URL_HOST); $now=gmdate('Ymd\THis\Z'); $date=gmdate('Ymd'); $scope="{$date}/{$region}/s3/aws4_request"; $path='/'.rawurlencode($bucket).'/'.str_replace('%2F','/',rawurlencode($key));
		$query=array('X-Amz-Algorithm'=>'AWS4-HMAC-SHA256','X-Amz-Credential'=>$access.'/'.$scope,'X-Amz-Date'=>$now,'X-Amz-Expires'=>'900','X-Amz-SignedHeaders'=>'content-type;host'); ksort($query); $canonical=http_build_query($query,'','&',PHP_QUERY_RFC3986); $headers='content-type:'.$type."\nhost:".$host."\n"; $request="PUT\n{$path}\n{$canonical}\n{$headers}\ncontent-type;host\nUNSIGNED-PAYLOAD"; $string="AWS4-HMAC-SHA256\n{$now}\n{$scope}\n".hash('sha256',$request); $sign=hash_hmac('sha256','aws4_request',hash_hmac('sha256','s3',hash_hmac('sha256',$region,hash_hmac('sha256',$date,'AWS4'.$secret,true),true),true),true); $query['X-Amz-Signature']=hash_hmac('sha256',$string,$sign); return $endpoint.$path.'?'.http_build_query($query,'','&',PHP_QUERY_RFC3986);
	}

	private static function safe_url( $url ) {
		return 'https' === wp_parse_url( $url, PHP_URL_SCHEME ) && (bool) wp_parse_url( $url, PHP_URL_HOST );
	}
}
