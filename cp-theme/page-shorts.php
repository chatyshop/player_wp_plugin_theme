<?php
/**
 * Template Name: Shorts
 */
get_header(); $shorts = new WP_Query( array( 'post_type' => 'cp_video', 'posts_per_page' => 30, 'meta_key' => '_cpwp_vertical', 'meta_value' => '1' ) );
?>
<div class="cp-shell"><header class="cp-section-head"><div><span class="cp-kicker"><?php esc_html_e( 'Vertical videos', 'cp-theme' ); ?></span><h1><?php esc_html_e( 'Shorts', 'cp-theme' ); ?></h1></div></header><div class="cp-shorts-feed"><?php while ( $shorts->have_posts() ) : $shorts->the_post(); ?><article class="cp-short-card"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'large' ); ?><div><h2><?php the_title(); ?></h2><span><?php echo esc_html( number_format_i18n( absint( get_post_meta( get_the_ID(), '_cpwp_views', true ) ) ) ); ?> views</span></div></a></article><?php endwhile; wp_reset_postdata(); ?></div></div>
<?php get_footer(); ?>
