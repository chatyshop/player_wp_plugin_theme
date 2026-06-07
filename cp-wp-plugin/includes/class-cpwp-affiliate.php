<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Affiliate {
	const PRICE = '_cpwp_product_price';
	const MERCHANT = '_cpwp_product_merchant';
	const COUPON = '_cpwp_product_coupon';
	const EXPIRY = '_cpwp_product_expiry';
	const COMPARE = '_cpwp_product_compare';
	const CLICKS = '_cpwp_affiliate_clicks';
	const LINK_STATUS = '_cpwp_affiliate_link_status';

	public static function register_routes() {
		if ( 'affiliate' !== CPWP_Settings::get( 'site_type' ) ) return;
		add_rewrite_rule( '^affiliate/go/([0-9]+)/?$', 'index.php?cpwp_affiliate_go=$matches[1]', 'top' );
		add_rewrite_tag( '%cpwp_affiliate_go%', '([0-9]+)' );
	}

	public static function add_meta_boxes() {
		if ( 'affiliate' === CPWP_Settings::get( 'site_type' ) ) add_meta_box( 'cpwp-affiliate-product', __( 'Affiliate Product Details', 'cp-wp-plugin' ), array( __CLASS__, 'render_fields' ), 'cp_product', 'normal', 'high' );
	}

	public static function render_fields( $post ) {
		wp_nonce_field( 'cpwp_affiliate_product', 'cpwp_affiliate_nonce' );
		foreach ( array( self::PRICE => array( 'Price', 'text' ), self::MERCHANT => array( 'Merchant', 'text' ), self::COUPON => array( 'Coupon code', 'text' ), self::EXPIRY => array( 'Coupon expiry', 'date' ) ) as $key => $field ) printf( '<p><label><strong>%s</strong><br><input style="width:100%%" type="%s" name="%s" value="%s"></label></p>', esc_html( $field[0] ), esc_attr( $field[1] ), esc_attr( $key ), esc_attr( get_post_meta( $post->ID, $key, true ) ) );
		echo '<p><label><input type="checkbox" name="' . esc_attr( self::COMPARE ) . '" value="1" ' . checked( get_post_meta( $post->ID, self::COMPARE, true ), '1', false ) . '> Include this product in comparison tables</label></p>';
	}

	public static function save( $post_id ) {
		if ( 'affiliate' !== CPWP_Settings::get( 'site_type' ) ) return;
		if ( 'cp_product' !== get_post_type( $post_id ) || ! isset( $_POST['cpwp_affiliate_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpwp_affiliate_nonce'] ) ), 'cpwp_affiliate_product' ) || ! current_user_can( 'edit_post', $post_id ) ) return;
		foreach ( array( self::PRICE, self::MERCHANT, self::COUPON, self::EXPIRY ) as $key ) { $value = sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) ); $value ? update_post_meta( $post_id, $key, $value ) : delete_post_meta( $post_id, $key ); }
		! empty( $_POST[ self::COMPARE ] ) ? update_post_meta( $post_id, self::COMPARE, '1' ) : delete_post_meta( $post_id, self::COMPARE );
	}

	public static function url( $product_id ) { return home_url( '/affiliate/go/' . absint( $product_id ) . '/' ); }

	public static function redirect() {
		if ( 'affiliate' !== CPWP_Settings::get( 'site_type' ) ) return;
		$product_id = absint( get_query_var( 'cpwp_affiliate_go' ) ); if ( ! $product_id ) return;
		$url = get_post_meta( $product_id, '_cpwp_external_url', true ); if ( 'cp_product' !== get_post_type( $product_id ) || ! $url ) wp_die( esc_html__( 'Affiliate link unavailable.', 'cp-wp-plugin' ), '', array( 'response' => 404 ) );
		update_post_meta( $product_id, self::CLICKS, absint( get_post_meta( $product_id, self::CLICKS, true ) ) + 1 );
		wp_redirect( esc_url_raw( $url ), 302, 'CP Affiliate' ); exit;
	}

	public static function register_menu() {
		if ( 'affiliate' === CPWP_Settings::get( 'site_type' ) ) add_submenu_page( 'edit.php?post_type=cp_video', __( 'Affiliate Tools', 'cp-wp-plugin' ), __( 'Affiliate Tools', 'cp-wp-plugin' ), 'manage_options', 'cpwp-affiliate-tools', array( __CLASS__, 'render_admin' ) );
	}

	public static function render_admin() {
		if ( ! empty( $_POST['cpwp_check_links'] ) && check_admin_referer( 'cpwp_check_links' ) ) self::check_links();
		$products = get_posts( array( 'post_type' => 'cp_product', 'posts_per_page' => 500 ) );
		echo '<div class="wrap"><h1>Affiliate Tools</h1><form method="post">'; wp_nonce_field( 'cpwp_check_links' ); echo '<button class="button button-primary" name="cpwp_check_links" value="1">Check product links</button></form><table class="widefat striped"><thead><tr><th>Product</th><th>Merchant</th><th>Price</th><th>Clicks</th><th>Link status</th><th>Comparison</th></tr></thead><tbody>';
		foreach ( $products as $product ) { $status = (array) get_post_meta( $product->ID, self::LINK_STATUS, true ); printf( '<tr><td><a href="%s">%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', esc_url( get_edit_post_link( $product ) ), esc_html( get_the_title( $product ) ), esc_html( get_post_meta( $product->ID, self::MERCHANT, true ) ), esc_html( get_post_meta( $product->ID, self::PRICE, true ) ), esc_html( absint( get_post_meta( $product->ID, self::CLICKS, true ) ) ), esc_html( $status['label'] ?? 'Not checked' ), get_post_meta( $product->ID, self::COMPARE, true ) ? 'Yes' : 'No' ); }
		echo '</tbody></table></div>';
	}

	private static function check_links() {
		foreach ( get_posts( array( 'post_type' => 'cp_product', 'posts_per_page' => 500 ) ) as $product ) {
			$url = get_post_meta( $product->ID, '_cpwp_external_url', true ); if ( ! $url ) { update_post_meta( $product->ID, self::LINK_STATUS, array( 'label' => 'Missing URL', 'checked' => time() ) ); continue; }
			$response = wp_remote_head( $url, array( 'timeout' => 8, 'redirection' => 5 ) ); if ( is_wp_error( $response ) ) $response = wp_remote_get( $url, array( 'timeout' => 8, 'redirection' => 5, 'limit_response_size' => 1 ) );
			$code = is_wp_error( $response ) ? 0 : wp_remote_retrieve_response_code( $response ); update_post_meta( $product->ID, self::LINK_STATUS, array( 'label' => $code >= 200 && $code < 400 ? 'Working (' . $code . ')' : 'Broken (' . ( $code ?: 'error' ) . ')', 'code' => $code, 'checked' => time() ) );
		}
	}
}
