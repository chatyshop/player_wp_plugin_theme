<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_Player_Renderer {
	public static function render( $post_id ) {
		$post_id = absint( $post_id );
		$mp4     = get_post_meta( $post_id, '_cpwp_mp4', true );
		$webm    = get_post_meta( $post_id, '_cpwp_webm', true );
		$ogg     = get_post_meta( $post_id, '_cpwp_ogg', true );

		if ( ! $mp4 && ! $webm && ! $ogg ) {
			return current_user_can( 'edit_post', $post_id )
				? '<p class="cpwp-notice">' . esc_html__( 'Add a video source to display ChatyPlayer.', 'cp-wp-plugin' ) . '</p>'
				: '';
		}

		CPWP_Assets::enqueue_player_assets();

		$attributes = array(
			'class'               => 'chaty-player cpwp-player',
			'data-cpwp-video-id'  => (string) $post_id,
			'data-cpwp-token'     => wp_hash( 'cpwp-analytics-' . $post_id ),
			'data-mp4'            => $mp4,
			'data-webm'           => $webm,
			'data-ogg'            => $ogg,
			'data-poster'         => get_post_meta( $post_id, '_cpwp_poster_url', true ) ?: get_the_post_thumbnail_url( $post_id, 'full' ),
			'data-thumbnails'     => get_post_meta( $post_id, '_cpwp_thumbnail_sprite', true ),
			'data-thumb-width'    => get_post_meta( $post_id, '_cpwp_thumb_width', true ),
			'data-thumb-height'   => get_post_meta( $post_id, '_cpwp_thumb_height', true ),
			'data-thumb-columns'  => get_post_meta( $post_id, '_cpwp_thumb_columns', true ),
			'data-thumb-rows'     => get_post_meta( $post_id, '_cpwp_thumb_rows', true ),
			'data-thumb-interval' => get_post_meta( $post_id, '_cpwp_thumb_interval', true ),
			'data-subtitles'      => self::json_attribute( get_post_meta( $post_id, '_cpwp_subtitles', true ) ),
			'data-chapters'       => self::json_attribute( get_post_meta( $post_id, '_cpwp_chapters', true ) ),
			'data-autoplay'       => get_post_meta( $post_id, '_cpwp_autoplay', true ) ? 'true' : 'false',
			'data-loop'           => get_post_meta( $post_id, '_cpwp_loop', true ) ? 'true' : 'false',
			'data-muted'          => get_post_meta( $post_id, '_cpwp_muted', true ) || CPWP_Settings::get( 'default_muted' ) ? 'true' : 'false',
			'data-preload'        => get_post_meta( $post_id, '_cpwp_preload', true ) ?: CPWP_Settings::get( 'default_preload' ),
			'data-color'          => get_post_meta( $post_id, '_cpwp_accent_color', true ) ?: CPWP_Settings::get( 'accent_color' ),
			'data-cpwp-preroll'   => CPWP_Settings::get( 'enable_monetization' ) ? CPWP_Monetization::player_url( 'preroll', $post_id ) : '',
			'data-cpwp-postroll'  => CPWP_Settings::get( 'enable_monetization' ) ? CPWP_Monetization::player_url( 'postroll', $post_id ) : '',
		);

		$html = '<div';
		foreach ( array_filter( $attributes, 'strlen' ) as $name => $value ) {
			$html .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}

		return $html . '></div>';
	}

	public static function prepend_to_video_content( $content ) {
		static $running = false;
		if ( $running ) return $content;

		if ( is_singular( 'cp_video' ) && in_the_loop() && is_main_query() ) {
			$running = true;
			$post_id    = get_the_ID();
			$views      = absint( get_post_meta( $post_id, '_cpwp_views', true ) );
			$categories = get_the_term_list( $post_id, 'category', '', ', ' );
			$transcript = get_post_meta( $post_id, '_cpwp_transcript', true );
			$channel_owner = absint( get_post_meta( $post_id, '_cpwp_channel_owner', true ) );
			$channel = $channel_owner && class_exists( 'CPWP_Channels' ) ? CPWP_Channels::get( $channel_owner ) : array();
			$meta       = sprintf(
				'<div class="cpwp-video-meta"><span>%s</span><span>%s</span>%s</div>',
				esc_html( sprintf( _n( '%s view', '%s views', $views, 'cp-wp-plugin' ), number_format_i18n( $views ) ) ),
				esc_html( get_the_date( '', $post_id ) ),
				$categories ? '<span>' . wp_kses_post( $categories ) . '</span>' : ''
			);

			$transcript_html = $transcript && CPWP_Settings::get( 'show_transcript' )
				? '<details class="cpwp-transcript-panel"><summary>' . esc_html__( 'Read transcript', 'cp-wp-plugin' ) . '</summary><div>' . wpautop( esc_html( $transcript ) ) . '</div></details>'
				: '';

			$sharing = CPWP_Settings::get( 'show_sharing' ) ? self::share_controls( $post_id ) : '';
			$engagement = self::engagement_controls( $post_id );
			$related = CPWP_Settings::get( 'show_related' ) ? CPWP_Video_Archive::related( $post_id ) : '';
			$channel_html = $channel ? '<div class="cpwp-channel-attribution">' . ( ! empty( $channel['logo_url'] ) ? '<img src="' . esc_url( $channel['logo_url'] ) . '" alt="">' : '' ) . '<div><strong>' . esc_html( $channel['name'] ) . '</strong><span>' . esc_html( $channel['description'] ?? '' ) . '</span></div></div>' : '';
			$html = '<div class="cpwp-single-video">' . CPWP_Monetization::render( 'video_above', $post_id ) . self::render( $post_id ) . CPWP_Monetization::render( 'video_below', $post_id ) . $meta . $channel_html . $engagement . $sharing . '<div class="cpwp-video-description">' . $content . CPWP_Monetization::render( 'video_description', $post_id ) . '</div>' . $transcript_html . $related . '</div>';
			$running = false;
			return $html;
		}

		return $content;
	}

	private static function json_attribute( $value ) {
		return is_array( $value ) && $value ? wp_json_encode( $value ) : '';
	}

	private static function share_controls( $post_id ) {
		$url   = get_permalink( $post_id );
		$title = get_the_title( $post_id );
		return sprintf(
			'<div class="cpwp-share" data-url="%1$s" data-title="%2$s">
				<button type="button" class="cpwp-native-share">
					<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
					<span>%3$s</span>
				</button>
				<button type="button" class="cpwp-copy-link">
					<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
					<span>%4$s</span>
				</button>
				<a target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u=%1$s">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
					<span>Facebook</span>
				</a>
				<a target="_blank" rel="noopener noreferrer" href="https://twitter.com/intent/tweet?url=%1$s&amp;text=%2$s">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
					<span>X</span>
				</a>
			</div>',
			esc_url( $url ),
			esc_attr( $title ),
			esc_html__( 'Share', 'cp-wp-plugin' ),
			esc_html__( 'Copy link', 'cp-wp-plugin' )
		);
	}

	private static function engagement_controls( $post_id ) {
		if ( ! CPWP_Settings::get( 'enable_reactions' ) && ! CPWP_Settings::get( 'enable_favorites_watch_later' ) && ! CPWP_Settings::get( 'enable_playlists' ) ) return '';
		return sprintf(
			'<div class="cpwp-engagement" data-video-id="%d">
				<div class="cpwp-reactions-group">
					<button type="button" data-action="like">
						<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
						Like <span>%d</span>
					</button>
					<button type="button" data-action="dislike">
						<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm12-3h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"></path></svg>
						Dislike <span>%d</span>
					</button>
				</div>
				<button type="button" data-action="favorite">
					<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
					Favorite
				</button>
				<button type="button" data-action="watch_later">
					<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
					Watch later
				</button>
				<div class="cpwp-playlist-container">
					<button type="button" data-action="playlist">
						<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="17" y1="10" x2="23" y2="10"></line><line x1="20" y1="7" x2="20" y2="13"></line><path d="M14 6H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2z"></path></svg>
						Playlist
					</button>
					<div class="cpwp-playlist-menu" hidden></div>
				</div>
			</div>',
			absint( $post_id ),
			absint( get_post_meta( $post_id, '_cpwp_likes', true ) ),
			absint( get_post_meta( $post_id, '_cpwp_dislikes', true ) )
		);
	}
}
