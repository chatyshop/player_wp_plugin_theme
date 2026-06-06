<?php
if ( ! defined( 'ABSPATH' ) ) exit;
final class CPWP_Bulk_Videos {
	public static function register_menu() { add_submenu_page( 'edit.php?post_type=cp_video', 'Bulk Videos', 'Bulk Videos', 'edit_others_posts', 'cpwp-bulk-videos', array( __CLASS__, 'render' ) ); }
	public static function render() {
		$message = '';
		if ( ! empty( $_POST['cpwp_import_csv'] ) && check_admin_referer( 'cpwp_import_csv' ) ) $message = self::import();
		echo '<div class="wrap"><h1>Bulk Video Import</h1>' . ( $message ? '<div class="notice notice-success"><p>' . esc_html( $message ) . '</p></div>' : '' ) . '<p>Paste CSV with columns: title,description,video_url,download_url,series,season,episode,visibility.</p><form method="post">'; wp_nonce_field( 'cpwp_import_csv' ); echo '<textarea name="cpwp_csv" rows="18" style="width:100%"></textarea><p><button class="button button-primary" name="cpwp_import_csv" value="1">Import videos</button></p></form></div>';
	}
	private static function import() {
		$lines = preg_split( '/\r\n|\r|\n/', wp_unslash( $_POST['cpwp_csv'] ?? '' ) ); $headers = array_map( 'sanitize_key', str_getcsv( array_shift( $lines ) ) ); $count = 0;
		foreach ( $lines as $line ) { if ( ! trim( $line ) ) continue; $row = array_combine( $headers, array_pad( str_getcsv( $line ), count( $headers ), '' ) ); if ( empty( $row['title'] ) ) continue;
			$id = wp_insert_post( array( 'post_type' => 'cp_video', 'post_status' => 'draft', 'post_title' => sanitize_text_field( $row['title'] ), 'post_content' => wp_kses_post( $row['description'] ?? '' ) ) ); if ( ! $id ) continue;
			$map = array( 'video_url' => '_cpwp_mp4', 'download_url' => '_cpwp_download_url', 'series' => '_cpwp_series_name', 'season' => '_cpwp_season', 'episode' => '_cpwp_episode', 'visibility' => '_cpwp_visibility' );
			foreach ( $map as $column => $meta ) if ( ! empty( $row[ $column ] ) ) update_post_meta( $id, $meta, in_array( $column, array( 'video_url', 'download_url' ), true ) ? esc_url_raw( $row[ $column ] ) : sanitize_text_field( $row[ $column ] ) ); $count++;
		}
		CPWP_Moderation::log( 'bulk_import', get_current_user_id(), 0, $count . ' videos' ); return sprintf( '%d videos imported as drafts.', $count );
	}
}
