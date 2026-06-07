<?php
/**
 * Template: Affiliate / Deal Site — Homepage
 * Focuses on featured products, reviews, and latest deals.
 */
get_header();
?>
<main class="ca-main">
	
	<!-- Hero Section -->
	<section class="ca-hero">
		<div class="ca-container ca-hero-layout">
			<div class="ca-hero-main">
				<?php
				// Featured Video/Review
				$featured_review = get_posts( array(
					'post_type'      => 'cp_video',
					'posts_per_page' => 1,
					'meta_query'     => array(
						array(
							'key'     => '_cpwp_badge',
							'value'   => 'Featured',
							'compare' => 'LIKE'
						)
					)
				) );
				if ( ! $featured_review ) {
					$featured_review = get_posts( array( 'post_type' => 'cp_video', 'posts_per_page' => 1 ) );
				}
				if ( $featured_review ) {
					$post = $featured_review[0];
					setup_postdata( $post );
					$bg = get_the_post_thumbnail_url( $post->ID, 'full' );
					?>
					<a href="<?php the_permalink(); ?>" class="ca-hero-card">
						<div class="ca-hero-card__bg" <?php if ( $bg ) echo 'style="background-image:url(' . esc_url( $bg ) . ')"'; ?>></div>
						<div class="ca-hero-card__overlay"></div>
						<div class="ca-hero-card__content">
							<span class="ca-badge ca-badge--primary">Featured Review</span>
							<h2 class="ca-hero-card__title"><?php the_title(); ?></h2>
							<p class="ca-hero-card__excerpt"><?php echo wp_trim_words( get_the_excerpt(), 15 ); ?></p>
						</div>
					</a>
					<?php wp_reset_postdata();
				}
				?>
			</div>
			<div class="ca-hero-side">
				<h3 class="ca-side-title">Top Deals Today</h3>
				<div class="ca-deal-list">
					<?php
					$top_deals = get_posts( array(
						'post_type'      => 'cp_product',
						'posts_per_page' => 4
					) );
					if ( $top_deals ) :
						foreach ( $top_deals as $post ) : setup_postdata( $post );
							$price = get_post_meta( get_the_ID(), '_cpwp_affiliate_price', true );
							$merchant = get_post_meta( get_the_ID(), '_cpwp_affiliate_merchant', true );
							$coupon = get_post_meta( get_the_ID(), '_cpwp_affiliate_coupon', true );
							?>
							<a href="<?php the_permalink(); ?>" class="ca-deal-item">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php echo get_the_post_thumbnail( get_the_ID(), 'thumbnail', array( 'class' => 'ca-deal-item__thumb' ) ); ?>
								<?php else : ?>
									<div class="ca-deal-item__thumb ca-deal-item__thumb--placeholder"></div>
								<?php endif; ?>
								<div class="ca-deal-item__info">
									<h4 class="ca-deal-item__title"><?php the_title(); ?></h4>
									<div class="ca-deal-item__meta">
										<?php if ( $price ) : ?><strong class="ca-price"><?php echo esc_html( $price ); ?></strong><?php endif; ?>
										<?php if ( $merchant ) : ?><span class="ca-merchant"><?php echo esc_html( $merchant ); ?></span><?php endif; ?>
									</div>
									<?php if ( $coupon ) : ?><span class="ca-coupon-badge"><?php echo esc_html( $coupon ); ?></span><?php endif; ?>
								</div>
							</a>
						<?php endforeach; wp_reset_postdata();
					endif;
					?>
				</div>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_product' ) ); ?>" class="ca-btn ca-btn--outline ca-btn--full">View all deals</a>
			</div>
		</div>
	</section>

	<!-- Latest Reviews -->
	<section class="ca-section ca-section--alt">
		<div class="ca-container">
			<div class="ca-section__header">
				<h2 class="ca-section__title">Latest Video Reviews</h2>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>" class="ca-section__link">Browse all</a>
			</div>
			
			<div class="ca-grid">
				<?php
				$latest_reviews = get_posts( array(
					'post_type'      => 'cp_video',
					'posts_per_page' => 8
				) );
				if ( $latest_reviews ) :
					foreach ( $latest_reviews as $post ) : setup_postdata( $post );
					?>
					<article class="ca-card">
						<a href="<?php the_permalink(); ?>" class="ca-card__thumb-link">
							<div class="ca-card__thumb">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'medium_large' ); ?>
								<?php else : ?>
									<div class="ca-card__thumb-placeholder"></div>
								<?php endif; ?>
								<div class="ca-card__play"><span aria-hidden="true">▶</span></div>
							</div>
						</a>
						<div class="ca-card__body">
							<h3 class="ca-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="ca-card__meta">
								<?php 
								$author = get_the_author_meta( 'display_name' );
								echo esc_html( $author ); 
								?> · <?php echo esc_html( get_the_date() ); ?>
							</p>
						</div>
					</article>
					<?php endforeach; wp_reset_postdata();
				endif;
				?>
			</div>
		</div>
	</section>

</main>
<?php get_footer(); ?>
