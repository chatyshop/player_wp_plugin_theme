<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Creator_Platform {
	const VERIFY_STATUS = '_cpwp_channel_verification_status';
	const SUB_PREFS = '_cpwp_channel_subscription_preferences';

	public static function register_routes() {
		if ( ! CPWP_Settings::get( 'enable_creator_channels' ) ) return;
		register_rest_route( 'cpwp/v1', '/creator/video/(?P<video_id>\d+)', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'manage_video' ), 'permission_callback' => 'is_user_logged_in' ) );
		register_rest_route( 'cpwp/v1', '/creator/comments/(?P<comment_id>\d+)', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'manage_comment' ), 'permission_callback' => 'is_user_logged_in' ) );
		register_rest_route( 'cpwp/v1', '/channels/(?P<owner_id>\d+)/preferences', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'preferences' ), 'permission_callback' => 'is_user_logged_in' ) );
	}

	public static function process_profile() {
		if ( ! empty( $_POST['cpwp_request_verification'] ) ) { update_user_meta( get_current_user_id(), self::VERIFY_STATUS, 'pending' ); return array( '', __( 'Channel verification request submitted.', 'cp-wp-plugin' ) ); }
		return null;
	}

	public static function render_profile() {
		if ( ! CPWP_Settings::get( 'enable_creator_channels' ) || ! CPWP_Channels::get() ) return;
		$user_id = get_current_user_id(); $videos = self::videos( $user_id ); $views = $watch = $completions = 0;
		foreach ( $videos as $video ) { $views += absint( get_post_meta( $video->ID, '_cpwp_views', true ) ); $watch += absint( get_post_meta( $video->ID, '_cpwp_watch_time', true ) ); $completions += absint( get_post_meta( $video->ID, '_cpwp_completions', true ) ); }
		echo '<section class="cpwp-channel-panel"><h2>Creator analytics</h2><div class="cp-training-dashboard"><div><strong>' . esc_html( count( $videos ) ) . '</strong><span>Videos</span></div><div><strong>' . esc_html( $views ) . '</strong><span>Views</span></div><div><strong>' . esc_html( round( $watch / 3600, 1 ) ) . '</strong><span>Watch hours</span></div><div><strong>' . esc_html( $completions ) . '</strong><span>Completions</span></div><div><strong>' . esc_html( count( CPWP_Channels::followers() ) ) . '</strong><span>Subscribers</span></div></div></section>';
		echo '<section class="cpwp-channel-panel"><h2>Manage creator videos</h2><div class="cp-table-wrap"><table class="cp-suite-table"><thead><tr><th>Video</th><th>Status</th><th>Views</th><th>Action</th></tr></thead><tbody>'; foreach ( $videos as $video ) printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td><button class="cp-button" data-cpwp-creator-video="%s" data-action="toggle_status">%s</button> <button class="cp-button" data-cpwp-creator-video="%s" data-action="delete">Delete</button></td></tr>', esc_html( get_the_title( $video ) ), esc_html( $video->post_status ), esc_html( absint( get_post_meta( $video->ID, '_cpwp_views', true ) ) ), esc_attr( $video->ID ), 'publish' === $video->post_status ? 'Unpublish' : 'Publish', esc_attr( $video->ID ) ); echo '</tbody></table></div></section>';
		$status = get_user_meta( $user_id, self::VERIFY_STATUS, true ) ?: 'not requested'; echo '<section class="cpwp-channel-panel"><h2>Channel verification</h2><p>Status: <strong>' . esc_html( $status ) . '</strong></p>'; if ( ! in_array( $status, array( 'pending', 'approved' ), true ) ) { echo '<form method="post">'; wp_nonce_field( 'cpwp_profile', 'cpwp_auth_nonce' ); echo '<button class="cp-button" name="cpwp_request_verification" value="1">Request verification</button></form>'; } echo '</section>';
		echo '<section class="cpwp-channel-panel"><h2>Channel strikes and claims</h2><p><strong>' . esc_html( absint( get_user_meta( $user_id, '_cpwp_strikes', true ) ) ) . '</strong> active strikes</p>'; foreach ( CPWP_Moderation::user_cases( $user_id ) as $case ) printf( '<p><strong>%s</strong> · %s</p>', esc_html( $case->post_title ), esc_html( get_post_meta( $case->ID, '_cpwp_case_status', true ) ) ); echo '</section>';
		echo '<p><button class="cp-button" data-cpwp-report="copyright_dispute" data-target-id="' . esc_attr( $user_id ) . '">Submit copyright claim dispute</button></p>';
	}

	public static function manage_video( WP_REST_Request $request ) {
		$id = absint( $request['video_id'] ); if ( 'cp_video' !== get_post_type( $id ) || absint( get_post_field( 'post_author', $id ) ) !== get_current_user_id() ) return new WP_Error( 'forbidden', 'Video unavailable.', array( 'status' => 403 ) );
		$action = sanitize_key( $request['action'] );
		if ( 'toggle_status' === $action ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' === get_post_status( $id ) ? 'draft' : 'publish' ) );
		} elseif ( 'delete' === $action ) {
			wp_trash_post( $id );
		} elseif ( 'update' === $action ) {
			$post_data = array( 'ID' => $id );
			if ( isset( $request['title'] ) ) $post_data['post_title'] = sanitize_text_field( $request['title'] );
			if ( isset( $request['description'] ) ) $post_data['post_content'] = wp_kses_post( $request['description'] );
			if ( isset( $request['allow_comments'] ) ) $post_data['comment_status'] = ! empty( $request['allow_comments'] ) ? 'open' : 'closed';
			wp_update_post( $post_data );

			if ( isset( $request['accent_color'] ) ) update_post_meta( $id, '_cpwp_accent_color', sanitize_hex_color( $request['accent_color'] ) );
			if ( isset( $request['autoplay'] ) ) update_post_meta( $id, '_cpwp_autoplay', ! empty( $request['autoplay'] ) );
			if ( isset( $request['loop'] ) ) update_post_meta( $id, '_cpwp_loop', ! empty( $request['loop'] ) );
			if ( isset( $request['muted'] ) ) update_post_meta( $id, '_cpwp_muted', ! empty( $request['muted'] ) );
			if ( isset( $request['preload'] ) ) update_post_meta( $id, '_cpwp_preload', sanitize_key( $request['preload'] ) );
			if ( isset( $request['poster_url'] ) ) update_post_meta( $id, '_cpwp_poster_url', esc_url_raw( $request['poster_url'] ) );
			if ( isset( $request['video_url'] ) ) update_post_meta( $id, '_cpwp_mp4', esc_url_raw( $request['video_url'] ) );
			if ( isset( $request['chapters'] ) ) {
				$chapters = json_decode( $request['chapters'], true );
				update_post_meta( $id, '_cpwp_chapters', is_array( $chapters ) ? $chapters : array() );
			}
			if ( isset( $request['subtitles'] ) ) {
				$subtitles = json_decode( $request['subtitles'], true );
				update_post_meta( $id, '_cpwp_subtitles', is_array( $subtitles ) ? $subtitles : array() );
			}

			// Save taxonomies based on site type
			$site_type = CPWP_Settings::get( 'site_type' );
			if ( 'creator_platform' === $site_type ) {
				if ( isset( $request['video_genre'] ) ) {
					wp_set_post_terms( $id, array( absint( $request['video_genre'] ) ), 'cp_genre' );
				}
				if ( isset( $request['video_topic'] ) ) {
					wp_set_post_terms( $id, array( absint( $request['video_topic'] ) ), 'cp_topic' );
				}
				if ( isset( $request['video_tags'] ) ) {
					wp_set_post_terms( $id, sanitize_text_field( $request['video_tags'] ), 'cp_tag' );
				}
			} elseif ( 'gaming' === $site_type ) {
				if ( isset( $request['video_genre'] ) ) {
					wp_set_post_terms( $id, array( absint( $request['video_genre'] ) ), 'cp_genre' );
				}
				if ( isset( $request['video_game'] ) ) {
					wp_set_post_terms( $id, array( absint( $request['video_game'] ) ), 'cp_game' );
				}
				if ( isset( $request['video_tags'] ) ) {
					wp_set_post_terms( $id, sanitize_text_field( $request['video_tags'] ), 'cp_tag' );
				}
			} elseif ( 'podcast' === $site_type ) {
				if ( isset( $request['video_genre'] ) ) {
					wp_set_post_terms( $id, array( absint( $request['video_genre'] ) ), 'cp_genre' );
				}
				if ( isset( $request['video_topic'] ) ) {
					wp_set_post_terms( $id, array( absint( $request['video_topic'] ) ), 'cp_topic' );
				}
			}
		} else {
			return new WP_Error( 'invalid_action', 'Invalid action.', array( 'status' => 400 ) );
		}
		CPWP_Moderation::log( 'creator_video_' . $action, get_current_user_id(), $id ); return rest_ensure_response( array( 'success' => true ) );
	}

	public static function manage_comment( WP_REST_Request $request ) {
		$comment_id = absint( $request['comment_id'] );
		$comment = get_comment( $comment_id );
		if ( ! $comment ) return new WP_Error( 'not_found', 'Comment not found.', array( 'status' => 404 ) );

		$post = get_post( $comment->comment_post_ID );
		if ( ! $post || absint( $post->post_author ) !== get_current_user_id() ) {
			return new WP_Error( 'forbidden', 'Permission denied.', array( 'status' => 403 ) );
		}

		$action = sanitize_key( $request['action'] );
		if ( 'approve' === $action ) {
			wp_set_comment_status( $comment_id, 'approve' );
		} elseif ( 'unapprove' === $action ) {
			wp_set_comment_status( $comment_id, 'hold' );
		} elseif ( 'delete' === $action ) {
			wp_delete_comment( $comment_id, true );
		} else {
			return new WP_Error( 'invalid_action', 'Invalid action.', array( 'status' => 400 ) );
		}

		CPWP_Moderation::log( 'creator_comment_' . $action, get_current_user_id(), $comment_id );
		return rest_ensure_response( array( 'success' => true ) );
	}

	public static function preferences( WP_REST_Request $request ) {
		$owner = absint( $request['owner_id'] ); if ( ! in_array( $owner, CPWP_Channels::following(), true ) ) return new WP_Error( 'not_following', 'Follow this channel first.', array( 'status' => 400 ) );
		$prefs = (array) get_user_meta( get_current_user_id(), self::SUB_PREFS, true ); $prefs[ $owner ] = array( 'private' => ! empty( $request['private'] ), 'notifications' => ! empty( $request['notifications'] ) ); update_user_meta( get_current_user_id(), self::SUB_PREFS, $prefs ); return rest_ensure_response( $prefs[ $owner ] );
	}

	public static function channels( $search = '', $category = '' ) {
		$result = array(); foreach ( CPWP_Site_Modules::channels() as $item ) { $channel = $item['channel']; if ( $search && false === stripos( $channel['name'] . ' ' . $channel['description'], $search ) ) continue; if ( $category && $category !== ( $channel['category'] ?? '' ) ) continue; $result[] = $item; } return $result;
	}

	public static function videos( $owner ) { return get_posts( array( 'post_type' => 'cp_video', 'post_status' => array( 'publish', 'draft' ), 'posts_per_page' => 200, 'author' => absint( $owner ), 'meta_key' => '_cpwp_channel_owner', 'meta_value' => absint( $owner ) ) ); }
}
