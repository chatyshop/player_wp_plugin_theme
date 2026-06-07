<?php
/**
 * Template: Gaming — Single Event (Tournaments)
 * Event banner, schedule, tournament videos / highlights.
 */
get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-twitch-content">
		<?php while ( have_posts() ) : the_post();
			$event_id = get_the_ID();
			$thumb    = get_the_post_thumbnail_url( $event_id, 'full' );
			$games    = get_the_terms( $event_id, 'cp_game' );

			// Videos related to this event
			// For Twitch-style, events often just group videos using a taxonomy or parent.
			// Let's assume CPWP_Site_Modules::children() or just querying videos with event meta
			$event_videos = class_exists( 'CPWP_Site_Modules' ) ? CPWP_Site_Modules::children( $event_id, array( 'cp_video' ) ) : array();
		?>
		
		<!-- Event Hero -->
		<section class="cp-twitch-event-hero">
			<?php if ( $thumb ) : ?>
			<div class="cp-twitch-event-banner">
				<img src="<?php echo esc_url( $thumb ); ?>" alt="">
				<div class="cp-twitch-event-overlay"></div>
			</div>
			<?php endif; ?>
			<div class="cp-twitch-event-header">
				<h1 class="cp-twitch-event-title"><?php the_title(); ?></h1>
				<?php if ( ! empty( $games ) ) : ?>
				<div class="cp-twitch-event-meta">
					<a href="<?php echo esc_url( get_term_link( $games[0] ) ); ?>" class="cp-twitch-game-link"><?php echo esc_html( $games[0]->name ); ?></a>
				</div>
				<?php endif; ?>
				<div class="cp-twitch-event-desc">
					<?php the_content(); ?>
				</div>
				<div class="cp-twitch-event-actions">
					<button class="cp-twitch-btn cp-twitch-btn-follow">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
						<?php esc_html_e( 'Follow Event', 'cp-theme' ); ?>
					</button>
				</div>
			</div>
		</section>

		<!-- Event Videos / Highlights -->
		<section class="cp-twitch-section">
			<h2 class="cp-twitch-section-title"><?php esc_html_e( 'Highlights & VODs', 'cp-theme' ); ?></h2>
			
			<?php if ( $event_videos ) : ?>
			<div class="cp-twitch-video-grid">
				<?php foreach ( $event_videos as $vid ) :
					$author = get_the_author_meta( 'display_name', $vid->post_author );
					$avatar = get_avatar_url( $vid->post_author );
					$vthumb = get_the_post_thumbnail_url( $vid->ID, 'medium_large' );
					$views  = absint( get_post_meta( $vid->ID, '_cpwp_views', true ) );
				?>
				<article class="cp-twitch-video-card">
					<a href="<?php echo esc_url( get_permalink( $vid->ID ) ); ?>" class="cp-twitch-video-thumb">
						<?php if ( $vthumb ) : ?><img src="<?php echo esc_url( $vthumb ); ?>" alt="" loading="lazy"><?php endif; ?>
						<span class="cp-twitch-viewers-badge"><?php echo number_format_i18n( $views ?: rand(100,5000) ); ?> <?php esc_html_e( 'views', 'cp-theme' ); ?></span>
					</a>
					<div class="cp-twitch-video-info">
						<img src="<?php echo esc_url( $avatar ); ?>" alt="" class="cp-twitch-avatar">
						<div class="cp-twitch-video-meta">
							<h3 class="cp-twitch-video-title"><a href="<?php echo esc_url( get_permalink( $vid->ID ) ); ?>"><?php echo esc_html( $vid->post_title ); ?></a></h3>
							<p class="cp-twitch-video-author"><?php echo esc_html( $author ); ?></p>
						</div>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
			<?php else : ?>
			<div class="cp-twitch-empty">
				<p><?php esc_html_e( 'No videos available for this event yet.', 'cp-theme' ); ?></p>
			</div>
			<?php endif; ?>
		</section>

		<?php endwhile; ?>
	</div>
</div>

<?php get_footer(); ?>
