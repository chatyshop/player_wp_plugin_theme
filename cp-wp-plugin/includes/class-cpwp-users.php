<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Users {
	const VERIFIED_META = '_cpwp_email_verified';
	const VERIFY_META = '_cpwp_verify_token';

	public static function register_menu() {
		add_submenu_page( 'edit.php?post_type=cp_video', __( 'CP Users', 'cp-wp-plugin' ), __( 'Users', 'cp-wp-plugin' ), 'list_users', 'cpwp-users', array( __CLASS__, 'render_admin' ) );
	}

	public static function auth_url( $action, $args = array() ) {
		return add_query_arg( array_merge( array( 'cpwp_auth' => $action ), $args ), home_url( '/' ) );
	}

	public static function login_url( $redirect = '' ) { return self::auth_url( 'login', array_filter( array( 'redirect_to' => $redirect ) ) ); }
	public static function register_url() { return self::auth_url( 'register' ); }
	public static function profile_url() { return self::auth_url( 'profile' ); }

	public static function handle_authentication() {
		$action = sanitize_key( wp_unslash( $_GET['cpwp_auth'] ?? '' ) );
		$actions = array( 'login', 'register', 'profile', 'forgot', 'reset', 'verify', 'resend-verification', 'delete-account' );
		if ( ! in_array( $action, $actions, true ) ) return;

		self::guard_action( $action );
		$error = '';
		$success = '';

		if ( 'verify' === $action ) {
			list( $error, $success ) = self::verify_email();
		} elseif ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'cpwp_' . $action, 'cpwp_auth_nonce' );
			if ( in_array( $action, array( 'login', 'register', 'forgot' ), true ) && ! self::captcha_valid() ) {
				$error = __( 'CAPTCHA answer is incorrect.', 'cp-wp-plugin' );
			} else {
				list( $error, $success ) = self::process_action( $action );
			}
		}

		status_header( 200 );
		nocache_headers();
		get_header();
		self::render_action( $action, $error, $success );
		get_footer();
		exit;
	}

	private static function guard_action( $action ) {
		if ( 'login' === $action && ! CPWP_Settings::get( 'enable_login' ) ) self::auth_disabled();
		if ( 'register' === $action && ! CPWP_Settings::get( 'enable_registration' ) ) self::auth_disabled();
		if ( in_array( $action, array( 'forgot', 'reset' ), true ) && ! CPWP_Settings::get( 'enable_password_recovery' ) ) self::auth_disabled();
		if ( in_array( $action, array( 'verify', 'resend-verification' ), true ) && ! CPWP_Settings::get( 'enable_email_verification' ) ) self::auth_disabled();
		if ( 'delete-account' === $action && ! CPWP_Settings::get( 'enable_account_deletion' ) ) self::auth_disabled();
		if ( in_array( $action, array( 'profile', 'delete-account' ), true ) && ! is_user_logged_in() ) {
			wp_safe_redirect( self::login_url( self::auth_url( $action ) ) );
			exit;
		}
		if ( in_array( $action, array( 'login', 'register', 'forgot', 'reset' ), true ) && is_user_logged_in() ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
	}

	private static function process_action( $action ) {
		if ( 'login' === $action ) return self::process_login();
		if ( 'register' === $action ) return self::process_register();
		if ( 'forgot' === $action ) return self::process_forgot();
		if ( 'reset' === $action ) return self::process_reset();
		if ( 'profile' === $action ) return self::process_profile();
		if ( 'resend-verification' === $action ) return self::resend_verification();
		if ( 'delete-account' === $action ) return self::process_delete();
		return array( '', '' );
	}

	private static function process_login() {
		$login = sanitize_text_field( wp_unslash( $_POST['user_login'] ?? '' ) );
		$rate_key = 'cpwp_login_' . md5( strtolower( $login ) . '|' . self::client_ip() );
		$attempts = absint( get_transient( $rate_key ) );
		if ( CPWP_Settings::get( 'enable_login_rate_limit' ) && $attempts >= 5 ) return array( __( 'Too many login attempts. Try again in 15 minutes.', 'cp-wp-plugin' ), '' );
		$user = wp_signon( array( 'user_login' => $login, 'user_password' => (string) wp_unslash( $_POST['user_password'] ?? '' ), 'remember' => ! empty( $_POST['remember'] ) ), is_ssl() );
		if ( is_wp_error( $user ) ) {
			if ( CPWP_Settings::get( 'enable_login_rate_limit' ) ) set_transient( $rate_key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
			return array( $user->get_error_message(), '' );
		}
		if ( get_user_meta( $user->ID, '_cpwp_suspended', true ) ) { wp_logout(); return array( __( 'This account is suspended.', 'cp-wp-plugin' ), '' ); }
		if ( CPWP_Settings::get( 'enable_email_verification' ) && get_user_meta( $user->ID, self::VERIFY_META, true ) && ! get_user_meta( $user->ID, self::VERIFIED_META, true ) && ! user_can( $user, 'edit_posts' ) ) {
			wp_logout();
			return array( __( 'Verify your email address before logging in.', 'cp-wp-plugin' ), '' );
		}
		delete_transient( $rate_key );
		$redirect_url = wp_validate_redirect( wp_unslash( $_POST['redirect_to'] ?? '' ), home_url( '/' ) );
		if ( ! wp_redirect( $redirect_url ) ) {
			echo '<script>window.location.replace("' . esc_url_raw( $redirect_url ) . '");</script>';
		}
		exit;
	}

	private static function process_register() {
		$password = (string) wp_unslash( $_POST['user_password'] ?? '' );
		if ( strlen( $password ) < 8 ) return array( __( 'Password must contain at least 8 characters.', 'cp-wp-plugin' ), '' );
		$user_id = wp_create_user( sanitize_user( wp_unslash( $_POST['user_login'] ?? '' ) ), $password, sanitize_email( wp_unslash( $_POST['user_email'] ?? '' ) ) );
		if ( is_wp_error( $user_id ) ) return array( $user_id->get_error_message(), '' );
		if ( CPWP_Settings::get( 'enable_email_verification' ) ) {
			self::send_verification( $user_id );
			return array( '', __( 'Account created. Check your email to verify your account before logging in.', 'cp-wp-plugin' ) );
		}
		update_user_meta( $user_id, self::VERIFIED_META, 1 );
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true, is_ssl() );
		$redirect_url = home_url( '/' );
		if ( ! wp_redirect( $redirect_url ) ) {
			echo '<script>window.location.replace("' . esc_url_raw( $redirect_url ) . '");</script>';
		}
		exit;
	}

	private static function process_forgot() {
		$login = sanitize_text_field( wp_unslash( $_POST['user_login'] ?? '' ) );
		$user = get_user_by( 'login', $login );
		if ( ! $user && is_email( $login ) ) $user = get_user_by( 'email', $login );
		if ( $user ) {
			$key = get_password_reset_key( $user );
			if ( ! is_wp_error( $key ) ) {
				$url = self::auth_url( 'reset', array( 'login' => rawurlencode( $user->user_login ), 'key' => $key ) );
				wp_mail( $user->user_email, sprintf( __( 'Reset your %s password', 'cp-wp-plugin' ), get_bloginfo( 'name' ) ), sprintf( __( "Use this link to reset your password:\n\n%s", 'cp-wp-plugin' ), $url ) );
			}
		}
		return array( '', __( 'If that account exists, a password-reset email has been sent.', 'cp-wp-plugin' ) );
	}

	private static function process_reset() {
		$user = check_password_reset_key( sanitize_text_field( wp_unslash( $_POST['reset_key'] ?? '' ) ), sanitize_user( wp_unslash( $_POST['reset_login'] ?? '' ) ) );
		$password = (string) wp_unslash( $_POST['new_password'] ?? '' );
		if ( is_wp_error( $user ) ) return array( __( 'This password-reset link is invalid or expired.', 'cp-wp-plugin' ), '' );
		if ( strlen( $password ) < 8 ) return array( __( 'Password must contain at least 8 characters.', 'cp-wp-plugin' ), '' );
		reset_password( $user, $password );
		return array( '', __( 'Password reset successfully. You can now log in.', 'cp-wp-plugin' ) );
	}

	private static function process_profile() {
		$creator = CPWP_Creator_Platform::process_profile(); if ( is_array( $creator ) ) return $creator;
		$community = CPWP_Community::publish_from_request(); if ( is_array( $community ) ) return $community;
		if ( ! empty( $_POST['cpwp_apply_creator_monetization'] ) && isset( $_POST['cpwp_creator_monetization_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpwp_creator_monetization_nonce'] ) ), 'cpwp_creator_monetization' ) ) return CPWP_Monetization::apply_creator();
		if ( ! empty( $_POST['cpwp_save_creator_ads'] ) && isset( $_POST['cpwp_creator_ads_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpwp_creator_ads_nonce'] ) ), 'cpwp_creator_ads' ) ) return CPWP_Monetization::save_creator_ads();
		if ( ! empty( $_POST['cpwp_save_channel'] ) && isset( $_POST['cpwp_channel_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpwp_channel_nonce'] ) ), 'cpwp_channel' ) ) return CPWP_Channels::save_from_request();
		if ( ! empty( $_POST['cpwp_publish_channel_video'] ) && isset( $_POST['cpwp_channel_video_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpwp_channel_video_nonce'] ) ), 'cpwp_channel_video' ) ) return self::publish_channel_video();
		$user = wp_get_current_user();
		$new_password = (string) wp_unslash( $_POST['new_password'] ?? '' );
		if ( $new_password && CPWP_Settings::get( 'enable_password_confirmation' ) && ! wp_check_password( (string) wp_unslash( $_POST['current_password'] ?? '' ), $user->user_pass, $user->ID ) ) return array( __( 'Enter your current password to set a new password.', 'cp-wp-plugin' ), '' );
		if ( $new_password && strlen( $new_password ) < 8 ) return array( __( 'New password must contain at least 8 characters.', 'cp-wp-plugin' ), '' );
		$result = wp_update_user( array( 'ID' => $user->ID, 'display_name' => sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) ), 'first_name' => sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) ), 'last_name' => sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) ), 'user_email' => sanitize_email( wp_unslash( $_POST['user_email'] ?? '' ) ) ) );
		if ( is_wp_error( $result ) ) return array( $result->get_error_message(), '' );
		if ( $new_password ) {
			wp_set_password( $new_password, $user->ID );
			wp_set_auth_cookie( $user->ID, true, is_ssl() );
		}
		return array( '', __( 'Your profile has been updated.', 'cp-wp-plugin' ) );
	}

	private static function process_delete() {
		$user = wp_get_current_user();
		if ( user_can( $user, 'edit_posts' ) ) return array( __( 'Administrator and editor accounts cannot be deleted here.', 'cp-wp-plugin' ), '' );
		if ( ! wp_check_password( (string) wp_unslash( $_POST['current_password'] ?? '' ), $user->user_pass, $user->ID ) || 'DELETE' !== strtoupper( sanitize_text_field( wp_unslash( $_POST['confirm_delete'] ?? '' ) ) ) ) return array( __( 'Enter your current password and type DELETE to confirm.', 'cp-wp-plugin' ), '' );
		require_once ABSPATH . 'wp-admin/includes/user.php';
		wp_logout();
		wp_delete_user( $user->ID );
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	private static function send_verification( $user_id ) {
		$user = get_userdata( $user_id );
		$token = wp_generate_password( 40, false, false );
		update_user_meta( $user_id, self::VERIFY_META, array( 'hash' => wp_hash_password( $token ), 'expires' => time() + DAY_IN_SECONDS ) );
		$url = self::auth_url( 'verify', array( 'uid' => $user_id, 'token' => $token ) );
		wp_mail( $user->user_email, sprintf( __( 'Verify your %s account', 'cp-wp-plugin' ), get_bloginfo( 'name' ) ), sprintf( __( "Verify your account using this link:\n\n%s", 'cp-wp-plugin' ), $url ) );
	}

	private static function verify_email() {
		$user_id = absint( $_GET['uid'] ?? 0 );
		$data = get_user_meta( $user_id, self::VERIFY_META, true );
		$token = sanitize_text_field( wp_unslash( $_GET['token'] ?? '' ) );
		if ( ! is_array( $data ) || time() > absint( $data['expires'] ?? 0 ) || ! wp_check_password( $token, $data['hash'] ?? '' ) ) return array( __( 'This verification link is invalid or expired.', 'cp-wp-plugin' ), '' );
		update_user_meta( $user_id, self::VERIFIED_META, 1 );
		delete_user_meta( $user_id, self::VERIFY_META );
		return array( '', __( 'Email verified successfully. You can now log in.', 'cp-wp-plugin' ) );
	}

	private static function resend_verification() {
		$login = sanitize_text_field( wp_unslash( $_POST['user_login'] ?? '' ) );
		$user = is_user_logged_in() ? wp_get_current_user() : get_user_by( 'login', $login );
		if ( ! $user && is_email( $login ) ) $user = get_user_by( 'email', $login );
		if ( $user && get_user_meta( $user->ID, self::VERIFY_META, true ) && ! get_user_meta( $user->ID, self::VERIFIED_META, true ) ) self::send_verification( $user->ID );
		return array( '', __( 'If that account needs verification, a new email has been sent.', 'cp-wp-plugin' ) );
	}

	private static function captcha_valid() {
		if ( ! CPWP_Settings::get( 'enable_auth_captcha' ) ) return true;
		$answer = absint( $_POST['captcha_answer'] ?? -1 );
		$token = sanitize_text_field( wp_unslash( $_POST['captcha_token'] ?? '' ) );
		return hash_equals( wp_hash( 'cpwp-captcha-' . $answer ), $token );
	}

	private static function captcha_field() {
		if ( ! CPWP_Settings::get( 'enable_auth_captcha' ) ) return;
		$a = wp_rand( 1, 9 ); $b = wp_rand( 1, 9 ); $answer = $a + $b;
		printf( '<label><span>%s</span><input name="captcha_answer" type="number" required><input name="captcha_token" type="hidden" value="%s"></label>', esc_html( sprintf( __( 'Security check: %d + %d = ?', 'cp-wp-plugin' ), $a, $b ) ), esc_attr( wp_hash( 'cpwp-captcha-' . $answer ) ) );
	}

	private static function render_action( $action, $error, $success ) {
		if ( 'profile' === $action ) return self::render_profile_form( $error, $success );
		if ( 'delete-account' === $action ) return self::render_delete_form( $error );
		$title = array( 'login' => __( 'Welcome back', 'cp-wp-plugin' ), 'register' => __( 'Create your account', 'cp-wp-plugin' ), 'forgot' => __( 'Recover your password', 'cp-wp-plugin' ), 'reset' => __( 'Choose a new password', 'cp-wp-plugin' ), 'verify' => __( 'Email verification', 'cp-wp-plugin' ), 'resend-verification' => __( 'Email verification', 'cp-wp-plugin' ) )[ $action ];
		?>
		<div class="cp-shell"><section class="cp-auth-card"><span class="cp-kicker"><?php echo esc_html( CPWP_Settings::get( 'platform_name' ) ?: get_bloginfo( 'name' ) ); ?></span><h1><?php echo esc_html( $title ); ?></h1>
		<?php self::notices( $error, $success ); ?>
		<?php if ( ! in_array( $action, array( 'verify', 'resend-verification' ), true ) || 'resend-verification' === $action ) : ?><form method="post" class="cp-auth-form"><?php wp_nonce_field( 'cpwp_' . $action, 'cpwp_auth_nonce' ); ?>
			<?php if ( in_array( $action, array( 'login', 'register', 'forgot', 'resend-verification' ), true ) && ! ( 'resend-verification' === $action && is_user_logged_in() ) ) : ?><label><span><?php echo esc_html( 'register' === $action ? __( 'Username', 'cp-wp-plugin' ) : __( 'Username or email', 'cp-wp-plugin' ) ); ?></span><input name="user_login" type="text" required></label><?php endif; ?>
			<?php if ( 'register' === $action ) : ?><label><span><?php esc_html_e( 'Email', 'cp-wp-plugin' ); ?></span><input name="user_email" type="email" required></label><?php endif; ?>
			<?php if ( in_array( $action, array( 'login', 'register' ), true ) ) : ?><label><span><?php esc_html_e( 'Password', 'cp-wp-plugin' ); ?></span><input name="user_password" type="password" required></label><?php endif; ?>
			<?php if ( 'login' === $action ) : ?><label class="cp-auth-check"><input name="remember" type="checkbox" value="1"> <span><?php esc_html_e( 'Remember me', 'cp-wp-plugin' ); ?></span></label><input name="redirect_to" type="hidden" value="<?php echo esc_attr( wp_unslash( $_GET['redirect_to'] ?? '' ) ); ?>"><?php endif; ?>
			<?php if ( 'reset' === $action ) : ?><input name="reset_login" type="hidden" value="<?php echo esc_attr( sanitize_user( wp_unslash( $_GET['login'] ?? '' ) ) ); ?>"><input name="reset_key" type="hidden" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['key'] ?? '' ) ) ); ?>"><label><span><?php esc_html_e( 'New password', 'cp-wp-plugin' ); ?></span><input name="new_password" type="password" required></label><?php endif; ?>
			<?php self::captcha_field(); ?><button class="cp-button" type="submit"><?php echo esc_html( 'forgot' === $action ? __( 'Send reset email', 'cp-wp-plugin' ) : ( 'reset' === $action ? __( 'Reset password', 'cp-wp-plugin' ) : ( 'resend-verification' === $action ? __( 'Resend verification email', 'cp-wp-plugin' ) : ucfirst( $action ) ) ) ); ?></button>
		</form><?php endif; ?>
		<?php if ( 'login' === $action && CPWP_Settings::get( 'enable_password_recovery' ) ) : ?><p><a href="<?php echo esc_url( self::auth_url( 'forgot' ) ); ?>"><?php esc_html_e( 'Forgot your password?', 'cp-wp-plugin' ); ?></a></p><?php endif; ?>
		<?php if ( 'login' === $action && CPWP_Settings::get( 'enable_email_verification' ) ) : ?><p><a href="<?php echo esc_url( self::auth_url( 'resend-verification' ) ); ?>"><?php esc_html_e( 'Resend verification email', 'cp-wp-plugin' ); ?></a></p><?php endif; ?>
		<?php if ( 'login' === $action && CPWP_Settings::get( 'enable_registration' ) ) : ?><p><a href="<?php echo esc_url( self::register_url() ); ?>"><?php esc_html_e( 'Create an account', 'cp-wp-plugin' ); ?></a></p><?php endif; ?>
		</section></div>
		<?php
	}

	private static function render_profile_form( $error, $success ) {
		$user = wp_get_current_user();
		?><div class="cp-shell"><section class="cp-auth-card cp-profile-card"><div class="cp-profile-head"><?php echo get_avatar( $user->ID, 80 ); ?><div><span class="cp-kicker"><?php esc_html_e( 'Your account', 'cp-wp-plugin' ); ?></span><h1><?php echo esc_html( $user->display_name ); ?></h1></div></div><?php self::notices( $error, $success ); ?>
		<form method="post" class="cp-auth-form"><?php wp_nonce_field( 'cpwp_profile', 'cpwp_auth_nonce' ); ?><label><span><?php esc_html_e( 'Display name', 'cp-wp-plugin' ); ?></span><input name="display_name" type="text" value="<?php echo esc_attr( $user->display_name ); ?>" required></label><label><span><?php esc_html_e( 'First name', 'cp-wp-plugin' ); ?></span><input name="first_name" type="text" value="<?php echo esc_attr( $user->first_name ); ?>"></label><label><span><?php esc_html_e( 'Last name', 'cp-wp-plugin' ); ?></span><input name="last_name" type="text" value="<?php echo esc_attr( $user->last_name ); ?>"></label><label><span><?php esc_html_e( 'Email', 'cp-wp-plugin' ); ?></span><input name="user_email" type="email" value="<?php echo esc_attr( $user->user_email ); ?>" required></label><?php if ( CPWP_Settings::get( 'enable_password_confirmation' ) ) : ?><label><span><?php esc_html_e( 'Current password', 'cp-wp-plugin' ); ?></span><input name="current_password" type="password"></label><?php endif; ?><label><span><?php esc_html_e( 'New password', 'cp-wp-plugin' ); ?></span><input name="new_password" type="password" placeholder="<?php esc_attr_e( 'Leave blank to keep current password', 'cp-wp-plugin' ); ?>"></label><button class="cp-button" type="submit"><?php esc_html_e( 'Save profile', 'cp-wp-plugin' ); ?></button></form>
		<?php if ( get_user_meta( $user->ID, '_cpwp_suspended', true ) || get_user_meta( $user->ID, '_cpwp_strikes', true ) ) : ?><p><button class="cp-button" data-cpwp-report="appeal" data-target-id="<?php echo esc_attr( $user->ID ); ?>"><?php esc_html_e( 'Submit moderation appeal', 'cp-wp-plugin' ); ?></button></p><?php endif; ?>
		<?php if ( CPWP_Settings::get( 'enable_favorites_watch_later' ) || CPWP_Settings::get( 'enable_playlists' ) || CPWP_Settings::get( 'enable_continue_watching' ) ) : ?><section class="cpwp-library" data-cpwp-library><h2><?php esc_html_e( 'My Library', 'cp-wp-plugin' ); ?></h2><p><?php esc_html_e( 'Loading your videos...', 'cp-wp-plugin' ); ?></p></section><?php endif; ?>
		<?php if ( CPWP_Settings::get( 'enable_email_verification' ) && get_user_meta( $user->ID, self::VERIFY_META, true ) && ! get_user_meta( $user->ID, self::VERIFIED_META, true ) ) : ?><p><a href="<?php echo esc_url( self::auth_url( 'resend-verification' ) ); ?>"><?php esc_html_e( 'Resend verification email', 'cp-wp-plugin' ); ?></a></p><?php endif; ?><?php if ( CPWP_Settings::get( 'enable_account_deletion' ) ) : ?><p><a class="cp-danger-link" href="<?php echo esc_url( self::auth_url( 'delete-account' ) ); ?>"><?php esc_html_e( 'Delete my account', 'cp-wp-plugin' ); ?></a></p><?php endif; ?></section></div><?php
	}

	private static function render_delete_form( $error ) {
		?><div class="cp-shell"><section class="cp-auth-card"><span class="cp-kicker"><?php esc_html_e( 'Danger zone', 'cp-wp-plugin' ); ?></span><h1><?php esc_html_e( 'Delete your account', 'cp-wp-plugin' ); ?></h1><?php self::notices( $error, '' ); ?><p><?php esc_html_e( 'This action is permanent. Your account will be removed.', 'cp-wp-plugin' ); ?></p><form method="post" class="cp-auth-form"><?php wp_nonce_field( 'cpwp_delete-account', 'cpwp_auth_nonce' ); ?><label><span><?php esc_html_e( 'Current password', 'cp-wp-plugin' ); ?></span><input name="current_password" type="password" required></label><label><span><?php esc_html_e( 'Type DELETE to confirm', 'cp-wp-plugin' ); ?></span><input name="confirm_delete" type="text" required></label><button class="cp-button cp-danger-button" type="submit"><?php esc_html_e( 'Permanently delete account', 'cp-wp-plugin' ); ?></button></form></section></div><?php
	}

	private static function notices( $error, $success ) {
		if ( $error ) echo '<div class="cp-auth-error">' . wp_kses_post( $error ) . '</div>';
		if ( $success ) echo '<div class="cp-auth-success">' . esc_html( $success ) . '</div>';
	}

	private static function client_ip() { return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) ); }

	public static function publish_channel_video() {
		if ( ! CPWP_Settings::get( 'enable_creator_channels' ) || ! CPWP_Channels::get() ) return array( __( 'Create your channel before publishing videos.', 'cp-wp-plugin' ), '' );
		$title = sanitize_text_field( wp_unslash( $_POST['channel_video_title'] ?? '' ) );
		$url = esc_url_raw( wp_unslash( $_POST['channel_video_url'] ?? '' ) );
		$channel = CPWP_Channels::get();
		if ( ! $title || ! $url ) return array( __( 'Video title and uploaded video URL are required.', 'cp-wp-plugin' ), '' );
		if ( 0 !== strpos( $url, trailingslashit( $channel['storage_public_url'] ) ) ) return array( __( 'Video URL must come from your connected channel storage bucket.', 'cp-wp-plugin' ), '' );
		
		$status = sanitize_key( $_POST['post_status'] ?? 'publish' );
		if ( ! in_array( $status, array( 'publish', 'draft' ), true ) ) $status = 'publish';
		
		$post_id = wp_insert_post( array(
			'post_type' => 'cp_video',
			'post_status' => $status,
			'post_title' => $title,
			'post_content' => wp_kses_post( wp_unslash( $_POST['channel_video_description'] ?? '' ) ),
			'post_author' => get_current_user_id(),
			'comment_status' => isset( $_POST['allow_comments'] ) && ! empty( $_POST['allow_comments'] ) ? 'open' : 'closed'
		), true );
		if ( is_wp_error( $post_id ) ) return array( $post_id->get_error_message(), '' );
		
		update_post_meta( $post_id, '_cpwp_mp4', $url );
		update_post_meta( $post_id, '_cpwp_channel_owner', get_current_user_id() );
		
		if ( isset( $_POST['accent_color'] ) ) update_post_meta( $post_id, '_cpwp_accent_color', sanitize_hex_color( $_POST['accent_color'] ) );
		if ( isset( $_POST['autoplay'] ) ) update_post_meta( $post_id, '_cpwp_autoplay', ! empty( $_POST['autoplay'] ) );
		if ( isset( $_POST['loop'] ) ) update_post_meta( $post_id, '_cpwp_loop', ! empty( $_POST['loop'] ) );
		if ( isset( $_POST['muted'] ) ) update_post_meta( $post_id, '_cpwp_muted', ! empty( $_POST['muted'] ) );
		if ( isset( $_POST['preload'] ) ) update_post_meta( $post_id, '_cpwp_preload', sanitize_key( $_POST['preload'] ) );
		if ( isset( $_POST['poster_url'] ) ) update_post_meta( $post_id, '_cpwp_poster_url', esc_url_raw( $_POST['poster_url'] ) );
		if ( isset( $_POST['chapters'] ) ) {
			$chapters = json_decode( wp_unslash( $_POST['chapters'] ), true );
			update_post_meta( $post_id, '_cpwp_chapters', is_array( $chapters ) ? $chapters : array() );
		}
		if ( isset( $_POST['subtitles'] ) ) {
			$subtitles = json_decode( wp_unslash( $_POST['subtitles'] ), true );
			update_post_meta( $post_id, '_cpwp_subtitles', is_array( $subtitles ) ? $subtitles : array() );
		}

		// Save taxonomies based on site type
		$site_type = CPWP_Settings::get( 'site_type' );
		if ( 'creator_platform' === $site_type ) {
			if ( ! empty( $_POST['video_genre'] ) ) {
				wp_set_post_terms( $post_id, array( absint( $_POST['video_genre'] ) ), 'cp_genre' );
			}
			if ( ! empty( $_POST['video_topic'] ) ) {
				wp_set_post_terms( $post_id, array( absint( $_POST['video_topic'] ) ), 'cp_topic' );
			}
			if ( ! empty( $_POST['video_tags'] ) ) {
				wp_set_post_terms( $post_id, sanitize_text_field( wp_unslash( $_POST['video_tags'] ) ), 'cp_tag' );
			}
		} elseif ( 'gaming' === $site_type ) {
			if ( ! empty( $_POST['video_genre'] ) ) {
				wp_set_post_terms( $post_id, array( absint( $_POST['video_genre'] ) ), 'cp_genre' );
			}
			if ( ! empty( $_POST['video_game'] ) ) {
				wp_set_post_terms( $post_id, array( absint( $_POST['video_game'] ) ), 'cp_game' );
			}
			if ( ! empty( $_POST['video_tags'] ) ) {
				wp_set_post_terms( $post_id, sanitize_text_field( wp_unslash( $_POST['video_tags'] ) ), 'cp_tag' );
			}
		} elseif ( 'podcast' === $site_type ) {
			if ( ! empty( $_POST['video_genre'] ) ) {
				wp_set_post_terms( $post_id, array( absint( $_POST['video_genre'] ) ), 'cp_genre' );
			}
			if ( ! empty( $_POST['video_topic'] ) ) {
				wp_set_post_terms( $post_id, array( absint( $_POST['video_topic'] ) ), 'cp_topic' );
			}
		}
		
		return array( '', __( 'Your channel video has been published.', 'cp-wp-plugin' ) );
	}

	public static function ajax_publish_channel_video() {
		if ( ! isset( $_POST['cpwp_channel_video_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpwp_channel_video_nonce'] ) ), 'cpwp_channel_video' ) ) {
			echo '<div class="cp-auth-error">' . esc_html__( 'Invalid security token.', 'cp-wp-plugin' ) . '</div>';
			wp_die();
		}
		list( $error, $success ) = self::publish_channel_video();
		if ( $error ) {
			echo '<div class="cp-auth-error">' . esc_html( $error ) . '</div>';
		} else {
			echo esc_html( $success );
		}
		wp_die();
	}


	private static function render_channel_video_form() {
		if ( ! CPWP_Settings::get( 'enable_creator_channels' ) || ! CPWP_Channels::get() ) return;
		?>
		<section class="cpwp-channel-panel"><h2><?php esc_html_e( 'Publish to your channel', 'cp-wp-plugin' ); ?></h2><form method="post" class="cp-auth-form"><?php wp_nonce_field( 'cpwp_profile', 'cpwp_auth_nonce' ); ?><?php wp_nonce_field( 'cpwp_channel_video', 'cpwp_channel_video_nonce' ); ?>
		<label><span><?php esc_html_e( 'Video title', 'cp-wp-plugin' ); ?></span><input name="channel_video_title" type="text" required></label>
		<label><span><?php esc_html_e( 'Description', 'cp-wp-plugin' ); ?></span><textarea name="channel_video_description" rows="4"></textarea></label>
		<label><span><?php esc_html_e( 'Video URL', 'cp-wp-plugin' ); ?></span><input id="cpwp-channel-video-url" name="channel_video_url" type="url" required></label>
		<button type="button" class="cp-button" id="cpwp-channel-upload"><?php esc_html_e( 'Upload video to my bucket', 'cp-wp-plugin' ); ?></button>
		<button class="cp-button" name="cpwp_publish_channel_video" value="1" type="submit"><?php esc_html_e( 'Publish video', 'cp-wp-plugin' ); ?></button></form></section>
		<?php
	}
	public static function block_user_admin() { if ( is_user_logged_in() && ! current_user_can( 'edit_posts' ) && ! wp_doing_ajax() && 'cpwp-group-moderation' !== sanitize_key( wp_unslash( $_GET['page'] ?? '' ) ) ) { wp_safe_redirect( self::profile_url() ); exit; } }
	public static function show_admin_bar( $show ) { return is_user_logged_in() && ! current_user_can( 'edit_posts' ) ? false : $show; }
	public static function require_login_for_comment( $data ) { if ( CPWP_Settings::get( 'comments_login_only' ) && 'cp_video' === get_post_type( absint( $data['comment_post_ID'] ?? 0 ) ) && ! is_user_logged_in() ) wp_die( esc_html__( 'You must be logged in to comment.', 'cp-wp-plugin' ), '', array( 'response' => 403 ) ); return $data; }
	public static function render_admin() {
		if ( ! current_user_can( 'list_users' ) ) return;
		if ( isset( $_POST['cpwp_user_id'], $_POST['cpwp_user_action'] ) && check_admin_referer( 'cpwp_manage_user' ) ) self::manage_user();
		$search = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) ); $users = get_users( array( 'number' => 200, 'search' => $search ? '*' . $search . '*' : '', 'orderby' => 'registered', 'order' => 'DESC' ) );
		echo '<div class="wrap"><h1>CP Users</h1><form method="get"><input type="hidden" name="post_type" value="cp_video"><input type="hidden" name="page" value="cpwp-users"><input name="s" value="' . esc_attr( $search ) . '" placeholder="Search users"><button class="button">Search</button></form><table class="widefat striped"><thead><tr><th>User</th><th>Role</th><th>Verified</th><th>Status</th><th>Strikes</th><th>Activity</th><th>Manage</th></tr></thead><tbody>';
		$log = get_option( CPWP_Moderation::LOG, array() );
		foreach ( $users as $user ) { $activity = count( array_filter( $log, function ( $item ) use ( $user ) { return absint( $item['user'] ?? 0 ) === $user->ID || absint( $item['target'] ?? 0 ) === $user->ID; } ) );
			echo '<tr><td><strong>' . esc_html( $user->display_name ) . '</strong><br>' . esc_html( $user->user_email ) . '</td><td>' . esc_html( implode( ', ', $user->roles ) ) . '</td><td>' . ( get_user_meta( $user->ID, self::VERIFIED_META, true ) ? 'Yes' : 'No' ) . '</td><td>' . ( get_user_meta( $user->ID, '_cpwp_suspended', true ) ? 'Suspended' : 'Active' ) . '</td><td>' . esc_html( absint( get_user_meta( $user->ID, '_cpwp_strikes', true ) ) ) . '</td><td>' . esc_html( $activity ) . ' events</td><td><form method="post">'; wp_nonce_field( 'cpwp_manage_user' );
			echo '<input type="hidden" name="cpwp_user_id" value="' . esc_attr( $user->ID ) . '"><select name="cpwp_user_action"><optgroup label="Account"><option value="verify">Verify email</option><option value="unverify">Unverify email</option></optgroup><optgroup label="Channel Verification"><option value="verify_channel">Approve channel</option><option value="reject_channel">Reject channel</option></optgroup><optgroup label="Moderation"><option value="suspend">Suspend user</option><option value="activate">Activate user</option><option value="clear_strikes">Clear strikes</option></optgroup><optgroup label="Danger Zone"><option value="delete">Delete user</option></optgroup></select> <button class="button">Apply</button></form></td></tr>'; }
		echo '</tbody></table></div>';
	}

	private static function manage_user() {
		$id = absint( $_POST['cpwp_user_id'] ); $action = sanitize_key( wp_unslash( $_POST['cpwp_user_action'] ) ); if ( ! $id || $id === get_current_user_id() ) return;
		if ( 'verify' === $action ) update_user_meta( $id, self::VERIFIED_META, 1 );
		elseif ( 'verify_channel' === $action ) update_user_meta( $id, CPWP_Creator_Platform::VERIFY_STATUS, 'approved' );
		elseif ( 'reject_channel' === $action ) update_user_meta( $id, CPWP_Creator_Platform::VERIFY_STATUS, 'rejected' );
		elseif ( 'unverify' === $action ) delete_user_meta( $id, self::VERIFIED_META );
		elseif ( 'suspend' === $action ) update_user_meta( $id, '_cpwp_suspended', 1 );
		elseif ( 'activate' === $action ) delete_user_meta( $id, '_cpwp_suspended' );
		elseif ( 'clear_strikes' === $action ) delete_user_meta( $id, '_cpwp_strikes' );
		elseif ( 'delete' === $action && current_user_can( 'delete_users' ) ) { require_once ABSPATH . 'wp-admin/includes/user.php'; wp_delete_user( $id ); }
		CPWP_Moderation::log( 'user_' . $action, get_current_user_id(), $id );
	}
	private static function auth_disabled() { wp_die( esc_html__( 'This account feature is currently disabled.', 'cp-wp-plugin' ), esc_html__( 'Page unavailable', 'cp-wp-plugin' ), array( 'response' => 404 ) ); }
}
