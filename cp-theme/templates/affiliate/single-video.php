<?php
/**
 * Template: Affiliate / Deal Site — Single Video
 * Video player with sidebar for linked affiliate products.
 */
get_header();

$author_id = get_post_field( 'post_author', get_the_ID() );
$author_name = get_the_author_meta( 'display_name', $author_id );
$affiliate_url = get_post_meta( get_the_ID(), '_cpwp_affiliate_url', true );
?>
<main class="ca-main ca-single-video-layout">
	
	<div class="ca-container ca-video-grid">
		<!-- Main Content -->
		<div class="ca-video-main">
			<div class="ca-player-wrapper">
				<?php 
				if ( class_exists( 'CPWP_Assets' ) ) {
					echo do_shortcode('[cp_player]');
				} else {
					echo '<div class="ca-player-placeholder"><p>Player Placeholder</p></div>';
				}
				?>
			</div>
			
			<div class="ca-video-header">
				<h1 class="ca-video-title"><?php the_title(); ?></h1>
				<div class="ca-video-meta">
					<div class="ca-video-author">
						<?php echo get_avatar( $author_id, 40 ); ?>
						<span>By <a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( $author_name ); ?></a></span>
					</div>
					<div class="ca-video-date"><?php echo esc_html( get_the_date() ); ?></div>
				</div>
			</div>

			<div class="ca-video-content">
				<?php the_content(); ?>
				
				<?php if ( $affiliate_url ) : ?>
					<div class="ca-inline-affiliate-cta">
						<a href="<?php echo esc_url( $affiliate_url ); ?>" target="_blank" rel="nofollow sponsored" class="ca-btn ca-btn--primary ca-btn--lg">Buy Now / View Offer</a>
					</div>
				<?php endif; ?>
			</div>

			<!-- Comments -->
			<?php if ( comments_open() || get_comments_number() ) : ?>
				<div class="ca-comments-section">
					<?php comments_template(); ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- Sidebar -->
		<aside class="ca-video-sidebar">
			<?php if ( $affiliate_url ) : ?>
				<div class="ca-sidebar-widget ca-widget-deal">
					<h3>Featured in this video</h3>
					<div class="ca-widget-deal__card">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium', array( 'class' => 'ca-widget-deal__thumb' ) ); ?>
						<?php endif; ?>
						<a href="<?php echo esc_url( $affiliate_url ); ?>" target="_blank" rel="nofollow sponsored" class="ca-btn ca-btn--primary ca-btn--full">Get it here</a>
						<p class="ca-disclosure-text">We may earn a commission if you purchase through this link.</p>
					</div>
				</div>
			<?php endif; ?>

			<div class="ca-sidebar-widget">
				<h3>More Reviews</h3>
				<div class="ca-up-next-list">
					<?php
					$more_videos = get_posts( array(
						'post_type'      => 'cp_video',
						'post__not_in'   => array( get_the_ID() ),
						'posts_per_page' => 4
					) );
					foreach ( $more_videos as $mv ) :
					?>
						<a href="<?php echo esc_url( get_permalink( $mv->ID ) ); ?>" class="ca-up-next-item">
							<div class="ca-up-next-item__thumb">
								<?php echo get_the_post_thumbnail( $mv->ID, 'thumbnail' ); ?>
							</div>
							<div class="ca-up-next-item__info">
								<h4><?php echo esc_html( get_the_title( $mv->ID ) ); ?></h4>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</aside>
	</div>

</main>
<?php get_footer(); ?>
