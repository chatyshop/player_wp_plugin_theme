<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_Video_Fields {
	public static function add_meta_boxes() {
		add_meta_box( 'cpwp-video-settings', __( 'CP Video Settings', 'cp-wp-plugin' ), array( __CLASS__, 'render' ), 'cp_video', 'normal', 'high' );
	}

	public static function render( $post ) {
		wp_nonce_field( 'cpwp_save_video', 'cpwp_video_nonce' );
		$fields = array(
			'_cpwp_mp4'              => array( 'MP4 URL', 'url', true ),
			'_cpwp_webm'             => array( 'WebM URL', 'url', true ),
			'_cpwp_ogg'              => array( 'OGG URL', 'url', true ),
			'_cpwp_thumbnail_sprite' => array( 'Thumbnail sprite URL', 'url', true ),
			'_cpwp_thumb_width'      => array( 'Sprite frame width', 'number' ),
			'_cpwp_thumb_height'     => array( 'Sprite frame height', 'number' ),
			'_cpwp_thumb_columns'    => array( 'Sprite columns', 'number' ),
			'_cpwp_thumb_rows'       => array( 'Sprite rows', 'number' ),
			'_cpwp_thumb_interval'   => array( 'Sprite interval in seconds', 'number' ),
		);
		?>
		<div class="cpwp-fields">
			<p class="description"><?php esc_html_e( 'Use the Featured Image as the player poster.', 'cp-wp-plugin' ); ?></p>
			<div class="cpwp-field-grid">
				<?php foreach ( $fields as $key => $field ) : ?>
					<label>
						<span><?php echo esc_html( $field[0] ); ?></span>
						<span class="cpwp-input-row">
							<input type="<?php echo esc_attr( $field[1] ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( get_post_meta( $post->ID, $key, true ) ); ?>" min="0" step="any">
							<?php if ( ! empty( $field[2] ) ) : ?><button type="button" class="button cpwp-media-select" data-target="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Media', 'cp-wp-plugin' ); ?></button><button type="button" class="button cpwp-storage-upload" data-target="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Storage', 'cp-wp-plugin' ); ?></button><?php endif; ?>
						</span>
					</label>
				<?php endforeach; ?>
			</div>

			<h3><?php esc_html_e( 'Player settings', 'cp-wp-plugin' ); ?></h3>
			<div class="cpwp-player-settings">
				<label><input type="checkbox" name="_cpwp_autoplay" value="1" <?php checked( get_post_meta( $post->ID, '_cpwp_autoplay', true ) ); ?>> <?php esc_html_e( 'Autoplay', 'cp-wp-plugin' ); ?></label>
				<label><input type="checkbox" name="_cpwp_loop" value="1" <?php checked( get_post_meta( $post->ID, '_cpwp_loop', true ) ); ?>> <?php esc_html_e( 'Loop', 'cp-wp-plugin' ); ?></label>
				<label><input type="checkbox" name="_cpwp_muted" value="1" <?php checked( get_post_meta( $post->ID, '_cpwp_muted', true ) ); ?>> <?php esc_html_e( 'Muted', 'cp-wp-plugin' ); ?></label>
				<label><?php esc_html_e( 'Preload', 'cp-wp-plugin' ); ?>
					<select name="_cpwp_preload">
						<?php foreach ( array( 'metadata', 'auto', 'none' ) as $preload ) : ?>
							<option value="<?php echo esc_attr( $preload ); ?>" <?php selected( get_post_meta( $post->ID, '_cpwp_preload', true ) ?: 'metadata', $preload ); ?>><?php echo esc_html( ucfirst( $preload ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label><?php esc_html_e( 'Accent color', 'cp-wp-plugin' ); ?> <input type="color" name="_cpwp_accent_color" value="<?php echo esc_attr( get_post_meta( $post->ID, '_cpwp_accent_color', true ) ?: '#3b82f6' ); ?>"></label>
			</div>

			<div class="cpwp-preview-panel">
				<h3><?php esc_html_e( 'Video preview', 'cp-wp-plugin' ); ?></h3>
				<video id="cpwp-video-preview" controls preload="metadata"></video>
				<p id="cpwp-preview-empty" class="description"><?php esc_html_e( 'Enter or select a video URL to preview it.', 'cp-wp-plugin' ); ?></p>
			</div>

			<h3><?php esc_html_e( 'Subtitles', 'cp-wp-plugin' ); ?></h3>
			<div id="cpwp-subtitles" class="cpwp-repeater" data-template="cpwp-subtitle-template">
				<?php self::render_subtitles( get_post_meta( $post->ID, '_cpwp_subtitles', true ) ); ?>
			</div>
			<button type="button" class="button cpwp-add" data-target="cpwp-subtitles"><?php esc_html_e( 'Add subtitle', 'cp-wp-plugin' ); ?></button>
			<button type="button" class="button cpwp-storage-upload" data-target="subtitle-new"><?php esc_html_e( 'Upload subtitle to storage', 'cp-wp-plugin' ); ?></button>

			<h3><?php esc_html_e( 'Chapters', 'cp-wp-plugin' ); ?></h3>
			<div id="cpwp-chapters" class="cpwp-repeater" data-template="cpwp-chapter-template">
				<?php self::render_chapters( get_post_meta( $post->ID, '_cpwp_chapters', true ) ); ?>
			</div>
			<button type="button" class="button cpwp-add" data-target="cpwp-chapters"><?php esc_html_e( 'Add chapter', 'cp-wp-plugin' ); ?></button>

			<h3><?php esc_html_e( 'Transcript', 'cp-wp-plugin' ); ?></h3>
			<p class="description"><?php esc_html_e( 'The transcript is displayed below the video and included in WordPress search.', 'cp-wp-plugin' ); ?></p>
			<textarea class="cpwp-transcript" name="_cpwp_transcript" rows="12"><?php echo esc_textarea( get_post_meta( $post->ID, '_cpwp_transcript', true ) ); ?></textarea>
		</div>
		<script type="text/template" id="cpwp-subtitle-template"><?php self::subtitle_row( '__INDEX__', array() ); ?></script>
		<script type="text/template" id="cpwp-chapter-template"><?php self::chapter_row( '__INDEX__', array() ); ?></script>
		<?php
	}

	public static function save( $post_id ) {
		if ( ! isset( $_POST['cpwp_video_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpwp_video_nonce'] ) ), 'cpwp_save_video' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( array( '_cpwp_mp4', '_cpwp_webm', '_cpwp_ogg', '_cpwp_thumbnail_sprite' ) as $key ) {
			self::save_value( $post_id, $key, isset( $_POST[ $key ] ) ? esc_url_raw( wp_unslash( $_POST[ $key ] ) ) : '' );
		}
		foreach ( array( '_cpwp_thumb_width', '_cpwp_thumb_height', '_cpwp_thumb_columns', '_cpwp_thumb_rows', '_cpwp_thumb_interval' ) as $key ) {
			self::save_value( $post_id, $key, isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : '' );
		}
		foreach ( array( '_cpwp_autoplay', '_cpwp_loop', '_cpwp_muted' ) as $key ) {
			self::save_value( $post_id, $key, ! empty( $_POST[ $key ] ) ? '1' : '' );
		}
		$preload = isset( $_POST['_cpwp_preload'] ) ? sanitize_key( wp_unslash( $_POST['_cpwp_preload'] ) ) : 'metadata';
		self::save_value( $post_id, '_cpwp_preload', in_array( $preload, array( 'none', 'metadata', 'auto' ), true ) ? $preload : 'metadata' );
		self::save_value( $post_id, '_cpwp_accent_color', isset( $_POST['_cpwp_accent_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['_cpwp_accent_color'] ) ) : '' );

		self::save_value( $post_id, '_cpwp_subtitles', self::sanitize_subtitles( isset( $_POST['cpwp_subtitles'] ) ? wp_unslash( $_POST['cpwp_subtitles'] ) : array() ) );
		self::save_value( $post_id, '_cpwp_chapters', self::sanitize_chapters( isset( $_POST['cpwp_chapters'] ) ? wp_unslash( $_POST['cpwp_chapters'] ) : array() ) );
		self::save_value( $post_id, '_cpwp_transcript', isset( $_POST['_cpwp_transcript'] ) ? sanitize_textarea_field( wp_unslash( $_POST['_cpwp_transcript'] ) ) : '' );
	}

	private static function render_subtitles( $items ) {
		foreach ( is_array( $items ) ? $items : array() as $index => $item ) {
			self::subtitle_row( $index, $item );
		}
	}

	private static function subtitle_row( $index, $item ) {
		?>
		<div class="cpwp-repeater-row">
			<input type="url" name="cpwp_subtitles[<?php echo esc_attr( $index ); ?>][src]" value="<?php echo esc_attr( $item['src'] ?? '' ); ?>" placeholder="VTT URL">
			<input type="text" name="cpwp_subtitles[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $item['label'] ?? '' ); ?>" placeholder="English">
			<input type="text" name="cpwp_subtitles[<?php echo esc_attr( $index ); ?>][srclang]" value="<?php echo esc_attr( $item['srclang'] ?? '' ); ?>" placeholder="en">
			<label><input type="checkbox" name="cpwp_subtitles[<?php echo esc_attr( $index ); ?>][default]" value="1" <?php checked( ! empty( $item['default'] ) ); ?>> Default</label>
			<button type="button" class="button-link-delete cpwp-remove">Remove</button>
		</div>
		<?php
	}

	private static function render_chapters( $items ) {
		foreach ( is_array( $items ) ? $items : array() as $index => $item ) {
			self::chapter_row( $index, $item );
		}
	}

	private static function chapter_row( $index, $item ) {
		?>
		<div class="cpwp-repeater-row">
			<input type="number" min="0" step="any" name="cpwp_chapters[<?php echo esc_attr( $index ); ?>][time]" value="<?php echo esc_attr( $item['time'] ?? '' ); ?>" placeholder="Seconds">
			<input type="text" name="cpwp_chapters[<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $item['title'] ?? '' ); ?>" placeholder="Chapter title">
			<button type="button" class="button-link-delete cpwp-remove">Remove</button>
		</div>
		<?php
	}

	private static function sanitize_subtitles( $items ) {
		$clean = array();
		foreach ( is_array( $items ) ? $items : array() as $item ) {
			$track = array(
				'src'      => esc_url_raw( $item['src'] ?? '' ),
				'label'    => sanitize_text_field( $item['label'] ?? '' ),
				'srclang'  => sanitize_key( $item['srclang'] ?? '' ),
				'default'  => ! empty( $item['default'] ),
			);
			if ( $track['src'] && $track['label'] && $track['srclang'] ) {
				$clean[] = $track;
			}
		}
		return $clean;
	}

	private static function sanitize_chapters( $items ) {
		$clean = array();
		foreach ( is_array( $items ) ? $items : array() as $item ) {
			$title = sanitize_text_field( $item['title'] ?? '' );
			$time  = isset( $item['time'] ) ? max( 0, (float) $item['time'] ) : null;
			if ( '' !== $title && null !== $time ) {
				$clean[] = array( 'time' => $time, 'title' => $title );
			}
		}
		usort( $clean, function ( $a, $b ) { return $a['time'] <=> $b['time']; } );
		return $clean;
	}

	private static function save_value( $post_id, $key, $value ) {
		if ( '' === $value || array() === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
