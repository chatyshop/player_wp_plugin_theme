<?php
/**
 * Template: Podcast — Homepage (Spotify style)
 * Show artwork grid + latest episodes list.
 */
get_header();

// Fetch Latest Episodes (cp_video)
$latest_episodes = get_posts( array(
	'post_type'      => 'cp_video',
	'posts_per_page' => 10,
	'post_status'    => 'publish',
) );

// Fetch Featured Shows (cp_series)
$shows = get_posts( array(
	'post_type'      => 'cp_series',
	'posts_per_page' => 6,
	'post_status'    => 'publish',
) );

?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-podcast-content">

		<!-- Welcome Header -->
		<header class="cp-podcast-welcome">
			<?php
			$hour = current_time( 'H' );
			if ( $hour < 12 ) $greeting = __( 'Good morning', 'cp-theme' );
			elseif ( $hour < 18 ) $greeting = __( 'Good afternoon', 'cp-theme' );
			else $greeting = __( 'Good evening', 'cp-theme' );
			?>
			<h1><?php echo esc_html( $greeting ); ?></h1>
		</header>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_hero' ); ?>

		<!-- Featured Shows -->
		<?php if ( $shows ) : ?>
		<section class="cp-podcast-section">
			<div class="cp-podcast-section-head">
				<h2><?php esc_html_e( 'Popular Shows', 'cp-theme' ); ?></h2>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_series' ) ); ?>" class="cp-podcast-see-all"><?php esc_html_e( 'Show all', 'cp-theme' ); ?></a>
			</div>
			<div class="cp-podcast-show-grid">
				<?php foreach ( $shows as $show ) :
					$sthumb = get_the_post_thumbnail_url( $show->ID, 'medium_large' );
					$author = get_the_author_meta( 'display_name', $show->post_author );
				?>
				<a href="<?php echo esc_url( get_permalink( $show->ID ) ); ?>" class="cp-podcast-show-card">
					<div class="cp-podcast-show-thumb">
						<?php if ( $sthumb ) : ?>
						<img src="<?php echo esc_url( $sthumb ); ?>" alt="" loading="lazy">
						<?php else : ?>
						<div class="cp-podcast-placeholder">🎙️</div>
						<?php endif; ?>
					</div>
					<div class="cp-podcast-show-info">
						<h3><?php echo esc_html( $show->post_title ); ?></h3>
						<p><?php echo esc_html( $author ); ?></p>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<!-- Latest Episodes -->
		<?php if ( $latest_episodes ) : ?>
		<section class="cp-podcast-section">
			<div class="cp-podcast-section-head">
				<h2><?php esc_html_e( 'New Episodes', 'cp-theme' ); ?></h2>
			</div>
			<div class="cp-podcast-ep-list">
				<?php foreach ( $latest_episodes as $ep ) :
					$ethumb  = get_the_post_thumbnail_url( $ep->ID, 'thumbnail' );
					$series_name = get_post_meta( $ep->ID, '_cpwp_series_name', true );
					$parent_obj  = $series_name ? get_page_by_title( $series_name, OBJECT, 'cp_series' ) : null;
					$parent      = $parent_obj ? $parent_obj->ID : 0;
					$show_name = $parent ? get_the_title( $parent ) : get_the_author_meta( 'display_name', $ep->post_author );
					$length  = get_post_meta( $ep->ID, '_cpwp_duration', true ) ?: '45 min';
					$date    = get_the_date( 'M j', $ep->ID );
				?>
				<div class="cp-podcast-ep-row">
					<a href="<?php echo esc_url( get_permalink( $ep->ID ) ); ?>" class="cp-podcast-ep-thumb">
						<?php if ( $ethumb ) : ?>
						<img src="<?php echo esc_url( $ethumb ); ?>" alt="" loading="lazy">
						<?php else : ?>
						<div class="cp-podcast-placeholder-sm">▶</div>
						<?php endif; ?>
					</a>
					<div class="cp-podcast-ep-info">
						<h4><a href="<?php echo esc_url( get_permalink( $ep->ID ) ); ?>"><?php echo esc_html( $ep->post_title ); ?></a></h4>
						<span class="cp-podcast-ep-show"><?php echo esc_html( $show_name ); ?></span>
					</div>
					<div class="cp-podcast-ep-meta">
						<span class="cp-podcast-ep-date"><?php echo esc_html( $date ); ?></span>
						<span class="cp-podcast-ep-length"><?php echo esc_html( $length ); ?></span>
					</div>
					<div class="cp-podcast-ep-actions">
						<a href="<?php echo esc_url( get_permalink( $ep->ID ) ); ?>" class="cp-podcast-play-btn" title="<?php esc_attr_e( 'Play', 'cp-theme' ); ?>">
							<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
						</a>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_grid' ); ?>

	</div>
</div>
<?php get_footer(); ?>
