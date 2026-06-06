<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Engagement {
	public static function register_menu() { add_submenu_page( 'edit.php?post_type=cp_video', 'Engagement', 'Engagement', 'manage_options', 'cpwp-engagement', array( __CLASS__, 'render_admin' ) ); }

	public static function render_admin() {
		if ( ! empty( $_POST['cpwp_clear_engagement'] ) && check_admin_referer( 'cpwp_clear_engagement' ) ) {
			$user_id = absint( $_POST['user_id'] ); foreach ( array( '_cpwp_reactions', '_cpwp_favorites', '_cpwp_watch_later', '_cpwp_progress', '_cpwp_watch_history', '_cpwp_playlists' ) as $key ) delete_user_meta( $user_id, $key );
			CPWP_Moderation::log( 'engagement_cleared', get_current_user_id(), $user_id );
		}
		$users = get_users( array( 'number' => 200 ) );
		echo '<div class="wrap"><h1>Engagement Administration</h1><table class="widefat striped"><thead><tr><th>User</th><th>Reactions</th><th>Favorites</th><th>Watch Later</th><th>Progress</th><th>Playlists</th><th>Action</th></tr></thead><tbody>';
		foreach ( $users as $user ) { $counts = array(); foreach ( array( '_cpwp_reactions', '_cpwp_favorites', '_cpwp_watch_later', '_cpwp_progress', '_cpwp_playlists' ) as $key ) $counts[] = count( (array) get_user_meta( $user->ID, $key, true ) );
			echo '<tr><td>' . esc_html( $user->display_name ) . '</td>'; foreach ( $counts as $count ) echo '<td>' . esc_html( $count ) . '</td>'; echo '<td><form method="post">'; wp_nonce_field( 'cpwp_clear_engagement' ); echo '<input type="hidden" name="user_id" value="' . esc_attr( $user->ID ) . '"><button class="button" name="cpwp_clear_engagement" value="1">Clear data</button></form></td></tr>'; }
		echo '</tbody></table></div>';
	}

	public static function register_routes() {
		register_rest_route( 'cpwp/v1', '/engagement/(?P<post_id>\d+)', array( array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'state' ), 'permission_callback' => '__return_true' ), array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'update' ), 'permission_callback' => array( __CLASS__, 'logged_in' ) ) ) );
		register_rest_route( 'cpwp/v1', '/library', array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'library' ), 'permission_callback' => array( __CLASS__, 'logged_in' ) ) );
	}

	public static function logged_in() { return is_user_logged_in(); }

	public static function state( WP_REST_Request $request ) {
		$post_id = absint( $request['post_id'] );
		if ( 'cp_video' !== get_post_type( $post_id ) ) return new WP_Error( 'invalid_video', 'Invalid video.', array( 'status' => 404 ) );
		$user_id = get_current_user_id();
		$reactions = $user_id ? self::map( $user_id, '_cpwp_reactions' ) : array();
		$playlists = $user_id ? self::playlists( $user_id ) : array();
		return rest_ensure_response( array(
			'loggedIn' => is_user_logged_in(),
			'loginUrl' => CPWP_Users::login_url( get_permalink( $post_id ) ),
			'reaction' => $reactions[ $post_id ] ?? '',
			'likes' => absint( get_post_meta( $post_id, '_cpwp_likes', true ) ),
			'dislikes' => absint( get_post_meta( $post_id, '_cpwp_dislikes', true ) ),
			'favorite' => $user_id ? in_array( $post_id, self::ids( $user_id, '_cpwp_favorites' ), true ) : false,
			'watchLater' => $user_id ? in_array( $post_id, self::ids( $user_id, '_cpwp_watch_later' ), true ) : false,
			'progress' => $user_id ? ( self::map( $user_id, '_cpwp_progress' )[ $post_id ] ?? array() ) : array(),
			'playlists' => array_map( function ( $list ) use ( $post_id ) { return array( 'id' => $list['id'], 'name' => $list['name'], 'contains' => in_array( $post_id, $list['videos'], true ) ); }, $playlists ),
		) );
	}

	public static function update( WP_REST_Request $request ) {
		$post_id = absint( $request['post_id'] );
		if ( 'cp_video' !== get_post_type( $post_id ) ) return new WP_Error( 'invalid_video', 'Invalid video.', array( 'status' => 404 ) );
		$user_id = get_current_user_id();
		$action = sanitize_key( $request['action'] );
		if ( 'reaction' === $action && CPWP_Settings::get( 'enable_reactions' ) ) self::reaction( $user_id, $post_id, sanitize_key( $request['value'] ) );
		elseif ( in_array( $action, array( 'favorite', 'watch_later' ), true ) && CPWP_Settings::get( 'enable_favorites_watch_later' ) ) self::toggle_id( $user_id, '_cpwp_' . $action, $post_id );
		elseif ( 'progress' === $action && CPWP_Settings::get( 'enable_continue_watching' ) ) self::progress( $user_id, $post_id, $request );
		elseif ( 'playlist' === $action && CPWP_Settings::get( 'enable_playlists' ) ) self::playlist( $user_id, $post_id, $request );
		else return new WP_Error( 'disabled_action', 'Feature unavailable.', array( 'status' => 400 ) );
		return self::state( $request );
	}

	public static function library() {
		$user_id = get_current_user_id();
		return rest_ensure_response( array( 'favorites' => self::video_data( self::ids( $user_id, '_cpwp_favorites' ) ), 'watchLater' => self::video_data( self::ids( $user_id, '_cpwp_watch_later' ) ), 'progress' => self::progress_data( self::map( $user_id, '_cpwp_progress' ) ), 'playlists' => array_map( function ( $list ) { $list['videos'] = self::video_data( $list['videos'] ); return $list; }, self::playlists( $user_id ) ) ) );
	}

	private static function reaction( $user_id, $post_id, $value ) {
		if ( ! in_array( $value, array( 'like', 'dislike', '' ), true ) ) return;
		$map = self::map( $user_id, '_cpwp_reactions' );
		$old = $map[ $post_id ] ?? '';
		if ( $old ) update_post_meta( $post_id, '_cpwp_' . $old . 's', max( 0, absint( get_post_meta( $post_id, '_cpwp_' . $old . 's', true ) ) - 1 ) );
		if ( $value && $value !== $old ) {
			$map[ $post_id ] = $value;
			update_post_meta( $post_id, '_cpwp_' . $value . 's', absint( get_post_meta( $post_id, '_cpwp_' . $value . 's', true ) ) + 1 );
		} else unset( $map[ $post_id ] );
		update_user_meta( $user_id, '_cpwp_reactions', $map );
	}

	private static function toggle_id( $user_id, $key, $post_id ) {
		$ids = self::ids( $user_id, $key );
		if ( in_array( $post_id, $ids, true ) ) $ids = array_values( array_diff( $ids, array( $post_id ) ) ); else array_unshift( $ids, $post_id );
		update_user_meta( $user_id, $key, array_slice( $ids, 0, 500 ) );
	}

	private static function progress( $user_id, $post_id, $request ) {
		$map = self::map( $user_id, '_cpwp_progress' );
		$percent = min( 100, max( 0, (float) $request['percent'] ) );
		$history = self::map( $user_id, '_cpwp_watch_history' );
		$history[ $post_id ] = array( 'time' => max( 0, (float) $request['time'] ), 'duration' => max( 0, (float) $request['duration'] ), 'percent' => $percent, 'updated' => time(), 'completed' => $percent >= 95 );
		uasort( $history, function ( $a, $b ) { return ( $b['updated'] ?? 0 ) <=> ( $a['updated'] ?? 0 ); } );
		update_user_meta( $user_id, '_cpwp_watch_history', array_slice( $history, 0, 500, true ) );
		if ( $percent >= 95 ) unset( $map[ $post_id ] ); else $map[ $post_id ] = array( 'time' => max( 0, (float) $request['time'] ), 'duration' => max( 0, (float) $request['duration'] ), 'percent' => $percent, 'updated' => time() );
		uasort( $map, function ( $a, $b ) { return ( $b['updated'] ?? 0 ) <=> ( $a['updated'] ?? 0 ); } );
		update_user_meta( $user_id, '_cpwp_progress', array_slice( $map, 0, 100, true ) );
		if ( class_exists( 'CPWP_Learning' ) ) CPWP_Learning::refresh_certificates( $user_id );
	}

	private static function playlist( $user_id, $post_id, $request ) {
		$lists = self::playlists( $user_id );
		$id = sanitize_key( $request['playlist_id'] );
		$name = sanitize_text_field( $request['name'] );
		if ( ! $id && $name ) { $id = 'list-' . wp_generate_uuid4(); $lists[] = array( 'id' => $id, 'name' => $name, 'videos' => array() ); }
		foreach ( $lists as &$list ) if ( $list['id'] === $id ) { if ( in_array( $post_id, $list['videos'], true ) ) $list['videos'] = array_values( array_diff( $list['videos'], array( $post_id ) ) ); else array_unshift( $list['videos'], $post_id ); }
		unset( $list );
		update_user_meta( $user_id, '_cpwp_playlists', $lists );
	}

	private static function ids( $user_id, $key ) { return array_values( array_filter( array_map( 'absint', (array) get_user_meta( $user_id, $key, true ) ) ) ); }
	private static function map( $user_id, $key ) { $value = get_user_meta( $user_id, $key, true ); return is_array( $value ) ? $value : array(); }
	private static function playlists( $user_id ) { $value = get_user_meta( $user_id, '_cpwp_playlists', true ); return is_array( $value ) ? $value : array(); }
	private static function video_data( $ids ) { return array_values( array_filter( array_map( function ( $id ) { return 'cp_video' === get_post_type( $id ) ? array( 'id' => $id, 'title' => get_the_title( $id ), 'url' => get_permalink( $id ), 'thumbnail' => get_the_post_thumbnail_url( $id, 'medium_large' ) ) : null; }, $ids ) ) ); }
	private static function progress_data( $map ) { $result = array(); foreach ( $map as $id => $progress ) { $video = self::video_data( array( $id ) ); if ( $video ) { $video[0]['progress'] = $progress; $result[] = $video[0]; } } return $result; }
}
