<?php
/**
 * Template: Streaming — Homepage
 * Netflix/Prime-style OTT homepage with hero banner,
 * Featured Series, Trending Movies, and Latest TV Episodes rows.
 */

// Hero: prefer featured setting, fallback to a recent movie.
$featured_id   = absint( cp_theme_cp_setting( 'home_featured_video', 0 ) );
$featured_args = array( 'post_type' => 'cp_video', 'posts_per_page' => 1 );
if ( $featured_id ) {
	$featured_args['p'] = $featured_id;
} else {
	$featured_args['meta_key'] = '_cpwp_views';
	$featured_args['orderby']  = 'meta_value_num';
	$featured_args['order']    = 'DESC';
}
$featured         = new WP_Query( $featured_args );
$hero_title       = '';
$hero_description = '';
$hero_permalink   = '';
$hero_thumbnail   = '';
$hero_type        = '';
$hero_rating      = '';
if ( $featured->have_posts() ) {
	$featured->the_post();
	$fid              = get_the_ID();
	$hero_title       = cp_theme_cp_setting( 'home_hero_title' ) ?: get_the_title();
	$hero_description = cp_theme_cp_setting( 'home_hero_description' ) ?: wp_trim_words( get_the_excerpt() ?: get_the_content(), 30 );
	$hero_permalink   = get_permalink();
	$hero_type        = get_post_meta( $fid, '_cpwp_streaming_type', true );
	$hero_rating      = get_post_meta( $fid, '_cpwp_age_rating', true );
	$hero_series      = get_post_meta( $fid, '_cpwp_series_name', true );
	ob_start();
	the_post_thumbnail( 'full', array( 'loading' => 'eager' ) );
	$hero_thumbnail = ob_get_clean();
	wp_reset_postdata();
}

get_header();
?>

<?php if ( is_user_logged_in() ) : ?>
<div class="cp-shell cp-page-layout-with-sidebar">
	<?php get_template_part( 'sidebar', 'logged-in' ); ?>
	<div class="cp-page-content cp-streaming-content">
<?php else : ?>
<div class="cp-shell">
	<div class="cp-page-content cp-streaming-content">
