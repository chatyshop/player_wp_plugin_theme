<?php
/**
 * Template: Affiliate / Deal Site — Archive
 */
get_header();

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$is_search = is_search();
$is_products = is_post_type_archive( 'cp_product' );
?>
<main class="ca-main">
	<div class="ca-archive-header">
		<div class="ca-container">
			<?php if ( $is_search ) : ?>
				<h1 class="ca-archive-title">Search results for: "<?php echo esc_html( get_search_query() ); ?>"</h1>
			<?php else : ?>
				<h1 class="ca-archive-title">
					<?php 
					if ( is_post_type_archive( 'cp_video' ) ) echo 'Video Reviews';
					elseif ( $is_products ) echo 'All Deals & Products';
					else echo post_type_archive_title( '', false ); 
					?>
				</h1>
			<?php endif; ?>
		</div>
	</div>

	<div class="ca-container ca-archive-body">
		<?php if ( have_posts() ) : ?>
			
			<div class="<?php echo $is_products ? 'ca-deals-grid' : 'ca-grid'; ?>">
				<?php while ( have_posts() ) : the_post(); ?>
					
					<?php if ( $is_products || 'cp_product' === get_post_type() ) : 
						$price = get_post_meta( get_the_ID(), '_cpwp_affiliate_price', true );
						$merchant = get_post_meta( get_the_ID(), '_cpwp_affiliate_merchant', true );
						$coupon = get_post_meta( get_the_ID(), '_cpwp_affiliate_coupon', true );
					?>
						<article class="ca-product-card">
							<a href="<?php the_permalink(); ?>" class="ca-product-card__thumb">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'medium' ); ?>
								<?php else : ?>
									<div class="ca-product-card__thumb-placeholder"></div>
								<?php endif; ?>
							</a>
							<div class="ca-product-card__body">
								<?php if ( $merchant ) : ?><span class="ca-merchant"><?php echo esc_html( $merchant ); ?></span><?php endif; ?>
								<h3 class="ca-product-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<div class="ca-product-card__footer">
									<?php if ( $price ) : ?><strong class="ca-price"><?php echo esc_html( $price ); ?></strong><?php endif; ?>
									<a href="<?php the_permalink(); ?>" class="ca-btn ca-btn--primary ca-btn--sm">View Deal</a>
								</div>
							</div>
						</article>

					<?php else : ?>
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
									<?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?> · <?php echo esc_html( get_the_date() ); ?>
								</p>
							</div>
						</article>
					<?php endif; ?>

				<?php endwhile; ?>
			</div>

			<div class="cp-pagination">
				<?php 
				echo paginate_links( array(
					'prev_text' => '← Previous',
					'next_text' => 'Next →',
				) ); 
				?>
			</div>
		<?php else : ?>
			<div class="ca-empty">
				<p>No content found.</p>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
