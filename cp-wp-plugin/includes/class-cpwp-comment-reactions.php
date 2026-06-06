<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Comment_Reactions {
	const USER_META = '_cpwp_comment_reactions';
	const LIKE_META = '_cpwp_comment_likes';
	const DISLIKE_META = '_cpwp_comment_dislikes';

	public static function register_routes() {
		register_rest_route( 'cpwp/v1', '/comment-reactions/(?P<comment_id>\d+)', array(
			array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'state' ), 'permission_callback' => '__return_true' ),
			array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'update' ), 'permission_callback' => array( __CLASS__, 'can_react' ) ),
		) );
	}

	public static function register_menu() {
		add_submenu_page( 'edit.php?post_type=cp_video', __( 'Comment Reactions', 'cp-wp-plugin' ), __( 'Comment Reactions', 'cp-wp-plugin' ), 'moderate_comments', 'cpwp-comment-reactions', array( __CLASS__, 'render_admin' ) );
	}

	public static function can_react() {
		return CPWP_Settings::get( 'enable_comment_reactions' ) && is_user_logged_in();
	}

	public static function state( WP_REST_Request $request ) {
		$comment = get_comment( absint( $request['comment_id'] ) );
		if ( ! $comment || 'cp_video' !== get_post_type( $comment->comment_post_ID ) ) return new WP_Error( 'invalid_comment', __( 'Invalid comment.', 'cp-wp-plugin' ), array( 'status' => 404 ) );
		$reactions = is_user_logged_in() ? self::user_reactions( get_current_user_id() ) : array();
		return rest_ensure_response( array(
			'likes' => absint( get_comment_meta( $comment->comment_ID, self::LIKE_META, true ) ),
			'dislikes' => absint( get_comment_meta( $comment->comment_ID, self::DISLIKE_META, true ) ),
			'reaction' => $reactions[ $comment->comment_ID ] ?? '',
		) );
	}

	public static function update( WP_REST_Request $request ) {
		$comment_id = absint( $request['comment_id'] );
		$comment = get_comment( $comment_id );
		if ( ! $comment || 'cp_video' !== get_post_type( $comment->comment_post_ID ) ) return new WP_Error( 'invalid_comment', __( 'Invalid comment.', 'cp-wp-plugin' ), array( 'status' => 404 ) );
		$value = sanitize_key( $request['reaction'] );
		if ( ! in_array( $value, array( 'like', 'dislike', '' ), true ) ) return new WP_Error( 'invalid_reaction', __( 'Invalid reaction.', 'cp-wp-plugin' ), array( 'status' => 400 ) );
		$user_id = get_current_user_id();
		$reactions = self::user_reactions( $user_id );
		$old = $reactions[ $comment_id ] ?? '';
		if ( $old ) self::increment( $comment_id, $old, -1 );
		if ( $value && $value !== $old ) {
			$reactions[ $comment_id ] = $value;
			self::increment( $comment_id, $value, 1 );
		} else {
			unset( $reactions[ $comment_id ] );
		}
		update_user_meta( $user_id, self::USER_META, $reactions );
		return self::state( $request );
	}

	public static function render_admin() {
		if ( ! current_user_can( 'moderate_comments' ) ) return;
		if ( isset( $_POST['cpwp_clear_comment_reactions'] ) ) {
			check_admin_referer( 'cpwp_clear_comment_reactions' );
			$comment_id = absint( $_POST['comment_id'] ?? 0 );
			delete_comment_meta( $comment_id, self::LIKE_META );
			delete_comment_meta( $comment_id, self::DISLIKE_META );
			self::remove_from_users( $comment_id );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Comment reactions cleared.', 'cp-wp-plugin' ) . '</p></div>';
		}
		$comments = get_comments( array( 'post_type' => 'cp_video', 'number' => 100, 'status' => 'all', 'meta_query' => array( 'relation' => 'OR', array( 'key' => self::LIKE_META, 'compare' => 'EXISTS' ), array( 'key' => self::DISLIKE_META, 'compare' => 'EXISTS' ) ) ) );
		?>
		<div class="wrap"><h1><?php esc_html_e( 'Comment Reactions', 'cp-wp-plugin' ); ?></h1><p><?php esc_html_e( 'Review database-backed likes and dislikes on video comments.', 'cp-wp-plugin' ); ?></p>
		<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Comment', 'cp-wp-plugin' ); ?></th><th><?php esc_html_e( 'Video', 'cp-wp-plugin' ); ?></th><th><?php esc_html_e( 'Likes', 'cp-wp-plugin' ); ?></th><th><?php esc_html_e( 'Dislikes', 'cp-wp-plugin' ); ?></th><th><?php esc_html_e( 'Actions', 'cp-wp-plugin' ); ?></th></tr></thead><tbody>
		<?php if ( ! $comments ) : ?><tr><td colspan="5"><?php esc_html_e( 'No comment reactions yet.', 'cp-wp-plugin' ); ?></td></tr><?php endif; ?>
		<?php foreach ( $comments as $comment ) : ?><tr><td><?php echo esc_html( wp_trim_words( $comment->comment_content, 18 ) ); ?></td><td><a href="<?php echo esc_url( get_permalink( $comment->comment_post_ID ) ); ?>"><?php echo esc_html( get_the_title( $comment->comment_post_ID ) ); ?></a></td><td><?php echo esc_html( absint( get_comment_meta( $comment->comment_ID, self::LIKE_META, true ) ) ); ?></td><td><?php echo esc_html( absint( get_comment_meta( $comment->comment_ID, self::DISLIKE_META, true ) ) ); ?></td><td><form method="post"><?php wp_nonce_field( 'cpwp_clear_comment_reactions' ); ?><input type="hidden" name="comment_id" value="<?php echo esc_attr( $comment->comment_ID ); ?>"><button class="button" name="cpwp_clear_comment_reactions" value="1"><?php esc_html_e( 'Clear reactions', 'cp-wp-plugin' ); ?></button></form></td></tr><?php endforeach; ?>
		</tbody></table></div>
		<?php
	}

	private static function increment( $comment_id, $reaction, $amount ) {
		$key = 'like' === $reaction ? self::LIKE_META : self::DISLIKE_META;
		update_comment_meta( $comment_id, $key, max( 0, absint( get_comment_meta( $comment_id, $key, true ) ) + $amount ) );
	}

	private static function user_reactions( $user_id ) {
		$value = get_user_meta( $user_id, self::USER_META, true );
		return is_array( $value ) ? $value : array();
	}

	private static function remove_from_users( $comment_id ) {
		$users = get_users( array( 'meta_key' => self::USER_META, 'fields' => 'ids' ) );
		foreach ( $users as $user_id ) {
			$reactions = self::user_reactions( $user_id );
			unset( $reactions[ $comment_id ] );
			update_user_meta( $user_id, self::USER_META, $reactions );
		}
	}
}
