<?php
/**
 * Template: Streaming — Single Video Page
 * Cinematic full-width player. Sidebar shows:
 *  - If TV Episode: other episodes from the same series in order.
 *  - If Movie: other trending movies.
 *  - Otherwise: latest videos.
 */
get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-streaming-content">
		<?php while ( have_posts() ) : the_post();
			$post_id     = get_the_ID();
			$v_type      = get_post_meta( $post_id, '_cpwp_streaming_type', true ) ?: 'standalone';
			$series_name = get_post_meta( $post_id, '_cpwp_series_name', true );
			$season      = get_post_meta( $post_id, '_cpwp_season', true );
			$episode_num = get_post_meta( $post_id, '_cpwp_episode', true );
			$rating      = get_post_meta( $post_id, '_cpwp_age_rating', true );
		?>
		<article class="cp-ott-watch-layout">

			<!-- Player column -->
			<div class="cp-ott-player-col">
				<div class="cp-ott-player-wrap">
					<?php the_content(); ?>
				</div>

				<div class="cp-ott-video-meta-bar">
					<?php if ( $v_type && 'standalone' !== $v_type ) : ?>
					<span class="cp-ott-badge cp-ott-badge-<?php echo esc_attr( $v_type ); ?>">
						<?php echo 'movie' === $v_type ? '🎬 Movie' : '📺 Episode'; ?>
					</span>
					<?php endif; ?>
					<?php if ( $rating ) : ?>
					<span class="cp-ott-rating"><?php echo esc_html( $rating ); ?></span>
					<?php endif; ?>
					<?php if ( $series_name ) : ?>
					<span class="cp-ott-series-crumb">
						<?php echo esc_html( $series_name ); ?>
						<?php if ( $season ) echo ' · ' . esc_html__( 'Season', 'cp-theme' ) . ' ' . esc_html( $season ); ?>
						<?php if ( $episode_num ) echo ' · ' . esc_html__( 'Episode', 'cp-theme' ) . ' ' . esc_html( $episode_num ); ?>
					</span>
					<?php endif; ?>
				</div>

				<header class="cp-ott-video-header">
					<h1 class="cp-ott-video-title"><?php the_title(); ?></h1>
				</header>

				<div class="cp-ott-video-details">
					<?php cp_theme_video_details( $post_id ); ?>
					<?php if ( is_user_logged_in() ) : ?>
					<div class="cp-video-report-actions">
						<button class="cp-button" data-cpwp-report="content" data-target-id="<?php the_ID(); ?>"><?php esc_html_e( 'Report', 'cp-theme' ); ?></button>
						<button class="cp-button" data-cpwp-report="copyright" data-target-id="<?php the_ID(); ?>"><?php esc_html_e( 'Copyright claim', 'cp-theme' ); ?></button>
					</div>
					<?php endif; ?>
				</div>

				<div class="cp-ott-video-comments">
					<?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
				</div>
			</div>

			<!-- Sidebar column: Up Next -->
			<aside class="cp-ott-upnext-col">
				<?php if ( 'episode' === $v_type && $series_name ) : ?>
					<div class="cp-ott-upnext-head">
						<h2><?php echo esc_html( $series_name ); ?></h2>
						<small><?php esc_html_e( 'Episodes', 'cp-theme' ); ?></small>
					</div>
					<?php
					$eps = get_posts( array(
						'post_type'      => 'cp_video',
						'posts_per_page' => 20,
						'orderby'        => 'date',
						'order'          => 'ASC',
						'meta_query'     => array( array( 'key' => '_cpwp_series_name', 'value' => $series_name ) ),
					) );
					foreach ( $eps as $ep ) :
						$ep_num   = get_post_meta( $ep->ID, '_cpwp_episode', true );
						$ep_thumb = get_the_post_thumbnail_url( $ep->ID, 'thumbnail' );
						$is_current = ( $ep->ID === $post_id );
					?>
					<a href="<?php echo esc_url( get_permalink( $ep ) ); ?>" class="cp-ott-episode-row <?php echo $is_current ? 'is-current' : ''; ?>">
						<div class="cp-ott-episode-thumb">
							<?php if ( $ep_thumb ) : ?>
							<img src="<?php echo esc_url( $ep_thumb ); ?>" alt="" loading="lazy">
							<?php else : ?>
							<div class="cp-ott-thumb-placeholder">▶</div>
							<?php endif; ?>
							<?php if ( $is_current ) : ?><span class="cp-ott-playing-indicator">▶</span><?php endif; ?>
						</div>
						<div class="cp-ott-episode-info">
							<?php if ( $ep_num ) : ?><span class="cp-ott-ep-num"><?php echo esc_html__( 'E', 'cp-theme' ) . esc_html( $ep_num ); ?></span><?php endif; ?>
							<strong><?php echo esc_html( get_the_title( $ep ) ); ?></strong>
							<small><?php echo esc_html( get_the_date( '', $ep ) ); ?></small>
						</div>
					</a>
					<?php endforeach; ?>

				<?php elseif ( 'movie' === $v_type ) : ?>
					<div class="cp-ott-upnext-head">
						<h2><?php esc_html_e( 'More Movies', 'cp-theme' ); ?></h2>
					</div>
					<?php
					$more = get_posts( array(
						'post_type'      => 'cp_video',
						'posts_per_page' => 6,
						'post__not_in'   => array( $post_id ),
						'meta_query'     => array( array( 'key' => '_cpwp_streaming_type', 'value' => 'movie' ) ),
						'orderby'        => 'rand',
					) );
					foreach ( $more as $m ) :
						$m_thumb = get_the_post_thumbnail_url( $m->ID, 'medium' );
					?>
					<a href="<?php echo esc_url( get_permalink( $m ) ); ?>" class="cp-ott-related-row">
						<div class="cp-ott-related-thumb">
							<?php if ( $m_thumb ) : ?><img src="<?php echo esc_url( $m_thumb ); ?>" alt="" loading="lazy"><?php endif; ?>
						</div>
						<div class="cp-ott-related-info">
							<strong><?php echo esc_html( get_the_title( $m ) ); ?></strong>
							<?php $mr = get_post_meta( $m->ID, '_cpwp_age_rating', true ); if ( $mr ) : ?>
							<span class="cp-ott-rating"><?php echo esc_html( $mr ); ?></span>
							<?php endif; ?>
						</div>
					</a>
					<?php endforeach; ?>

				<?php else : ?>
					<div class="cp-ott-upnext-head">
						<h2><?php esc_html_e( 'Up Next', 'cp-theme' ); ?></h2>
					</div>
					<?php
					$more = get_posts( array(
						'post_type'      => 'cp_video',
						'posts_per_page' => 6,
						'post__not_in'   => array( $post_id ),
						'orderby'        => 'rand',
					) );
					foreach ( $more as $m ) :
						$m_thumb = get_the_post_thumbnail_url( $m->ID, 'medium' );
					?>
					<a href="<?php echo esc_url( get_permalink( $m ) ); ?>" class="cp-ott-related-row">
						<div class="cp-ott-related-thumb">
							<?php if ( $m_thumb ) : ?><img src="<?php echo esc_url( $m_thumb ); ?>" alt="" loading="lazy"><?php endif; ?>
						</div>
						<div class="cp-ott-related-info">
							<strong><?php echo esc_html( get_the_title( $m ) ); ?></strong>
							<small><?php echo esc_html( get_the_date( '', $m ) ); ?></small>
						</div>
					</a>
					<?php endforeach; ?>
				<?php endif; ?>
			</aside>

		</article>
		<?php endwhile; ?>
	</div>
</div>

<?php get_footer(); ?>
