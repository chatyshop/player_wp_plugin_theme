<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_Transcript {
	public static function search_join( $join, $query ) {
		global $wpdb;
		if ( self::is_video_search( $query ) && false === strpos( $join, 'cpwp_transcript_meta' ) ) {
			$join .= " LEFT JOIN {$wpdb->postmeta} AS cpwp_transcript_meta ON ({$wpdb->posts}.ID = cpwp_transcript_meta.post_id AND cpwp_transcript_meta.meta_key = '_cpwp_transcript')";
		}
		return $join;
	}

	public static function search_content( $search, $query ) {
		global $wpdb;
		if ( ! self::is_video_search( $query ) || ! $search ) {
			return $search;
		}

		$term = $query->get( 's' );
		$like = '%' . $wpdb->esc_like( $term ) . '%';
		return $wpdb->prepare( " AND (({$wpdb->posts}.post_title LIKE %s) OR ({$wpdb->posts}.post_excerpt LIKE %s) OR ({$wpdb->posts}.post_content LIKE %s) OR (cpwp_transcript_meta.meta_value LIKE %s))", $like, $like, $like, $like );
	}

	public static function search_distinct( $distinct, $query ) {
		return self::is_video_search( $query ) ? 'DISTINCT' : $distinct;
	}

	private static function is_video_search( $query ) {
		return ! is_admin() && $query->is_search() && $query->is_main_query();
	}
}
