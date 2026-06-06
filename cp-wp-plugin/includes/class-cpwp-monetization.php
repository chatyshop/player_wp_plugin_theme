<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Monetization {
	const STATUS_META = '_cpwp_creator_monetization_status';
	const APPLICATION_META = '_cpwp_creator_monetization_application';
	const ADS_META = '_cpwp_creator_ads';
	public static function slots() {
		return array(
			'home_hero' => 'Homepage Hero Ad',
			'home_grid' => 'Homepage Grid Ad',
			'home_sidebar' => 'Homepage Sidebar Ad',
			'video_above' => 'Above Player Ad',
			'video_below' => 'Below Player Ad',
			'video_description' => 'Description Ad',
			'player_overlay' => 'Player Overlay Ad',
		);
	}

	public static function render( $slot, $post_id = 0 ) {
		if ( ! CPWP_Settings::get( 'enable_monetization' ) || ! array_key_exists( $slot, self::slots() ) ) return '';
		$code = $post_id ? get_post_meta( $post_id, '_cpwp_ad_' . $slot, true ) : '';
		if ( ! $code && $post_id && CPWP_Settings::get( 'enable_creator_monetization' ) && array_key_exists( $slot, self::creator_slots() ) ) {
			$owner = absint( get_post_meta( $post_id, '_cpwp_channel_owner', true ) );
			$ads = $owner && 'approved' === self::creator_status( $owner ) ? get_user_meta( $owner, self::ADS_META, true ) : array();
			$code = is_array( $ads ) ? ( $ads[ $slot ] ?? '' ) : '';
		}
		if ( ! $code ) $code = CPWP_Settings::get( 'ad_' . $slot );
		if ( ! $code ) return '';
		return '<aside class="cpwp-ad cpwp-ad-' . esc_attr( $slot ) . '" aria-label="' . esc_attr__( 'Advertisement', 'cp-wp-plugin' ) . '">' . $code . '</aside>';
	}

	public static function player_url( $type, $post_id ) {
		$url = get_post_meta( $post_id, '_cpwp_ad_' . $type . '_url', true );
		return $url ?: CPWP_Settings::get( 'ad_' . $type . '_url' );
	}

	public static function render_overlay() {
		if ( is_singular( 'cp_video' ) ) echo self::render( 'player_overlay', get_queried_object_id() );
	}

	public static function sanitize_code( $code ) {
		return current_user_can( 'unfiltered_html' ) ? wp_unslash( $code ) : wp_kses_post( wp_unslash( $code ) );
	}

	public static function creator_slots() { return array( 'video_below' => __( 'Below Player Ad', 'cp-wp-plugin' ), 'video_description' => __( 'Description Ad', 'cp-wp-plugin' ) ); }
	public static function creator_status( $user_id = 0 ) { return sanitize_key( get_user_meta( $user_id ?: get_current_user_id(), self::STATUS_META, true ) ); }

	public static function apply_creator() {
		if ( ! CPWP_Settings::get( 'enable_creator_monetization' ) || ! CPWP_Channels::get() ) return array( __( 'Creator monetization applications are not available.', 'cp-wp-plugin' ), '' );
		update_user_meta( get_current_user_id(), self::STATUS_META, 'pending' );
		update_user_meta( get_current_user_id(), self::APPLICATION_META, sanitize_textarea_field( wp_unslash( $_POST['creator_monetization_application'] ?? '' ) ) );
		return array( '', __( 'Your monetization application was submitted for admin review.', 'cp-wp-plugin' ) );
	}

	public static function save_creator_ads() {
		if ( 'approved' !== self::creator_status() ) return array( __( 'Your channel must be approved before you can save ads.', 'cp-wp-plugin' ), '' );
		$ads = array();
		foreach ( self::creator_slots() as $slot => $label ) $ads[ $slot ] = wp_kses_post( wp_unslash( $_POST[ 'creator_ad_' . $slot ] ?? '' ) );
		update_user_meta( get_current_user_id(), self::ADS_META, $ads );
		return array( '', __( 'Your creator ads were saved.', 'cp-wp-plugin' ) );
	}

	public static function render_creator_form() {
		if ( ! CPWP_Settings::get( 'enable_creator_monetization' ) || ! CPWP_Settings::get( 'enable_creator_channels' ) || ! CPWP_Channels::get() ) return;
		$status = self::creator_status();
		echo '<section class="cpwp-channel-panel"><h2>' . esc_html__( 'Creator Monetization', 'cp-wp-plugin' ) . '</h2>';
		if ( 'approved' === $status ) {
			$ads = get_user_meta( get_current_user_id(), self::ADS_META, true ); $ads = is_array( $ads ) ? $ads : array();
			echo '<p>' . esc_html__( 'Approved. You may show sanitized HTML ads below the player and in the description area of your channel videos.', 'cp-wp-plugin' ) . '</p><form method="post" class="cp-auth-form">';
			wp_nonce_field( 'cpwp_profile', 'cpwp_auth_nonce' ); wp_nonce_field( 'cpwp_creator_ads', 'cpwp_creator_ads_nonce' );
			foreach ( self::creator_slots() as $slot => $label ) printf( '<label><span>%s</span><textarea name="creator_ad_%s" rows="5">%s</textarea></label>', esc_html( $label ), esc_attr( $slot ), esc_textarea( $ads[ $slot ] ?? '' ) );
			echo '<button class="cp-button" name="cpwp_save_creator_ads" value="1" type="submit">' . esc_html__( 'Save creator ads', 'cp-wp-plugin' ) . '</button></form>';
		} elseif ( 'pending' === $status ) echo '<p>' . esc_html__( 'Your monetization application is pending admin review.', 'cp-wp-plugin' ) . '</p>';
		else {
			if ( 'rejected' === $status ) echo '<p>' . esc_html__( 'Your previous application was rejected. You may apply again.', 'cp-wp-plugin' ) . '</p>';
			echo '<form method="post" class="cp-auth-form">'; wp_nonce_field( 'cpwp_profile', 'cpwp_auth_nonce' ); wp_nonce_field( 'cpwp_creator_monetization', 'cpwp_creator_monetization_nonce' );
			echo '<label><span>' . esc_html__( 'Tell the admin about your channel and content', 'cp-wp-plugin' ) . '</span><textarea name="creator_monetization_application" rows="5" required></textarea></label><button class="cp-button" name="cpwp_apply_creator_monetization" value="1" type="submit">' . esc_html__( 'Apply for monetization', 'cp-wp-plugin' ) . '</button></form>';
		}
		echo '</section>';
	}

	public static function register_menu() { add_submenu_page( 'edit.php?post_type=cp_video', __( 'Creator Monetization', 'cp-wp-plugin' ), __( 'Creator Monetization', 'cp-wp-plugin' ), 'manage_options', 'cpwp-creator-monetization', array( __CLASS__, 'render_admin' ) ); }

	public static function render_admin() {
		if ( isset( $_POST['cpwp_monetization_user'], $_POST['cpwp_monetization_status'] ) && check_admin_referer( 'cpwp_manage_creator_monetization' ) ) {
			$status = sanitize_key( wp_unslash( $_POST['cpwp_monetization_status'] ) );
			if ( in_array( $status, array( 'approved', 'rejected', 'pending' ), true ) ) update_user_meta( absint( $_POST['cpwp_monetization_user'] ), self::STATUS_META, $status );
		}
		$users = get_users( array( 'meta_key' => CPWP_Channels::META ) );
		echo '<div class="wrap"><h1>' . esc_html__( 'Creator Monetization', 'cp-wp-plugin' ) . '</h1><p>' . esc_html__( 'Approved creators can place sanitized HTML ads below the player and in the description area only.', 'cp-wp-plugin' ) . '</p><table class="widefat striped"><thead><tr><th>Creator</th><th>Application</th><th>Status</th><th>Action</th></tr></thead><tbody>';
		foreach ( $users as $user ) {
			$status = self::creator_status( $user->ID ) ?: 'not applied';
			echo '<tr><td>' . esc_html( $user->display_name ) . '<br><small>' . esc_html( $user->user_email ) . '</small></td><td>' . esc_html( get_user_meta( $user->ID, self::APPLICATION_META, true ) ) . '</td><td>' . esc_html( ucfirst( $status ) ) . '</td><td><form method="post">'; wp_nonce_field( 'cpwp_manage_creator_monetization' );
			echo '<input type="hidden" name="cpwp_monetization_user" value="' . esc_attr( $user->ID ) . '"><select name="cpwp_monetization_status"><option value="approved">Approve</option><option value="rejected">Reject</option><option value="pending">Set pending</option></select> <button class="button">Update</button></form></td></tr>';
		}
		if ( ! $users ) echo '<tr><td colspan="4">' . esc_html__( 'No creator channels found.', 'cp-wp-plugin' ) . '</td></tr>';
		echo '</tbody></table></div>';
	}
}
