<?php
/**
 * Template: Streaming — Series Detail Page
 * Shows poster, description, season tabs, and all episodes grouped by season.
 */
get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-streaming-content">
		<?php while ( have_posts() ) : the_post();
			$series_id    = get_the_ID();
			$series_title = get_the_title();
			$series_desc  = get_the_content();
			$thumb_url    = get_the_post_thumbnail_url( $series_id, 'large' );

			// Get all episodes for this series by name.
			$episodes = get_posts( array(
				'post_type'      => 'cp_video',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'ASC',
				'meta_query'     => array( array( 'key' => '_cpwp_series_name', 'value' => $series_title ) ),
			) );

			// Group by season.
			$seasons = array();
			foreach ( $episodes as $ep ) {
				$s = get_post_meta( $ep->ID, '_cpwp_season', true ) ?: '1';
				$seasons[ $s ][] = $ep;
			}
			ksort( $seasons );
			$active_season = isset( $_GET['season'] ) ? sanitize_key( $_GET['season'] ) : array_key_first( $seasons );
		?>

		<!-- Series Hero -->
		<section class="cp-ott-series-hero">
			<?php if ( $thumb_url ) : ?>
			<div class="cp-ott-series-backdrop" style="background-image:url('<?php echo esc_url( $thumb_url ); ?>');"></div>
			<div class="cp-ott-hero-gradient"></div>
			<?php endif; ?>
			<div class="cp-ott-series-hero-content">
				<?php if ( $thumb_url ) : ?>
				<img class="cp-ott-series-poster" src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $series_title ); ?>">
				<?php endif; ?>
				<div class="cp-ott-series-info">
					<span class="cp-ott-badge">Series</span>
					<h1 class="cp-ott-series-title"><?php echo esc_html( $series_title ); ?></h1>
					<?php if ( $series_desc ) : ?>
					<p class="cp-ott-series-desc"><?php echo wp_kses_post( wpautop( $series_desc ) ); ?></p>
					<?php endif; ?>
					<?php if ( $episodes ) : ?>
					<a class="cp-ott-play-btn" href="<?php echo esc_url( get_permalink( $episodes[0] ) ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5,3 19,12 5,21"/></svg>
						<?php esc_html_e( 'Play from Start', 'cp-theme' ); ?>
					</a>
					<?php endif; ?>
					<p class="cp-ott-series-count">
						<?php printf( esc_html( _n( '%d season', '%d seasons', count( $seasons ), 'cp-theme' ) ), count( $seasons ) ); ?>
						· <?php printf( esc_html( _n( '%d episode', '%d episodes', count( $episodes ), 'cp-theme' ) ), count( $episodes ) ); ?>
					</p>
				</div>
			</div>
		</section>

		<!-- Season Tabs -->
		<?php if ( count( $seasons ) > 1 ) : ?>
		<nav class="cp-ott-season-tabs">
			<?php foreach ( $seasons as $season_num => $eps ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'season', $season_num ) ); ?>"
			   class="cp-ott-season-tab <?php echo $season_num === $active_season ? 'is-active' : ''; ?>">
				<?php printf( esc_html__( 'Season %s', 'cp-theme' ), esc_html( $season_num ) ); ?>
			</a>
			<?php endforeach; ?>
		</nav>
		<?php endif; ?>

		<!-- Episode List for Active Season -->
		<?php if ( isset( $seasons[ $active_season ] ) ) : ?>
		<section class="cp-ott-episode-list">
			<h2 class="cp-ott-season-heading">
				<?php if ( count( $seasons ) > 1 ) printf( esc_html__( 'Season %s', 'cp-theme' ), esc_html( $active_season ) );
				else esc_html_e( 'Episodes', 'cp-theme' ); ?>
			</h2>
			<?php foreach ( $seasons[ $active_season ] as $index => $ep ) :
				$ep_num   = get_post_meta( $ep->ID, '_cpwp_episode', true ) ?: ( $index + 1 );
				$ep_thumb = get_the_post_thumbnail_url( $ep->ID, 'medium' );
				$ep_views = absint( get_post_meta( $ep->ID, '_cpwp_views', true ) );
				$ep_desc  = wp_trim_words( get_post_field( 'post_excerpt', $ep->ID ) ?: get_post_field( 'post_content', $ep->ID ), 25 );
			?>
			<a href="<?php echo esc_url( get_permalink( $ep ) ); ?>" class="cp-ott-ep-card">
				<span class="cp-ott-ep-num"><?php echo esc_html( $ep_num ); ?></span>
				<div class="cp-ott-ep-thumb">
					<?php if ( $ep_thumb ) : ?>
					<img src="<?php echo esc_url( $ep_thumb ); ?>" alt="" loading="lazy">
					<?php else : ?>
					<div class="cp-ott-thumb-placeholder">▶</div>
					<?php endif; ?>
					<span class="cp-ott-play-overlay">▶</span>
				</div>
				<div class="cp-ott-ep-body">
					<strong class="cp-ott-ep-title"><?php echo esc_html( get_the_title( $ep ) ); ?></strong>
					<p class="cp-ott-ep-desc"><?php echo esc_html( $ep_desc ); ?></p>
					<small>
						<?php echo esc_html( get_the_date( '', $ep ) ); ?>
						<?php if ( $ep_views ) : ?> · <?php echo esc_html( number_format_i18n( $ep_views ) ); ?> <?php esc_html_e( 'views', 'cp-theme' ); ?><?php endif; ?>
					</small>
				</div>
			</a>
			<?php endforeach; ?>
		</section>
		<?php endif; ?>

		<?php endwhile; ?>
	</div>
</div>

<?php get_footer(); ?>
