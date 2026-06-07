<?php
/**
 * Template: Podcast — Single Video/Episode
 * Spotify-style audio/video episode player.
 */
get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-podcast-content">
		<?php while ( have_posts() ) : the_post();
			$post_id   = get_the_ID();
			$series_name = get_post_meta( $post_id, '_cpwp_series_name', true );
			$parent_obj  = $series_name ? get_page_by_title( $series_name, OBJECT, 'cp_series' ) : null;
			$parent_id   = $parent_obj ? $parent_obj->ID : 0;
			$thumb     = get_the_post_thumbnail_url( $post_id, 'full' );
			$date      = get_the_date( 'M j, Y' );
			$duration  = get_post_meta( $post_id, '_cpwp_duration', true ) ?: '45 min 12 sec';
			$guests    = get_the_terms( $post_id, 'cp_person' );
			
			// Show details
			$show_name = $parent_id ? get_the_title( $parent_id ) : get_the_author_meta( 'display_name', $post->post_author );
			$show_link = $parent_id ? get_permalink( $parent_id ) : '#';
			if ( ! $thumb && $parent_id ) {
				$thumb = get_the_post_thumbnail_url( $parent_id, 'full' );
			}
		?>
		
		<div class="cp-podcast-ep-hero">
			<div class="cp-podcast-ep-art">
				<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="">
				<?php else : ?>
				<div class="cp-podcast-art-placeholder">🎧</div>
				<?php endif; ?>
			</div>
			<div class="cp-podcast-ep-hero-info">
				<span class="cp-podcast-ep-label"><?php esc_html_e( 'Podcast Episode', 'cp-theme' ); ?></span>
				<h1 class="cp-podcast-ep-title"><?php the_title(); ?></h1>
				<h2 class="cp-podcast-ep-show-name"><a href="<?php echo esc_url( $show_link ); ?>"><?php echo esc_html( $show_name ); ?></a></h2>
			</div>
		</div>

		<div class="cp-podcast-ep-actions-bar">
			<button class="cp-podcast-btn-play-large">
				<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
			</button>
			<button class="cp-podcast-btn-icon" title="<?php esc_attr_e('Save to library', 'cp-theme'); ?>">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
			</button>
			<button class="cp-podcast-btn-icon" title="<?php esc_attr_e('More options', 'cp-theme'); ?>">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/><circle cx="5" cy="12" r="2"/></svg>
			</button>
		</div>

		<div class="cp-podcast-ep-layout">
			<div class="cp-podcast-ep-main">
				
				<div class="cp-podcast-player-box">
					<?php the_content(); ?>
				</div>

				<div class="cp-podcast-ep-desc">
					<h3><?php esc_html_e( 'Episode Description', 'cp-theme' ); ?></h3>
					<div class="cp-podcast-desc-meta">
						<span><?php echo esc_html( $date ); ?></span>
						<span class="cp-podcast-meta-dot">·</span>
						<span><?php echo esc_html( $duration ); ?></span>
					</div>
					
					<?php 
					$genres = get_the_terms( $post_id, 'cp_genre' ) ?: array();
					$topics = get_the_terms( $post_id, 'cp_topic' ) ?: array();
					if ( ! empty( $genres ) && ! is_wp_error( $genres ) || ! empty( $topics ) && ! is_wp_error( $topics ) ) :
					?>
					<div class="cp-podcast-taxonomies" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; margin-bottom:14px;">
						<?php if ( ! empty( $genres ) && ! is_wp_error( $genres ) ) : foreach ( $genres as $genre ) : ?>
							<a href="<?php echo esc_url( get_term_link( $genre ) ); ?>" class="cp-podcast-genre-pill" style="background: var(--cp-soft); color: var(--cp-accent); border: 1px solid var(--cp-line); padding: 4px 12px; border-radius: 999px; text-decoration: none; font-size: 0.8rem; font-weight: 600;">
								<?php echo esc_html( $genre->name ); ?>
							</a>
						<?php endforeach; endif; ?>
						<?php if ( ! empty( $topics ) && ! is_wp_error( $topics ) ) : foreach ( $topics as $topic ) : ?>
							<a href="<?php echo esc_url( get_term_link( $topic ) ); ?>" class="cp-podcast-topic-pill" style="background: var(--cp-soft); color: var(--cp-accent); border: 1px solid var(--cp-line); padding: 4px 12px; border-radius: 999px; text-decoration: none; font-size: 0.8rem; font-weight: 600;">
								<?php echo esc_html( $topic->name ); ?>
							</a>
						<?php endforeach; endif; ?>
					</div>
					<?php endif; ?>

					<div class="cp-podcast-desc-text">
						<?php cp_theme_video_details( $post_id ); ?>
					</div>
				</div>

				<?php if ( ! empty( $guests ) ) : ?>
				<div class="cp-podcast-guests">
					<h3><?php esc_html_e( 'Featuring', 'cp-theme' ); ?></h3>
					<div class="cp-podcast-guest-list">
						<?php foreach ( $guests as $guest ) : ?>
						<a href="<?php echo esc_url( get_term_link( $guest ) ); ?>" class="cp-podcast-guest-pill">
							<?php echo esc_html( $guest->name ); ?>
						</a>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

			</div>

			<!-- More from this show -->
			<?php if ( $parent_id ) : 
				$more_eps = class_exists('CPWP_Site_Modules') ? CPWP_Site_Modules::children( $parent_id, array('cp_video') ) : array();
				if ( ! empty( $more_eps ) ) :
			?>
			<aside class="cp-podcast-ep-aside">
				<h3><?php esc_html_e( 'More from', 'cp-theme' ); ?> <?php echo esc_html( $show_name ); ?></h3>
				<div class="cp-podcast-aside-list">
					<?php foreach ( array_slice( $more_eps, 0, 5 ) as $mep ) : 
						if ( $mep->ID === $post_id ) continue;
					?>
					<a href="<?php echo esc_url( get_permalink( $mep->ID ) ); ?>" class="cp-podcast-aside-item">
						<div class="cp-podcast-ai-info">
							<h4><?php echo esc_html( $mep->post_title ); ?></h4>
							<span><?php echo get_the_date( 'M j', $mep->ID ); ?></span>
						</div>
					</a>
					<?php endforeach; ?>
				</div>
				<a href="<?php echo esc_url( $show_link ); ?>" class="cp-podcast-btn-outline"><?php esc_html_e( 'See all episodes', 'cp-theme' ); ?></a>
			</aside>
			<?php endif; endif; ?>
		</div>

		<?php endwhile; ?>
	</div>
</div>
<?php get_footer(); ?>
