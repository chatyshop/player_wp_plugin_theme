<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_Dashboard {
	public static function register() {
		add_submenu_page( 'edit.php?post_type=cp_video', __( 'CP Dashboard', 'cp-wp-plugin' ), __( 'Dashboard', 'cp-wp-plugin' ), 'edit_posts', 'cpwp-dashboard', array( __CLASS__, 'render' ), 0 );
		CPWP_Settings::register_menu();
		CPWP_Users::register_menu();
	}

	public static function render() {
		$counts      = wp_count_posts( 'cp_video' );
		$total_views = self::total_views();
		$watch_time  = self::total_meta( '_cpwp_watch_time' );
		$completions = self::total_meta( '_cpwp_completions' );
		$top_videos  = get_posts(
			array(
				'post_type'      => 'cp_video',
				'posts_per_page' => 5,
				'meta_key'       => '_cpwp_views',
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC',
			)
		);
		?>
		<div class="wrap cpwp-dashboard">
			<h1><?php esc_html_e( 'CP Video Dashboard', 'cp-wp-plugin' ); ?></h1>
			<div class="cpwp-stats">
				<div><strong><?php echo esc_html( number_format_i18n( $counts->publish ?? 0 ) ); ?></strong><span><?php esc_html_e( 'Published videos', 'cp-wp-plugin' ); ?></span></div>
				<div><strong><?php echo esc_html( number_format_i18n( $counts->draft ?? 0 ) ); ?></strong><span><?php esc_html_e( 'Draft videos', 'cp-wp-plugin' ); ?></span></div>
				<div><strong><?php echo esc_html( number_format_i18n( $total_views ) ); ?></strong><span><?php esc_html_e( 'Total views', 'cp-wp-plugin' ); ?></span></div>
				<div><strong><?php echo esc_html( self::format_duration( $total_views ? round( $watch_time / $total_views ) : 0 ) ); ?></strong><span><?php esc_html_e( 'Average watch time', 'cp-wp-plugin' ); ?></span></div>
				<div><strong><?php echo esc_html( $total_views ? round( ( $completions / $total_views ) * 100, 1 ) : 0 ); ?>%</strong><span><?php esc_html_e( 'Completion rate', 'cp-wp-plugin' ); ?></span></div>
			</div>
			<div class="cpwp-dashboard-panel">
				<h2><?php esc_html_e( 'Views in the last 7 days', 'cp-wp-plugin' ); ?></h2>
				<div class="cpwp-chart"><?php foreach ( self::daily_views() as $date => $views ) : ?><div><strong><?php echo esc_html( number_format_i18n( $views['views'] ) ); ?></strong><span style="height:<?php echo esc_attr( max( 4, min( 100, $views['height'] ) ) ); ?>%"></span><small><?php echo esc_html( wp_date( 'M j', strtotime( $date ) ) ); ?></small></div><?php endforeach; ?></div>
			</div>
			<div class="cpwp-dashboard-panel">
				<h2><?php esc_html_e( 'Per-video analytics', 'cp-wp-plugin' ); ?></h2>
				<?php if ( $top_videos ) : ?>
					<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Video', 'cp-wp-plugin' ); ?></th><th><?php esc_html_e( 'Views', 'cp-wp-plugin' ); ?></th><th><?php esc_html_e( 'Watch time', 'cp-wp-plugin' ); ?></th><th><?php esc_html_e( 'Avg. watch', 'cp-wp-plugin' ); ?></th><th><?php esc_html_e( 'Completion', 'cp-wp-plugin' ); ?></th></tr></thead><tbody>
					<?php foreach ( $top_videos as $video ) : ?>
						<?php
						$video_views       = absint( get_post_meta( $video->ID, '_cpwp_views', true ) );
						$video_watch_time  = absint( get_post_meta( $video->ID, '_cpwp_watch_time', true ) );
						$video_completions = absint( get_post_meta( $video->ID, '_cpwp_completions', true ) );
						?>
						<tr><td><a href="<?php echo esc_url( get_edit_post_link( $video->ID ) ); ?>"><?php echo esc_html( get_the_title( $video ) ); ?></a></td><td><?php echo esc_html( number_format_i18n( $video_views ) ); ?></td><td><?php echo esc_html( self::format_duration( $video_watch_time ) ); ?></td><td><?php echo esc_html( self::format_duration( $video_views ? round( $video_watch_time / $video_views ) : 0 ) ); ?></td><td><?php echo esc_html( $video_views ? round( ( $video_completions / $video_views ) * 100, 1 ) : 0 ); ?>%</td></tr>
					<?php endforeach; ?>
					</tbody></table>
				<?php else : ?>
					<p><?php esc_html_e( 'Publish videos to see statistics here.', 'cp-wp-plugin' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	private static function total_views() {
		return self::total_meta( '_cpwp_views' );
	}

	private static function total_meta( $key ) {
		global $wpdb;
		return absint( $wpdb->get_var( $wpdb->prepare( "SELECT SUM(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} WHERE meta_key = %s", $key ) ) );
	}

	private static function daily_views() {
		$days = array();
		for ( $i = 6; $i >= 0; $i-- ) {
			$days[ wp_date( 'Y-m-d', strtotime( "-{$i} days" ) ) ] = 0;
		}
		$videos = get_posts( array( 'post_type' => 'cp_video', 'posts_per_page' => -1, 'fields' => 'ids' ) );
		foreach ( $videos as $video_id ) {
			$daily = get_post_meta( $video_id, '_cpwp_daily_analytics', true );
			foreach ( $days as $date => $value ) {
				if ( isset( $daily[ $date ] ) && is_array( $daily[ $date ] ) ) {
					$days[ $date ] += absint( $daily[ $date ]['views'] ?? 0 );
				}
			}
		}
		$max = max( 1, max( $days ) );
		foreach ( $days as $date => $views ) {
			$days[ $date ] = array( 'views' => $views, 'height' => ( $views / $max ) * 100 );
		}
		return $days;
	}

	private static function format_duration( $seconds ) {
		$seconds = absint( $seconds );
		if ( $seconds < MINUTE_IN_SECONDS ) {
			return sprintf( __( '%s sec', 'cp-wp-plugin' ), number_format_i18n( $seconds ) );
		}
		if ( $seconds < HOUR_IN_SECONDS ) {
			return sprintf( __( '%s min', 'cp-wp-plugin' ), number_format_i18n( round( $seconds / MINUTE_IN_SECONDS ) ) );
		}
		return sprintf( __( '%s hr', 'cp-wp-plugin' ), number_format_i18n( round( $seconds / HOUR_IN_SECONDS, 1 ) ) );
	}
}
