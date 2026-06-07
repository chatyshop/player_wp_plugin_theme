<?php
/**
 * Template: Podcast — Single Series/Show
 * Show page: artwork, description, hosts, full episode list.
 */
get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-podcast-content">
		<?php while ( have_posts() ) : the_post();
			$show_id = get_the_ID();
			$thumb   = get_the_post_thumbnail_url( $show_id, 'full' );
			$author  = get_the_author_meta( 'display_name' );
			
			// Fetch episodes
			$episodes = class_exists('CPWP_Site_Modules') ? CPWP_Site_Modules::children( $show_id, array('cp_video') ) : array();
		?>
		
		<div class="cp-podcast-show-header">
			<div class="cp-podcast-show-art">
				<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="">
				<?php else : ?>
				<div class="cp-podcast-art-placeholder">🎙️</div>
				<?php endif; ?>
			</div>
			<div class="cp-podcast-show-details">
				<span class="cp-podcast-ep-label"><?php esc_html_e( 'Podcast', 'cp-theme' ); ?></span>
				<h1 class="cp-podcast-show-title"><?php the_title(); ?></h1>
				<h2 class="cp-podcast-show-author"><?php echo esc_html( $author ); ?></h2>
			</div>
		</div>

		<div class="cp-podcast-ep-actions-bar">
			<button class="cp-podcast-btn-follow"><?php esc_html_e( 'Follow', 'cp-theme' ); ?></button>
			<button class="cp-podcast-btn-icon" title="<?php esc_attr_e('More options', 'cp-theme'); ?>">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/><circle cx="5" cy="12" r="2"/></svg>
			</button>
		</div>

		<div class="cp-podcast-show-layout">
			<div class="cp-podcast-show-main">
				
				<div class="cp-podcast-about-box">
					<h3><?php esc_html_e( 'About', 'cp-theme' ); ?></h3>
					<div class="cp-podcast-about-desc">
						<?php the_content(); ?>
					</div>
				</div>

				<div class="cp-podcast-episodes-list">
					<h3><?php esc_html_e( 'All Episodes', 'cp-theme' ); ?></h3>
					<?php if ( $episodes ) : foreach ( $episodes as $ep ) : 
						$ethumb  = get_the_post_thumbnail_url( $ep->ID, 'thumbnail' );
						$length  = get_post_meta( $ep->ID, '_cpwp_duration', true ) ?: '45 min';
						$date    = get_the_date( 'M j, Y', $ep->ID );
					?>
					<div class="cp-podcast-list-item">
						<a href="<?php echo esc_url( get_permalink( $ep->ID ) ); ?>" class="cp-podcast-li-thumb">
							<?php if ( $ethumb ) : ?><img src="<?php echo esc_url( $ethumb ); ?>" alt=""><?php elseif ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt=""><?php endif; ?>
						</a>
						<div class="cp-podcast-li-info">
							<h4><a href="<?php echo esc_url( get_permalink( $ep->ID ) ); ?>"><?php echo esc_html( $ep->post_title ); ?></a></h4>
							<p class="cp-podcast-li-desc"><?php echo wp_trim_words( get_the_excerpt( $ep->ID ), 20 ); ?></p>
							<div class="cp-podcast-li-meta">
								<a href="<?php echo esc_url( get_permalink( $ep->ID ) ); ?>" class="cp-podcast-play-btn-sm">
									<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
								</a>
								<span class="cp-podcast-li-date"><?php echo esc_html( $date ); ?></span>
								<span class="cp-podcast-meta-dot">·</span>
								<span><?php echo esc_html( $length ); ?></span>
							</div>
						</div>
					</div>
					<hr class="cp-podcast-li-divider">
					<?php endforeach; else : ?>
					<p class="cp-podcast-empty"><?php esc_html_e( 'No episodes found for this show.', 'cp-theme' ); ?></p>
					<?php endif; ?>
				</div>

			</div>
		</div>

		<?php endwhile; ?>
	</div>
</div>
<?php get_footer(); ?>
