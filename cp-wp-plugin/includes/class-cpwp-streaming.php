<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Streaming {
	const TYPE = '_cpwp_streaming_type';
	const SERIES = '_cpwp_series_id';

	public static function register_menu() {
		if ( 'streaming' === CPWP_Settings::get( 'site_type' ) ) add_submenu_page( 'edit.php?post_type=cp_video', __( 'Season Management', 'cp-wp-plugin' ), __( 'Seasons', 'cp-wp-plugin' ), 'edit_others_posts', 'cpwp-seasons', array( __CLASS__, 'render_admin' ) );
	}

	public static function render_fields( $post ) {
		$type = get_post_meta( $post->ID, self::TYPE, true ) ?: 'standalone';
		echo '<p><label><strong>Streaming content type</strong><br><select style="width:100%" name="' . esc_attr( self::TYPE ) . '">';
		foreach ( array( 'standalone' => 'Standalone video', 'movie' => 'Movie', 'episode' => 'TV episode' ) as $value => $label ) printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $type, $value, false ), esc_html( $label ) );
		echo '</select></label></p><p><label><strong>Series</strong><br><select style="width:100%" name="' . esc_attr( self::SERIES ) . '"><option value="0">No series</option>';
		$current = absint( get_post_meta( $post->ID, self::SERIES, true ) );
		foreach ( get_posts( array( 'post_type' => 'cp_series', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ) as $series ) printf( '<option value="%d" %s>%s</option>', $series->ID, selected( $current, $series->ID, false ), esc_html( get_the_title( $series ) ) );
		echo '</select></label></p>';
	}

	public static function save( $post_id ) {
		if ( 'cp_video' !== get_post_type( $post_id ) || ! isset( $_POST['cpwp_module_nonce'] ) ) return;
		$type = sanitize_key( wp_unslash( $_POST[ self::TYPE ] ?? 'standalone' ) );
		$type = in_array( $type, array( 'standalone', 'movie', 'episode' ), true ) ? $type : 'standalone';
		update_post_meta( $post_id, self::TYPE, $type );
		$series_id = absint( $_POST[ self::SERIES ] ?? 0 );
		if ( 'episode' === $type && 'cp_series' === get_post_type( $series_id ) ) {
			update_post_meta( $post_id, self::SERIES, $series_id );
			update_post_meta( $post_id, '_cpwp_series_name', get_the_title( $series_id ) );
		} else {
			delete_post_meta( $post_id, self::SERIES );
			if ( 'movie' === $type ) delete_post_meta( $post_id, '_cpwp_series_name' );
		}
	}

	public static function episodes( $series_id, $season = 0 ) {
		$meta = array( array( 'key' => self::SERIES, 'value' => absint( $series_id ) ) );
		if ( $season ) $meta[] = array( 'key' => '_cpwp_season', 'value' => absint( $season ) );
		return get_posts( array( 'post_type' => 'cp_video', 'post_status' => 'publish', 'posts_per_page' => 200, 'meta_query' => $meta, 'meta_key' => '_cpwp_episode', 'orderby' => 'meta_value_num', 'order' => 'ASC' ) );
	}

	public static function render_admin() {
		$series_id = absint( $_GET['series_id'] ?? 0 );
		echo '<div class="wrap"><h1>Season Management</h1><form method="get"><input type="hidden" name="post_type" value="cp_video"><input type="hidden" name="page" value="cpwp-seasons"><select name="series_id"><option value="0">Select a TV series</option>';
		foreach ( get_posts( array( 'post_type' => 'cp_series', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ) as $series ) printf( '<option value="%d" %s>%s</option>', $series->ID, selected( $series_id, $series->ID, false ), esc_html( get_the_title( $series ) ) );
		echo '</select> <button class="button">View seasons</button></form>';
		if ( $series_id ) {
			$seasons = array(); foreach ( self::episodes( $series_id ) as $episode ) $seasons[ absint( get_post_meta( $episode->ID, '_cpwp_season', true ) ) ?: 1 ][] = $episode;
			foreach ( $seasons as $number => $episodes ) { echo '<h2>Season ' . esc_html( $number ) . '</h2><table class="widefat striped"><thead><tr><th>Episode</th><th>Title</th><th>Status</th><th>Manage</th></tr></thead><tbody>'; foreach ( $episodes as $episode ) printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td><a class="button" href="%s">Edit</a></td></tr>', esc_html( get_post_meta( $episode->ID, '_cpwp_episode', true ) ?: '-' ), esc_html( get_the_title( $episode ) ), esc_html( get_post_status_object( $episode->post_status )->label ), esc_url( get_edit_post_link( $episode ) ) ); echo '</tbody></table>'; }
		}
		echo '</div>';
	}
}
