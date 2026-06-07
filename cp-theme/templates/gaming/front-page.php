<?php
/**
 * Template: Gaming — Homepage (Twitch style)
 * Hero stream + Top Categories (Games) + Live Channels / Recent VODs.
 */

get_header();

// Fetch featured video for hero
$featured_id   = absint( cp_theme_cp_setting( 'home_featured_video', 0 ) );
$featured_args = array( 'post_type' => 'cp_video', 'posts_per_page' => 1 );
if ( $featured_id ) {
	$featured_args['p'] = $featured_id;
} else {
	$featured_args['meta_key'] = '_cpwp_views';
	$featured_args['orderby']  = 'meta_value_num';
	$featured_args['order']    = 'DESC';
}
$featured = new WP_Query( $featured_args );
$hero_post = $featured->have_posts() ? $featured->posts[0] : null;

// Fetch top games
$games = get_terms( array(
	'taxonomy'   => 'cp_game',
	'hide_empty' => true,
	'number'     => 6,
	'orderby'    => 'count',
	'order'      => 'DESC',
) );

// Fetch recent videos (VODs / Clips)
$recent_videos = get_posts( array( 'post_type' => 'cp_video', 'posts_per_page' => 8 ) );

?>
<div class="cp-shell cp-page-layout-with-sidebar">
	<?php get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-twitch-content">
		
		<!-- Hero Carousel / Featured Stream -->
		<?php if ( $hero_post ) :
			$hero_author = get_the_author_meta( 'display_name', $hero_post->post_author );
			$hero_avatar = get_avatar_url( $hero_post->post_author );
			$hero_thumb  = get_the_post_thumbnail_url( $hero_post->ID, 'full' );
			$hero_games  = get_the_terms( $hero_post->ID, 'cp_game' );
		?>
		<section class="cp-twitch-hero">
			<div class="cp-twitch-hero-player">
				<?php if ( $hero_thumb ) : ?>
				<a href="<?php echo esc_url( get_permalink( $hero_post->ID ) ); ?>" class="cp-twitch-hero-thumb">
					<img src="<?php echo esc_url( $hero_thumb ); ?>" alt="">
					<span class="cp-twitch-live-badge"><?php esc_html_e( 'LIVE', 'cp-theme' ); ?></span>
				</a>
				<?php else : ?>
				<div class="cp-twitch-hero-placeholder">▶</div>
				<?php endif; ?>
			</div>
			<div class="cp-twitch-hero-info">
				<div class="cp-twitch-hero-author">
					<img src="<?php echo esc_url( $hero_avatar ); ?>" alt="" class="cp-twitch-avatar">
					<div>
						<a href="#" class="cp-twitch-author-name"><?php echo esc_html( $hero_author ); ?></a>
						<?php if ( ! empty( $hero_games ) ) : ?>
						<a href="<?php echo esc_url( get_term_link( $hero_games[0] ) ); ?>" class="cp-twitch-game-link"><?php echo esc_html( $hero_games[0]->name ); ?></a>
						<?php endif; ?>
					</div>
				</div>
				<h2 class="cp-twitch-hero-title">
					<a href="<?php echo esc_url( get_permalink( $hero_post->ID ) ); ?>"><?php echo esc_html( $hero_post->post_title ); ?></a>
				</h2>
				<div class="cp-twitch-tags">
					<span class="cp-twitch-tag">English</span>
					<span class="cp-twitch-tag">Esports</span>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_hero' ); ?>

		<!-- Categories (Games) -->
		<?php if ( ! empty( $games ) && ! is_wp_error( $games ) ) : ?>
		<section class="cp-twitch-section">
			<h2 class="cp-twitch-section-title">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>"><?php esc_html_e( 'Categories', 'cp-theme' ); ?></a> <?php esc_html_e( 'we think you\'ll like', 'cp-theme' ); ?>
			</h2>
			<div class="cp-twitch-category-grid">
				<?php foreach ( $games as $game ) :
					// Try to find a custom image, otherwise placeholder
					$term_img = get_term_meta( $game->term_id, 'image', true ) ?: '';
				?>
				<a href="<?php echo esc_url( get_term_link( $game ) ); ?>" class="cp-twitch-category-card">
					<div class="cp-twitch-category-box">
						<?php if ( $term_img ) : ?>
						<img src="<?php echo esc_url( $term_img ); ?>" alt="">
						<?php else : ?>
						<div class="cp-twitch-category-placeholder">🎮</div>
						<?php endif; ?>
					</div>
					<h3 class="cp-twitch-category-name"><?php echo esc_html( $game->name ); ?></h3>
					<p class="cp-twitch-category-viewers"><?php echo number_format_i18n( rand( 1000, 50000 ) ); ?> <?php esc_html_e( 'viewers', 'cp-theme' ); ?></p>
					<div class="cp-twitch-tags"><span class="cp-twitch-tag">Action</span></div>
				</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<!-- Live Channels / Recent Videos -->
		<?php if ( $recent_videos ) : ?>
		<section class="cp-twitch-section">
			<h2 class="cp-twitch-section-title">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>"><?php esc_html_e( 'Live channels', 'cp-theme' ); ?></a> <?php esc_html_e( 'we think you\'ll like', 'cp-theme' ); ?>
			</h2>
			<div class="cp-twitch-video-grid">
				<?php foreach ( $recent_videos as $vid ) :
					$author = get_the_author_meta( 'display_name', $vid->post_author );
					$avatar = get_avatar_url( $vid->post_author );
					$thumb  = get_the_post_thumbnail_url( $vid->ID, 'medium_large' );
					$vgames = get_the_terms( $vid->ID, 'cp_game' );
					$views  = absint( get_post_meta( $vid->ID, '_cpwp_views', true ) );
				?>
				<article class="cp-twitch-video-card">
					<a href="<?php echo esc_url( get_permalink( $vid->ID ) ); ?>" class="cp-twitch-video-thumb">
						<?php if ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt=""><?php endif; ?>
						<span class="cp-twitch-live-badge"><?php esc_html_e( 'LIVE', 'cp-theme' ); ?></span>
						<span class="cp-twitch-viewers-badge"><?php echo number_format_i18n( $views ?: rand(100,5000) ); ?> <?php esc_html_e( 'viewers', 'cp-theme' ); ?></span>
					</a>
					<div class="cp-twitch-video-info">
						<img src="<?php echo esc_url( $avatar ); ?>" alt="" class="cp-twitch-avatar">
						<div class="cp-twitch-video-meta">
							<h3 class="cp-twitch-video-title"><a href="<?php echo esc_url( get_permalink( $vid->ID ) ); ?>"><?php echo esc_html( $vid->post_title ); ?></a></h3>
							<p class="cp-twitch-video-author"><?php echo esc_html( $author ); ?></p>
							<?php if ( ! empty( $vgames ) ) : ?>
							<a href="<?php echo esc_url( get_term_link( $vgames[0] ) ); ?>" class="cp-twitch-game-link"><?php echo esc_html( $vgames[0]->name ); ?></a>
							<?php endif; ?>
							<div class="cp-twitch-tags"><span class="cp-twitch-tag">English</span></div>
						</div>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_grid' ); ?>

	</div>
</div>

<?php get_footer(); ?>
