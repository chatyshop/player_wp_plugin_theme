<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_Video_Archive {
	public static function render_archive_card( $content ) {
		if ( ! is_post_type_archive( 'cp_video' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		return self::card( get_the_ID() );
	}

	public static function shortcode( $attributes ) {
		$attributes = shortcode_atts( array( 'limit' => 12, 'filters' => 'true' ), $attributes, 'cp_video_grid' );
		$page       = isset( $_GET['cp_page'] ) ? max( 1, absint( $_GET['cp_page'] ) ) : 1;
		$search     = isset( $_GET['cp_search'] ) ? sanitize_text_field( wp_unslash( $_GET['cp_search'] ) ) : '';
		$category   = isset( $_GET['cp_category'] ) ? absint( $_GET['cp_category'] ) : 0;
		$sort       = isset( $_GET['cp_sort'] ) ? sanitize_key( wp_unslash( $_GET['cp_sort'] ) ) : 'newest';
		$args       = array(
			'post_type'      => 'cp_video',
			'posts_per_page' => min( 50, max( 1, absint( $attributes['limit'] ) ) ),
			'paged'          => $page,
			's'              => $search,
		);
		if ( $category ) {
			$args['cat'] = $category;
		}
		if ( 'oldest' === $sort ) {
			$args['order'] = 'ASC';
		} elseif ( 'views' === $sort ) {
			$args['meta_key'] = '_cpwp_views';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
		}
		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p>' . esc_html__( 'No videos found.', 'cp-wp-plugin' ) . '</p>';
		}

		CPWP_Assets::enqueue_player_assets();
		$html = 'true' === $attributes['filters'] ? self::filters( $search, $category, $sort ) : '';
		$html .= '<div class="cpwp-video-grid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$html .= self::card( get_the_ID() );
		}
		wp_reset_postdata();

		$html .= '</div>';
		if ( $query->max_num_pages > 1 ) {
			$html .= '<nav class="cpwp-pagination">';
			for ( $i = 1; $i <= $query->max_num_pages; $i++ ) {
				$html .= sprintf( '<a class="%s" href="%s">%d</a>', $i === $page ? 'is-current' : '', esc_url( add_query_arg( 'cp_page', $i ) ), $i );
			}
			$html .= '</nav>';
		}
		return $html;
	}

	private static function filters( $search, $category, $sort ) {
		$categories = get_categories( array( 'taxonomy' => 'category', 'hide_empty' => true ) );
		$html       = '<form class="cpwp-filters" method="get"><input type="search" name="cp_search" value="' . esc_attr( $search ) . '" placeholder="' . esc_attr__( 'Search videos', 'cp-wp-plugin' ) . '"><select name="cp_category"><option value="0">' . esc_html__( 'All categories', 'cp-wp-plugin' ) . '</option>';
		foreach ( $categories as $item ) {
			$html .= sprintf( '<option value="%d" %s>%s</option>', $item->term_id, selected( $category, $item->term_id, false ), esc_html( $item->name ) );
		}
		$html .= '</select><select name="cp_sort">';
		foreach ( array( 'newest' => __( 'Newest', 'cp-wp-plugin' ), 'oldest' => __( 'Oldest', 'cp-wp-plugin' ), 'views' => __( 'Most viewed', 'cp-wp-plugin' ) ) as $value => $label ) {
			$html .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $sort, $value, false ), esc_html( $label ) );
		}
		return $html . '</select><button type="submit">' . esc_html__( 'Apply', 'cp-wp-plugin' ) . '</button></form>';
	}

	public static function related( $post_id ) {
		$categories = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'ids' ) );
		$tags       = wp_get_post_terms( $post_id, 'post_tag', array( 'fields' => 'ids' ) );
		$args       = array(
			'post_type'      => 'cp_video',
			'posts_per_page' => 3,
			'post__not_in'   => array( $post_id ),
		);
		$tax_query = array( 'relation' => 'OR' );
		if ( $categories ) {
			$tax_query[] = array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $categories );
		}
		if ( $tags ) {
			$tax_query[] = array( 'taxonomy' => 'post_tag', 'field' => 'term_id', 'terms' => $tags );
		}
		if ( count( $tax_query ) > 1 ) {
			$args['tax_query'] = $tax_query;
		}

		$query = new WP_Query( $args );
		if ( ! $query->have_posts() ) {
			return '';
		}

		$html = '<section class="cpwp-related"><h2>' . esc_html__( 'Related videos', 'cp-wp-plugin' ) . '</h2><div class="cpwp-video-grid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$html .= self::card( get_the_ID() );
		}
		wp_reset_postdata();
		return $html . '</div></section>';
	}

	public static function card( $post_id ) {
		CPWP_Assets::enqueue_player_assets();
		$title     = get_the_title( $post_id );
		$thumbnail = get_the_post_thumbnail( $post_id, 'medium_large', array( 'loading' => 'lazy' ) );
		$excerpt   = get_the_excerpt( $post_id );
		$views     = absint( get_post_meta( $post_id, '_cpwp_views', true ) );

		if ( ! $thumbnail ) {
			$thumbnail = '<span class="cpwp-card-placeholder"><span class="dashicons dashicons-video-alt3"></span></span>';
		}

		return sprintf(
			'<article class="cpwp-video-card"><a class="cpwp-card-media" href="%1$s">%2$s<span class="cpwp-card-play" aria-hidden="true">&#9654;</span></a><div class="cpwp-card-body"><h2 class="cpwp-card-title"><a href="%1$s">%3$s</a></h2><p class="cpwp-card-meta">%4$s</p>%5$s</div></article>',
			esc_url( get_permalink( $post_id ) ),
			$thumbnail,
			esc_html( $title ),
			esc_html( sprintf( _n( '%s view', '%s views', $views, 'cp-wp-plugin' ), number_format_i18n( $views ) ) ),
			$excerpt ? '<p class="cpwp-card-excerpt">' . esc_html( wp_trim_words( $excerpt, 20 ) ) . '</p>' : ''
		);
	}
}