<?php endif; ?>

		<?php if ( $hero_permalink ) : ?>
		<section class="cp-ott-hero">
			<div class="cp-ott-hero-backdrop"><?php echo $hero_thumbnail; ?></div>
			<div class="cp-ott-hero-gradient"></div>
			<div class="cp-ott-hero-content">
				<?php if ( $hero_type ) : ?>
				<span class="cp-ott-badge cp-ott-badge-<?php echo esc_attr( $hero_type ); ?>">
					<?php echo 'movie' === $hero_type ? '🎬 Movie' : ( 'episode' === $hero_type ? '📺 TV Episode' : ucfirst( $hero_type ) ); ?>
				</span>
				<?php endif; ?>
				<h1 class="cp-ott-hero-title"><?php echo esc_html( $hero_title ); ?></h1>
				<?php if ( $hero_rating ) : ?>
				<span class="cp-ott-rating"><?php echo esc_html( $hero_rating ); ?></span>
				<?php endif; ?>
				<p class="cp-ott-hero-desc"><?php echo esc_html( $hero_description ); ?></p>
				<div class="cp-ott-hero-actions">
					<a class="cp-ott-play-btn" href="<?php echo esc_url( $hero_permalink ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="5,3 19,12 5,21"/></svg>
						<?php esc_html_e( 'Play Now', 'cp-theme' ); ?>
					</a>
					<?php if ( is_user_logged_in() && class_exists( 'CPWP_Assets' ) ) : ?>
					<button class="cp-ott-info-btn" data-cpwp-watchlater="<?php the_ID(); ?>" aria-label="<?php esc_attr_e( 'Add to My List', 'cp-theme' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
						<?php esc_html_e( 'My List', 'cp-theme' ); ?>
					</button>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_hero' ); ?>

		<?php
		// Featured Series row.
		$series_items = get_posts( array( 'post_type' => 'cp_series', 'posts_per_page' => 8, 'post_status' => 'publish' ) );
		if ( $series_items ) : ?>
		<section class="cp-ott-row">
			<div class="cp-ott-row-head">
				<h2 class="cp-ott-row-title"><?php esc_html_e( 'Featured Series', 'cp-theme' ); ?></h2>
			</div>
			<div class="cp-ott-poster-grid cp-ott-poster-grid--series">
				<?php foreach ( $series_items as $item ) : ?>
				<article class="cp-ott-poster">
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>" class="cp-ott-poster-link">
						<?php if ( has_post_thumbnail( $item ) ) : ?>
						<?php echo get_the_post_thumbnail( $item, 'medium_large', array( 'loading' => 'lazy' ) ); ?>
						<?php else : ?>
						<div class="cp-ott-poster-placeholder"><span><?php echo esc_html( get_the_title( $item ) ); ?></span></div>
						<?php endif; ?>
						<div class="cp-ott-poster-overlay">
							<h3 class="cp-ott-poster-title"><?php echo esc_html( get_the_title( $item ) ); ?></h3>
							<span class="cp-ott-badge">Series</span>
						</div>
					</a>
				</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php
		// Trending Movies row.
		$movies = get_posts( array(
			'post_type'      => 'cp_video',
			'posts_per_page' => 8,
			'meta_key'       => '_cpwp_views',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'meta_query'     => array( array( 'key' => '_cpwp_streaming_type', 'value' => 'movie' ) ),
		) );
		$movies_url = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'movies' ) : get_post_type_archive_link( 'cp_video' );
		if ( $movies ) : ?>
		<section class="cp-ott-row">
			<div class="cp-ott-row-head">
				<h2 class="cp-ott-row-title"><?php esc_html_e( 'Trending Movies', 'cp-theme' ); ?></h2>
				<a class="cp-ott-row-link" href="<?php echo esc_url( $movies_url ); ?>"><?php esc_html_e( 'See all', 'cp-theme' ); ?> →</a>
			</div>
			<div class="cp-ott-poster-grid">
				<?php foreach ( $movies as $movie ) : ?>
				<article class="cp-ott-poster">
					<a href="<?php echo esc_url( get_permalink( $movie ) ); ?>" class="cp-ott-poster-link">
						<?php $thumb = get_the_post_thumbnail( $movie, 'medium_large', array( 'loading' => 'lazy' ) ); ?>
						<?php if ( $thumb ) : echo $thumb; else : ?>
						<div class="cp-ott-poster-placeholder"><span><?php echo esc_html( get_the_title( $movie ) ); ?></span></div>
						<?php endif; ?>
						<div class="cp-ott-poster-overlay">
							<h3 class="cp-ott-poster-title"><?php echo esc_html( get_the_title( $movie ) ); ?></h3>
							<?php $rating = get_post_meta( $movie->ID, '_cpwp_age_rating', true ); if ( $rating ) : ?>
							<span class="cp-ott-rating"><?php echo esc_html( $rating ); ?></span>
							<?php endif; ?>
							<span class="cp-ott-badge cp-ott-badge-movie">🎬 Movie</span>
						</div>
					</a>
				</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php
		// Latest TV Episodes row.
		$episodes = get_posts( array(
			'post_type'      => 'cp_video',
			'posts_per_page' => 8,
			'meta_query'     => array( array( 'key' => '_cpwp_streaming_type', 'value' => 'episode' ) ),
		) );
		$episodes_url = class_exists( 'CPWP_Page_Suites' ) ? CPWP_Page_Suites::url( 'tv-shows' ) : get_post_type_archive_link( 'cp_video' );
		if ( $episodes ) : ?>
		<section class="cp-ott-row">
			<div class="cp-ott-row-head">
				<h2 class="cp-ott-row-title"><?php esc_html_e( 'Latest TV Episodes', 'cp-theme' ); ?></h2>
				<a class="cp-ott-row-link" href="<?php echo esc_url( $episodes_url ); ?>"><?php esc_html_e( 'See all', 'cp-theme' ); ?> →</a>
			</div>
			<div class="cp-ott-poster-grid">
				<?php foreach ( $episodes as $ep ) : ?>
				<article class="cp-ott-poster">
					<a href="<?php echo esc_url( get_permalink( $ep ) ); ?>" class="cp-ott-poster-link">
						<?php $thumb = get_the_post_thumbnail( $ep, 'medium_large', array( 'loading' => 'lazy' ) ); ?>
						<?php if ( $thumb ) : echo $thumb; else : ?>
						<div class="cp-ott-poster-placeholder"><span><?php echo esc_html( get_the_title( $ep ) ); ?></span></div>
						<?php endif; ?>
						<div class="cp-ott-poster-overlay">
							<h3 class="cp-ott-poster-title"><?php echo esc_html( get_the_title( $ep ) ); ?></h3>
							<?php $series = get_post_meta( $ep->ID, '_cpwp_series_name', true ); if ( $series ) : ?>
							<small class="cp-ott-series-label"><?php echo esc_html( $series ); ?></small>
							<?php endif; ?>
							<span class="cp-ott-badge cp-ott-badge-episode">📺 Episode</span>
						</div>
					</a>
				</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_grid' ); ?>

	</div><!-- .cp-page-content -->
</div><!-- .cp-shell -->

<?php get_footer(); ?>
