<?php
/**
 * Template: Video Library — Single Video
 * Vimeo-style wide theater player, sleek sidebar info.
 */
get_header();

$author_id = get_post_field( 'post_author', get_the_ID() );
$author_name = get_the_author_meta( 'display_name', $author_id );
$views = absint( get_post_meta( get_the_ID(), '_cpwp_views', true ) );
$badge = get_post_meta( get_the_ID(), '_cpwp_badge', true );

// Ensure views are updated if user is watching (normally handled via AJAX or PHP snippet, we'll just bump it here if no caching is assumed)
// update_post_meta( get_the_ID(), '_cpwp_views', $views + 1 );
?>
<div class="cvl-single">
	
	<!-- Player Banner -->
	<section class="cvl-player-section">
		<div class="cvl-player-container">
			<?php 
			// Display the player if class exists, else a placeholder
			if ( class_exists( 'CPWP_Assets' ) ) {
				echo do_shortcode('[cp_player]');
			} else {
				echo '<div class="cvl-player-placeholder"><p>Player Placeholder</p></div>';
			}
			?>
		</div>
	</section>

	<!-- Video Info -->
	<section class="cvl-info-section">
		<div class="cvl-container cvl-info-layout">
			
			<div class="cvl-info-main">
				<div class="cvl-info-header">
					<?php if ( $badge ) : ?>
						<span class="cvl-badge cvl-badge--featured"><?php echo esc_html( $badge ); ?></span>
					<?php endif; ?>
					<h1 class="cvl-video-title"><?php the_title(); ?></h1>
					
					<div class="cvl-video-meta">
						<div class="cvl-author">
							<?php echo get_avatar( $author_id, 40, '', '', array( 'class' => 'cvl-author__avatar' ) ); ?>
							<div class="cvl-author__details">
								<strong><a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( $author_name ); ?></a></strong>
								<?php if ( class_exists( 'CPWP_Community' ) ) : ?>
									<button class="cvl-btn cvl-btn--sm cvl-btn--follow" data-cpwp-follow="<?php echo esc_attr( $author_id ); ?>">Follow</button>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>

				<div class="cvl-video-description">
					<?php the_content(); ?>
				</div>

				<!-- Comments -->
				<?php if ( comments_open() || get_comments_number() ) : ?>
					<div class="cvl-comments-area">
						<?php comments_template(); ?>
					</div>
				<?php endif; ?>
			</div>

			<aside class="cvl-info-sidebar">
				<div class="cvl-sidebar-stats">
					<div class="cvl-stat">
						<span class="cvl-stat__val"><?php echo number_format_i18n( $views ); ?></span>
						<span class="cvl-stat__label">Plays</span>
					</div>
					<div class="cvl-stat">
						<span class="cvl-stat__val"><?php echo number_format_i18n( get_comments_number() ); ?></span>
						<span class="cvl-stat__label">Comments</span>
					</div>
				</div>
				
				<?php 
				$download_url = get_post_meta( get_the_ID(), '_cpwp_download_url', true );
				if ( $download_url ) : ?>
					<div class="cvl-sidebar-actions">
						<a href="<?php echo esc_url( $download_url ); ?>" class="cvl-btn cvl-btn--outline cvl-btn--full" download>Download Original</a>
					</div>
				<?php endif; ?>

				<div class="cvl-up-next">
					<h3>More from <?php echo esc_html( $author_name ); ?></h3>
					<div class="cvl-up-next-list">
						<?php
						$more_videos = get_posts( array(
							'post_type'      => 'cp_video',
							'author'         => $author_id,
							'post__not_in'   => array( get_the_ID() ),
							'posts_per_page' => 3
						) );

						if ( $more_videos ) :
							foreach ( $more_videos as $mv ) :
								$mv_thumb = get_the_post_thumbnail_url( $mv->ID, 'medium' );
							?>
							<a href="<?php echo esc_url( get_permalink( $mv->ID ) ); ?>" class="cvl-next-item">
								<div class="cvl-next-item__thumb" <?php if ( $mv_thumb ) echo 'style="background-image:url(' . esc_url( $mv_thumb ) . ')"'; ?>></div>
								<div class="cvl-next-item__info">
									<h4><?php echo esc_html( get_the_title( $mv->ID ) ); ?></h4>
									<p><?php echo esc_html( get_the_date( '', $mv->ID ) ); ?></p>
								</div>
							</a>
						<?php 
							endforeach;
						else :
							echo '<p class="cvl-empty-small">No other videos.</p>';
						endif; 
						?>
					</div>
				</div>
			</aside>
			
		</div>
	</section>
</div>
<?php get_footer(); ?>
