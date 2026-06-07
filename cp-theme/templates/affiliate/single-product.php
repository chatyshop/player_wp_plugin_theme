<?php
/**
 * Template: Affiliate / Deal Site — Single Product
 * Dedicated product/deal page.
 */
get_header();

$price = get_post_meta( get_the_ID(), '_cpwp_affiliate_price', true );
$merchant = get_post_meta( get_the_ID(), '_cpwp_affiliate_merchant', true );
$coupon = get_post_meta( get_the_ID(), '_cpwp_affiliate_coupon', true );
$expiry = get_post_meta( get_the_ID(), '_cpwp_affiliate_expiry', true );
$affiliate_url = get_post_meta( get_the_ID(), '_cpwp_external_url', true ) ?: get_post_meta( get_the_ID(), '_cpwp_affiliate_url', true );
?>
<main class="ca-main">
	<div class="ca-container ca-product-layout">
		
		<div class="ca-product-gallery">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large', array( 'class' => 'ca-product-image' ) ); ?>
			<?php else : ?>
				<div class="ca-product-image-placeholder">No image available</div>
			<?php endif; ?>
		</div>

		<div class="ca-product-details">
			<div class="ca-product-header">
				<?php if ( $merchant ) : ?><span class="ca-merchant-tag"><?php echo esc_html( $merchant ); ?></span><?php endif; ?>
				<h1 class="ca-product-title"><?php the_title(); ?></h1>
			</div>
			
			<div class="ca-product-price-box">
				<?php if ( $price ) : ?>
					<div class="ca-product-price"><?php echo esc_html( $price ); ?></div>
				<?php endif; ?>
				
				<?php if ( $coupon ) : ?>
					<div class="ca-product-coupon">
						Use code: <strong><?php echo esc_html( $coupon ); ?></strong>
					</div>
				<?php endif; ?>
			</div>

			<div class="ca-product-buy">
				<?php if ( $affiliate_url ) : ?>
					<a href="<?php echo esc_url( $affiliate_url ); ?>" target="_blank" rel="nofollow sponsored" class="ca-btn ca-btn--primary ca-btn--lg ca-btn--full">View Deal at <?php echo esc_html( $merchant ?: 'Retailer' ); ?></a>
					<p class="ca-disclosure-text">If you buy something from a link, we may earn a commission.</p>
				<?php else : ?>
					<p class="ca-expired-text">Deal currently unavailable.</p>
				<?php endif; ?>
			</div>

			<div class="ca-product-description">
				<h3>About this deal</h3>
				<?php the_content(); ?>
				
				<?php if ( $expiry ) : ?>
					<p class="ca-expiry-date"><strong>Expires:</strong> <?php echo esc_html( $expiry ); ?></p>
				<?php endif; ?>
			</div>
		</div>

	</div>
</main>
<?php get_footer(); ?>
