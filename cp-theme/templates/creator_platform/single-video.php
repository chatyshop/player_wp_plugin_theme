<?php
/**
 * Template: Creator Platform — Single Video Page
 * YouTube-style: player + title + channel card + actions.
 * Sidebar: related videos from same channel or same category.
 */
get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-yt-content">
		<?php while ( have_posts() ) : the_post();
			$post_id      = get_the_ID();
			$views        = absint( get_post_meta( $post_id, '_cpwp_views', true ) );
			$owner        = absint( get_post_meta( $post_id, '_cpwp_channel_owner', true ) );
			$channel      = ( $owner && class_exists( 'CPWP_Channels' ) ) ? CPWP_Channels::get( $owner ) : array();
			$channel_url  = $channel ? CPWP_Channels::public_url( $channel ) : '';
			$ch_subs      = $channel ? count( CPWP_Channels::followers( $owner ) ) : 0;
			$categories   = get_the_terms( $post_id, 'category' ) ?: array();
			$cat_ids      = wp_list_pluck( $categories, 'term_id' );
		?>
		<div class="cp-yt-watch-layout">

			<!-- Main: player + info -->
			<div class="cp-yt-watch-main">

				<!-- Player -->
				<div class="cp-yt-player-box">
					<?php the_content(); ?>
				</div>

				<!-- Title & meta -->
				<div class="cp-yt-watch-info">
					<h1 class="cp-yt-watch-title"><?php the_title(); ?></h1>
					<div class="cp-yt-watch-toprow">
						<span class="cp-yt-view-count">
							<?php echo esc_html( sprintf( _n( '%s view', '%s views', $views, 'cp-theme' ), number_format_i18n( $views ) ) ); ?>
						</span>
						<span class="cp-yt-watch-date"><?php echo esc_html( get_the_date() ); ?></span>
						<?php if ( is_user_logged_in() ) : ?>
						<div class="cp-video-report-actions">
							<button class="cp-button" data-cpwp-report="content" data-target-id="<?php the_ID(); ?>"><?php esc_html_e( 'Report', 'cp-theme' ); ?></button>
						</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Channel card -->
				<?php if ( $channel ) : ?>
				<div class="cp-yt-channel-card">
					<a href="<?php echo esc_url( $channel_url ); ?>" class="cp-yt-channel-card-avatar">
						<img src="<?php echo esc_url( $channel['logo_url'] ?? get_avatar_url( $owner ) ); ?>" alt="">
					</a>
					<div class="cp-yt-channel-card-info">
						<a href="<?php echo esc_url( $channel_url ); ?>" class="cp-yt-channel-card-name">
							<?php echo esc_html( $channel['name'] ); ?>
						</a>
						<span class="cp-yt-channel-card-subs">
							<?php echo esc_html( number_format_i18n( $ch_subs ) ); ?> <?php esc_html_e( 'subscribers', 'cp-theme' ); ?>
						</span>
					</div>
					<?php if ( is_user_logged_in() && get_current_user_id() !== $owner ) : ?>
					<button class="cp-yt-subscribe-btn" data-cpwp-follow-channel="<?php echo esc_attr( $owner ); ?>">
						<?php esc_html_e( 'Subscribe', 'cp-theme' ); ?>
					</button>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<!-- Video description / details -->
				<div class="cp-yt-watch-description">
					<?php cp_theme_video_details( $post_id ); ?>
				</div>

				<!-- Genres, Topics, and Tags -->
				<?php 
				$genres = get_the_terms( $post_id, 'cp_genre' ) ?: array();
				$topics = get_the_terms( $post_id, 'cp_topic' ) ?: array();
				$tags   = get_the_terms( $post_id, 'cp_tag' ) ?: array();
				
				if ( ! empty( $genres ) && ! is_wp_error( $genres ) || ! empty( $topics ) && ! is_wp_error( $topics ) || ! empty( $tags ) && ! is_wp_error( $tags ) ) :
				?>
				<div class="cp-video-meta-taxonomies" style="margin-top: 15px; display: flex; flex-direction: column; gap: 10px;">
					<?php if ( ! empty( $genres ) && ! is_wp_error( $genres ) || ! empty( $topics ) && ! is_wp_error( $topics ) ) : ?>
					<div class="cp-video-meta-badges" style="display: flex; flex-wrap: wrap; gap: 8px;">
						<?php if ( ! empty( $genres ) && ! is_wp_error( $genres ) ) : foreach ( $genres as $genre ) : ?>
							<a href="<?php echo esc_url( get_term_link( $genre ) ); ?>" class="cp-badge cp-genre-badge" style="background: var(--cp-soft); color: var(--cp-accent); border: 1px solid var(--cp-line); padding: 4px 10px; border-radius: 999px; text-decoration: none; font-size: 0.8rem; font-weight: 600;">
								<?php echo esc_html( $genre->name ); ?>
							</a>
						<?php endforeach; endif; ?>
						<?php if ( ! empty( $topics ) && ! is_wp_error( $topics ) ) : foreach ( $topics as $topic ) : ?>
							<a href="<?php echo esc_url( get_term_link( $topic ) ); ?>" class="cp-badge cp-topic-badge" style="background: var(--cp-soft); color: var(--cp-accent); border: 1px solid var(--cp-line); padding: 4px 10px; border-radius: 999px; text-decoration: none; font-size: 0.8rem; font-weight: 600;">
								<?php echo esc_html( $topic->name ); ?>
							</a>
						<?php endforeach; endif; ?>
					</div>
					<?php endif; ?>

					<?php if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) : ?>
					<div class="cp-yt-watch-tags">
						<?php foreach ( $tags as $tag ) : ?>
						<a class="cp-yt-tag" href="<?php echo esc_url( get_term_link( $tag ) ); ?>">#<?php echo esc_html( $tag->name ); ?></a>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<!-- Comments -->
				<div class="cp-yt-watch-comments">
					<?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
				</div>
			</div>

			<!-- Sidebar: Up Next / Related -->
			<aside class="cp-yt-watch-aside">
				<h2 class="cp-yt-aside-heading"><?php esc_html_e( 'Up Next', 'cp-theme' ); ?></h2>
				<?php
				// Prefer same channel first, then same category.
				$related_args = array(
					'post_type'      => 'cp_video',
					'posts_per_page' => 8,
					'post__not_in'   => array( $post_id ),
				);
				if ( $owner ) {
					$related_args['meta_query'] = array( array( 'key' => '_cpwp_channel_owner', 'value' => $owner ) );
				} elseif ( $cat_ids ) {
					$related_args['tax_query'] = array( array( 'taxonomy' => 'category', 'field' => 'term_id', 'terms' => $cat_ids ) );
				} else {
					$related_args['orderby'] = 'rand';
				}
				$related = get_posts( $related_args );
				// If same-channel yields nothing, fall back to random.
				if ( ! $related ) {
					$related = get_posts( array( 'post_type' => 'cp_video', 'posts_per_page' => 8, 'post__not_in' => array( $post_id ), 'orderby' => 'rand' ) );
				}
				foreach ( $related as $rv ) :
					$rv_views = absint( get_post_meta( $rv->ID, '_cpwp_views', true ) );
					$rv_owner = absint( get_post_meta( $rv->ID, '_cpwp_channel_owner', true ) );
					$rv_ch    = ( $rv_owner && class_exists( 'CPWP_Channels' ) ) ? CPWP_Channels::get( $rv_owner ) : array();
					$rv_thumb = get_the_post_thumbnail_url( $rv->ID, 'medium' );
				?>
				<a class="cp-yt-related-row" href="<?php echo esc_url( get_permalink( $rv ) ); ?>">
					<div class="cp-yt-related-thumb">
						<?php if ( $rv_thumb ) : ?>
						<img src="<?php echo esc_url( $rv_thumb ); ?>" alt="" loading="lazy">
						<?php else : ?>
						<div class="cp-yt-thumb-placeholder">▶</div>
						<?php endif; ?>
					</div>
					<div class="cp-yt-related-info">
						<strong><?php echo esc_html( get_the_title( $rv ) ); ?></strong>
						<?php if ( $rv_ch ) : ?>
						<small><?php echo esc_html( $rv_ch['name'] ); ?></small>
						<?php endif; ?>
						<small><?php echo esc_html( number_format_i18n( $rv_views ) ); ?> <?php esc_html_e( 'views', 'cp-theme' ); ?> · <?php echo esc_html( get_the_date( '', $rv ) ); ?></small>
					</div>
				</a>
				<?php endforeach; ?>
			</aside>

		</div><!-- .cp-yt-watch-layout -->
		<?php endwhile; ?>
	</div>
</div>

<?php get_footer(); ?>
