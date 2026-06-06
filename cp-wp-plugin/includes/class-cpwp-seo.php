<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_SEO {
	public static function render_meta() {
		if ( ! is_singular( 'cp_video' ) ) {
			return;
		}

		$post_id     = get_queried_object_id();
		$title       = get_the_title( $post_id );
		$url         = get_permalink( $post_id );
		$description = get_the_excerpt( $post_id ) ?: wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 30 );
		$thumbnail   = get_the_post_thumbnail_url( $post_id, 'full' );
		$content_url = self::video_url( $post_id );
		$schema      = array_filter(
			array(
				'@context'      => 'https://schema.org',
				'@type'         => 'VideoObject',
				'name'          => $title,
				'description'   => $description ?: $title,
				'thumbnailUrl'  => $thumbnail ? array( $thumbnail ) : null,
				'uploadDate'    => get_the_date( DATE_W3C, $post_id ),
				'contentUrl'    => $content_url,
				'embedUrl'      => $url,
				'interactionStatistic' => array(
					'@type'                => 'InteractionCounter',
					'interactionType'      => array( '@type' => 'WatchAction' ),
					'userInteractionCount' => absint( get_post_meta( $post_id, '_cpwp_views', true ) ),
				),
			)
		);

		printf( "\n<meta property=\"og:type\" content=\"video.other\">\n" );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
		if ( $description ) {
			printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
		}
		if ( $thumbnail ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $thumbnail ) );
		}
		if ( $content_url ) {
			printf( '<meta property="og:video" content="%s">' . "\n", esc_url( $content_url ) );
		}
		printf( '<script type="application/ld+json">%s</script>' . "\n", wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) );
	}

	private static function video_url( $post_id ) {
		foreach ( array( '_cpwp_mp4', '_cpwp_webm', '_cpwp_ogg' ) as $key ) {
			$url = get_post_meta( $post_id, $key, true );
			if ( $url ) {
				return $url;
			}
		}
		return '';
	}
}
