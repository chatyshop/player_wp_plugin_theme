<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Moderation {
	const REPORTS = 'cpwp_reports';
	const LOG = 'cpwp_activity_log';

	public static function register_types() {
		register_post_type( 'cpwp_case', array( 'label' => __( 'Trust & Safety Cases', 'cp-wp-plugin' ), 'public' => false, 'show_ui' => false, 'supports' => array( 'title', 'editor', 'author' ) ) );
	}

	public static function register_menu() {
		add_submenu_page( 'edit.php?post_type=cp_video', __( 'Trust & Safety', 'cp-wp-plugin' ), __( 'Trust & Safety', 'cp-wp-plugin' ), 'moderate_comments', 'cpwp-moderation', array( __CLASS__, 'render_admin' ) );
	}

	public static function register_routes() {
		register_rest_route( 'cpwp/v1', '/report', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'report' ), 'permission_callback' => 'is_user_logged_in' ) );
	}

	public static function report( WP_REST_Request $request ) {
		$type = sanitize_key( $request['type'] ); $target = absint( $request['target_id'] ); $reason = sanitize_textarea_field( $request['reason'] );
		$details = sanitize_textarea_field( $request['details'] ); $evidence = esc_url_raw( $request['evidence_url'] );
		if ( ! in_array( $type, array( 'content', 'copyright', 'appeal' ), true ) || ! $reason ) return new WP_Error( 'invalid_report', 'Report details are required.', array( 'status' => 400 ) );
		$id = wp_insert_post( array( 'post_type' => 'cpwp_case', 'post_status' => 'publish', 'post_title' => ucfirst( $type ) . ' case for #' . $target, 'post_content' => $reason, 'post_author' => get_current_user_id() ) );
		update_post_meta( $id, '_cpwp_case_type', $type ); update_post_meta( $id, '_cpwp_target_id', $target ); update_post_meta( $id, '_cpwp_case_status', 'open' );
		update_post_meta( $id, '_cpwp_case_details', $details ); update_post_meta( $id, '_cpwp_case_evidence', $evidence );
		self::log( 'case_created', get_current_user_id(), $target, $type );
		return rest_ensure_response( array( 'success' => true, 'message' => __( 'Your case was submitted for review.', 'cp-wp-plugin' ) ) );
	}

	public static function user_cases( $user_id = 0 ) {
		return get_posts( array( 'post_type' => 'cpwp_case', 'post_status' => 'publish', 'posts_per_page' => 100, 'author' => $user_id ?: get_current_user_id() ) );
	}

	public static function log( $action, $user_id = 0, $target_id = 0, $note = '' ) {
		$log = get_option( self::LOG, array() );
		array_unshift( $log, array( 'time' => time(), 'action' => sanitize_key( $action ), 'user' => absint( $user_id ), 'target' => absint( $target_id ), 'note' => sanitize_text_field( $note ) ) );
		update_option( self::LOG, array_slice( $log, 0, 1000 ), false );
	}

	public static function render_admin() {
		if ( isset( $_POST['cpwp_case_id'], $_POST['cpwp_case_action'] ) && check_admin_referer( 'cpwp_manage_case' ) ) self::handle_case();
		$cases = get_posts( array( 'post_type' => 'cpwp_case', 'posts_per_page' => 100, 'post_status' => 'publish' ) );
		echo '<div class="wrap"><h1>Trust &amp; Safety</h1><table class="widefat striped"><thead><tr><th>Case</th><th>Reporter</th><th>Target</th><th>Status</th><th>Action</th></tr></thead><tbody>';
		foreach ( $cases as $case ) {
			$target = absint( get_post_meta( $case->ID, '_cpwp_target_id', true ) ); $status = get_post_meta( $case->ID, '_cpwp_case_status', true );
			echo '<tr><td><strong>' . esc_html( $case->post_title ) . '</strong><br>' . esc_html( $case->post_content ) . '</td><td>' . esc_html( get_the_author_meta( 'display_name', $case->post_author ) ) . '</td><td><a href="' . esc_url( get_edit_post_link( $target ) ) . '">#' . esc_html( $target ) . '</a></td><td>' . esc_html( $status ) . '</td><td><form method="post">'; wp_nonce_field( 'cpwp_manage_case' );
			echo '<input type="hidden" name="cpwp_case_id" value="' . esc_attr( $case->ID ) . '"><select name="cpwp_case_action"><option value="resolved">Resolve</option><option value="dismissed">Dismiss</option><option value="strike">Issue strike</option><option value="remove">Remove content</option></select> <button class="button">Apply</button></form></td></tr>';
		}
		if ( ! $cases ) echo '<tr><td colspan="5">No cases found.</td></tr>';
		echo '</tbody></table></div>';
	}

	private static function handle_case() {
		$case_id = absint( $_POST['cpwp_case_id'] ); $action = sanitize_key( wp_unslash( $_POST['cpwp_case_action'] ) ); $target = absint( get_post_meta( $case_id, '_cpwp_target_id', true ) );
		if ( 'remove' === $action && $target ) wp_update_post( array( 'ID' => $target, 'post_status' => 'draft' ) );
		if ( 'strike' === $action && $target ) {
			$owner = absint( get_post_field( 'post_author', $target ) ); update_user_meta( $owner, '_cpwp_strikes', absint( get_user_meta( $owner, '_cpwp_strikes', true ) ) + 1 );
		}
		update_post_meta( $case_id, '_cpwp_case_status', in_array( $action, array( 'resolved', 'dismissed' ), true ) ? $action : 'resolved' );
		self::log( 'case_' . $action, get_current_user_id(), $target, 'Case #' . $case_id );
	}
}
