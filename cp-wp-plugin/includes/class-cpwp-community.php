<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Community {
	const MEMBERS = '_cpwp_group_members';
	const MODERATORS = '_cpwp_group_moderators';
	const GROUP = '_cpwp_community_group';
	const POLL = '_cpwp_community_poll';
	const IMAGE = '_cpwp_community_image';
	const POLL_VOTES = '_cpwp_community_poll_votes';

	public static function register_routes() {
		if ( ! in_array( CPWP_Settings::get( 'site_type' ), array( 'creator_platform', 'membership', 'gaming', 'business_training', 'video_library' ), true ) ) return;
		register_rest_route( 'cpwp/v1', '/groups/(?P<group_id>\d+)/membership', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'membership_route' ), 'permission_callback' => 'is_user_logged_in' ) );
		register_rest_route( 'cpwp/v1', '/community/(?P<post_id>\d+)/vote', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'vote' ), 'permission_callback' => 'is_user_logged_in' ) );
	}

	public static function register_menu() {
		if ( in_array( CPWP_Settings::get( 'site_type' ), array( 'membership', 'creator_platform', 'gaming' ), true ) ) add_submenu_page( 'edit.php?post_type=cp_video', __( 'Group Moderation', 'cp-wp-plugin' ), __( 'Group Moderation', 'cp-wp-plugin' ), 'read', 'cpwp-group-moderation', array( __CLASS__, 'render_admin' ) );
	}

	public static function add_meta_boxes() {
		if ( ! in_array( CPWP_Settings::get( 'site_type' ), array( 'creator_platform', 'membership', 'gaming', 'business_training', 'video_library' ), true ) ) return;
		add_meta_box( 'cpwp-community-group', __( 'Community Group Access', 'cp-wp-plugin' ), array( __CLASS__, 'render_post_group' ), 'cp_community', 'side', 'default' );
		add_meta_box( 'cpwp-group-moderators', __( 'Group Moderators', 'cp-wp-plugin' ), array( __CLASS__, 'render_moderators' ), 'cp_group', 'normal', 'default' );
	}

	public static function render_post_group( $post ) {
		wp_nonce_field( 'cpwp_community_fields', 'cpwp_community_nonce' ); echo '<p><label><strong>Restrict to group</strong><br><select style="width:100%" name="' . esc_attr( self::GROUP ) . '"><option value="0">Public community post</option>';
		$current = absint( get_post_meta( $post->ID, self::GROUP, true ) ); foreach ( self::groups() as $group ) printf( '<option value="%d" %s>%s</option>', $group->ID, selected( $current, $group->ID, false ), esc_html( get_the_title( $group ) ) ); echo '</select></label></p>';
	}

	public static function render_moderators( $post ) {
		wp_nonce_field( 'cpwp_community_fields', 'cpwp_community_nonce' ); echo '<p><label>Moderator user IDs (comma separated)<br><input style="width:100%" name="cpwp_group_moderators" value="' . esc_attr( implode( ',', self::ids( get_post_meta( $post->ID, self::MODERATORS, true ) ) ) ) . '"></label></p>';
	}

	public static function save( $post_id ) {
		if ( ! in_array( CPWP_Settings::get( 'site_type' ), array( 'creator_platform', 'membership', 'gaming', 'business_training', 'video_library' ), true ) ) return;
		if ( ! isset( $_POST['cpwp_community_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpwp_community_nonce'] ) ), 'cpwp_community_fields' ) || ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( 'cp_community' === get_post_type( $post_id ) ) { $group = absint( $_POST[ self::GROUP ] ?? 0 ); $group ? update_post_meta( $post_id, self::GROUP, $group ) : delete_post_meta( $post_id, self::GROUP ); }
		if ( 'cp_group' === get_post_type( $post_id ) ) update_post_meta( $post_id, self::MODERATORS, self::ids( explode( ',', sanitize_text_field( wp_unslash( $_POST['cpwp_group_moderators'] ?? '' ) ) ) ) );
	}

	public static function membership_route( WP_REST_Request $request ) {
		$group_id = absint( $request['group_id'] ); if ( 'cp_group' !== get_post_type( $group_id ) ) return new WP_Error( 'invalid_group', 'Invalid group.', array( 'status' => 404 ) );
		$members = self::ids( get_post_meta( $group_id, self::MEMBERS, true ) ); $user_id = get_current_user_id();
		if ( in_array( $user_id, $members, true ) ) { $members = array_values( array_diff( $members, array( $user_id ) ) ); $joined = false; } else { $members[] = $user_id; $joined = true; }
		update_post_meta( $group_id, self::MEMBERS, $members ); CPWP_Moderation::log( $joined ? 'group_joined' : 'group_left', $user_id, $group_id );
		return rest_ensure_response( array( 'joined' => $joined, 'members' => count( $members ) ) );
	}

	public static function vote( WP_REST_Request $request ) {
		$post_id = absint( $request['post_id'] ); $choice = absint( $request['choice'] ); $poll = (array) get_post_meta( $post_id, self::POLL, true ); if ( 'cp_community' !== get_post_type( $post_id ) || ! isset( $poll[ $choice ] ) ) return new WP_Error( 'invalid_poll', 'Invalid poll choice.', array( 'status' => 400 ) );
		$votes = (array) get_post_meta( $post_id, self::POLL_VOTES, true ); $votes[ get_current_user_id() ] = $choice; update_post_meta( $post_id, self::POLL_VOTES, $votes ); return rest_ensure_response( array( 'votes' => count( $votes ) ) );
	}

	public static function is_member( $group_id, $user_id = 0 ) { return in_array( $user_id ?: get_current_user_id(), self::ids( get_post_meta( $group_id, self::MEMBERS, true ) ), true ); }
	public static function can_moderate( $group_id, $user_id = 0 ) { $user_id = $user_id ?: get_current_user_id(); return user_can( $user_id, 'moderate_comments' ) || in_array( $user_id, self::ids( get_post_meta( $group_id, self::MODERATORS, true ) ), true ); }
	public static function groups() { return get_posts( array( 'post_type' => 'cp_group', 'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' ) ); }
	public static function visible_posts() { return array_values( array_filter( get_posts( array( 'post_type' => 'cp_community', 'post_status' => 'publish', 'posts_per_page' => 100 ) ), function ( $post ) { $group = absint( get_post_meta( $post->ID, self::GROUP, true ) ); return ! $group || self::is_member( $group ) || self::can_moderate( $group ); } ) ); }

	public static function protect_post() {
		if ( ! is_singular( 'cp_community' ) ) return; $group = absint( get_post_meta( get_queried_object_id(), self::GROUP, true ) );
		if ( $group && ! self::is_member( $group ) && ! self::can_moderate( $group ) ) wp_die( esc_html__( 'Join this group to view the post.', 'cp-wp-plugin' ), '', array( 'response' => 403 ) );
	}

	public static function publish_from_request() {
		if ( empty( $_POST['cpwp_publish_community'] ) || ! isset( $_POST['cpwp_community_publish_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpwp_community_publish_nonce'] ) ), 'cpwp_publish_community' ) ) return null;
		$group = absint( $_POST['community_group'] ?? 0 ); if ( $group && ! self::is_member( $group ) && ! self::can_moderate( $group ) ) return array( __( 'Join the group before posting there.', 'cp-wp-plugin' ), '' );
		$title = sanitize_text_field( wp_unslash( $_POST['community_title'] ?? '' ) ); $content = wp_kses_post( wp_unslash( $_POST['community_content'] ?? '' ) ); if ( ! $title || ! $content ) return array( __( 'Post title and content are required.', 'cp-wp-plugin' ), '' );
		$id = wp_insert_post( array( 'post_type' => 'cp_community', 'post_status' => 'publish', 'post_title' => $title, 'post_content' => $content, 'post_author' => get_current_user_id() ), true ); if ( is_wp_error( $id ) ) return array( $id->get_error_message(), '' );
		if ( $group ) { update_post_meta( $id, self::GROUP, $group ); update_post_meta( $id, '_cpwp_parent_item', $group ); }
		$image = esc_url_raw( wp_unslash( $_POST['community_image'] ?? '' ) ); if ( $image ) update_post_meta( $id, self::IMAGE, $image );
		$poll = array_values( array_filter( array_map( 'sanitize_text_field', explode( '|', wp_unslash( $_POST['community_poll'] ?? '' ) ) ) ) ); if ( count( $poll ) >= 2 ) update_post_meta( $id, self::POLL, $poll );
		CPWP_Moderation::log( 'community_post_created', get_current_user_id(), $id ); return array( '', __( 'Your community post was published.', 'cp-wp-plugin' ) );
	}

	public static function render_profile_form() {
		if ( ! in_array( CPWP_Settings::get( 'site_type' ), array( 'membership', 'creator_platform', 'gaming' ), true ) ) return;
		echo '<section class="cpwp-channel-panel"><h2>Publish community post</h2><form method="post" class="cp-auth-form">'; wp_nonce_field( 'cpwp_profile', 'cpwp_auth_nonce' ); wp_nonce_field( 'cpwp_publish_community', 'cpwp_community_publish_nonce' );
		echo '<label><span>Title</span><input name="community_title" required></label><label><span>Group</span><select name="community_group"><option value="0">Public community</option>'; foreach ( self::groups() as $group ) if ( self::is_member( $group ) || self::can_moderate( $group ) ) echo '<option value="' . esc_attr( $group->ID ) . '">' . esc_html( get_the_title( $group ) ) . '</option>'; echo '</select></label><label><span>Post</span><textarea name="community_content" rows="6" required></textarea></label><label><span>Image URL</span><input type="url" name="community_image"></label><label><span>Poll choices separated by |</span><input name="community_poll"></label><button class="cp-button" name="cpwp_publish_community" value="1">Publish post</button></form></section>';
	}

	public static function render_admin() {
		if ( isset( $_POST['cpwp_group_action'], $_POST['group_id'] ) && check_admin_referer( 'cpwp_group_moderation' ) ) {
			$group = absint( $_POST['group_id'] ); if ( self::can_moderate( $group ) ) { $action = sanitize_key( wp_unslash( $_POST['cpwp_group_action'] ) ); $target = absint( $_POST['target_id'] ?? 0 ); if ( 'remove_post' === $action && $target && absint( get_post_meta( $target, self::GROUP, true ) ) === $group ) wp_update_post( array( 'ID' => $target, 'post_status' => 'draft' ) ); if ( 'remove_member' === $action ) update_post_meta( $group, self::MEMBERS, array_values( array_diff( self::ids( get_post_meta( $group, self::MEMBERS, true ) ), array( $target ) ) ) ); CPWP_Moderation::log( 'group_' . $action, get_current_user_id(), $target, 'Group #' . $group ); }
		}
		echo '<div class="wrap"><h1>Group Moderation</h1>'; foreach ( self::groups() as $group ) if ( self::can_moderate( $group->ID ) ) { echo '<h2>' . esc_html( get_the_title( $group ) ) . '</h2><h3>Members</h3><ul>'; foreach ( self::ids( get_post_meta( $group->ID, self::MEMBERS, true ) ) as $user_id ) { $user = get_userdata( $user_id ); if ( ! $user ) continue; echo '<li>' . esc_html( $user->display_name ) . ' <form method="post" style="display:inline">'; wp_nonce_field( 'cpwp_group_moderation' ); echo '<input type="hidden" name="group_id" value="' . esc_attr( $group->ID ) . '"><input type="hidden" name="target_id" value="' . esc_attr( $user_id ) . '"><button class="button-link-delete" name="cpwp_group_action" value="remove_member">Remove</button></form></li>'; } echo '</ul><h3>Posts</h3><table class="widefat striped"><tbody>'; foreach ( CPWP_Site_Modules::children( $group->ID, 'cp_community' ) as $post ) { echo '<tr><td>' . esc_html( get_the_title( $post ) ) . '</td><td><form method="post">'; wp_nonce_field( 'cpwp_group_moderation' ); echo '<input type="hidden" name="group_id" value="' . esc_attr( $group->ID ) . '"><input type="hidden" name="target_id" value="' . esc_attr( $post->ID ) . '"><button class="button" name="cpwp_group_action" value="remove_post">Remove post</button></form></td></tr>'; } echo '</tbody></table>'; } echo '</div>';
	}

	private static function ids( $value ) { return array_values( array_unique( array_filter( array_map( 'absint', (array) $value ) ) ) ); }
}
