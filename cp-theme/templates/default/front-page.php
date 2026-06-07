<?php
/**
 * Template: Default — Homepage
 * Generic homepage used by all non-streaming site types.
 */
$featured_id   = absint( cp_theme_cp_setting( 'home_featured_video', 0 ) );
$featured_args = array( 'post_type' => 'cp_video', 'posts_per_page' => 1 );
if ( $featured_id ) {
	$featured_args['p'] = $featured_id;
} else {
	$featured_args['meta_key'] = '_cpwp_views';
	$featured_args['orderby']  = 'meta_value_num';
	$featured_args['order']    = 'DESC';
}
$featured         = new WP_Query( $featured_args );
$hero_title       = '';
$hero_description = '';
$hero_permalink   = '';
$hero_thumbnail   = '';
if ( $featured->have_posts() ) {
	$featured->the_post();
	$hero_title       = cp_theme_cp_setting( 'home_hero_title' ) ?: get_the_title();
	$hero_description = cp_theme_cp_setting( 'home_hero_description' ) ?: wp_trim_words( get_the_excerpt() ?: get_the_content(), 26 );
	$hero_permalink   = get_permalink();
	ob_start(); the_post_thumbnail( 'full' ); $hero_thumbnail = ob_get_clean();
	wp_reset_postdata();
}

get_header();
?>

<?php if ( is_user_logged_in() ) : ?>
<div class="cp-shell cp-page-layout-with-sidebar">
	<?php get_template_part( 'sidebar', 'logged-in' ); ?>
	<div class="cp-page-content">
<?php else : ?>
<div class="cp-shell">
	<div class="cp-page-content">
<?php endif; ?>

		<?php if ( $hero_permalink ) : ?>
		<section class="cp-hero cp-hero-inline">
			<div class="cp-hero-media"><?php echo $hero_thumbnail; ?></div>
			<div class="cp-hero-overlay"></div>
			<div class="cp-hero-content">
				<span class="cp-kicker"><?php esc_html_e( 'Featured video', 'cp-theme' ); ?></span>
				<h1><?php echo esc_html( $hero_title ); ?></h1>
				<p><?php echo esc_html( $hero_description ); ?></p>
				<a class="cp-button" href="<?php echo esc_url( $hero_permalink ); ?>">&#9654; <?php echo esc_html( cp_theme_cp_setting( 'home_hero_button', 'Watch now' ) ); ?></a>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_hero' ); ?>

		<?php
		$order = array_filter( array_map( 'sanitize_key', explode( ',', cp_theme_cp_setting( 'home_section_order', 'categories,trending,latest,most_viewed,category_rows,promo' ) ) ) );
		foreach ( $order as $section ) {
			if ( 'categories' === $section && cp_theme_cp_setting( 'home_show_categories', true ) ) {
				$categories = get_categories( array( 'hide_empty' => true ) );
				if ( $categories ) { ?><section class="cp-section"><div class="cp-section-head"><h2><?php esc_html_e( 'Explore categories', 'cp-theme' ); ?></h2></div><div class="cp-category-row"><?php foreach ( $categories as $cat ) : ?><a class="cp-category-pill" href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a><?php endforeach; ?></div></section><?php }
			}
			if ( 'trending' === $section && cp_theme_cp_setting( 'home_show_trending', true ) ) cp_theme_video_section( cp_theme_cp_setting( 'home_trending_title', 'Trending now' ), array( 'meta_key' => '_cpwp_views', 'orderby' => 'meta_value_num', 'order' => 'DESC' ), get_post_type_archive_link( 'cp_video' ) );
			if ( 'latest' === $section && cp_theme_cp_setting( 'home_show_latest', true ) ) cp_theme_video_section( cp_theme_cp_setting( 'home_latest_title', 'Latest videos' ), array(), get_post_type_archive_link( 'cp_video' ) );
			if ( 'most_viewed' === $section && cp_theme_cp_setting( 'home_show_most_viewed', true ) ) cp_theme_video_section( cp_theme_cp_setting( 'home_most_viewed_title', 'Most viewed' ), array( 'meta_key' => '_cpwp_views', 'orderby' => 'meta_value_num', 'order' => 'DESC' ) );
			if ( 'category_rows' === $section && cp_theme_cp_setting( 'home_show_category_rows', true ) ) {
				foreach ( array_filter( array_map( 'absint', explode( ',', cp_theme_cp_setting( 'home_category_ids', '' ) ) ) ) as $cat_id ) {
					$cat = get_category( $cat_id );
					if ( $cat && ! is_wp_error( $cat ) ) cp_theme_video_section( $cat->name, array( 'cat' => $cat_id ), get_category_link( $cat_id ) );
				}
			}
			if ( 'promo' === $section && cp_theme_cp_setting( 'home_show_promo', false ) ) { ?><section class="cp-promo"><div><span class="cp-kicker"><?php esc_html_e( 'Featured', 'cp-theme' ); ?></span><h2><?php echo esc_html( cp_theme_cp_setting( 'home_promo_title' ) ); ?></h2><p><?php echo esc_html( cp_theme_cp_setting( 'home_promo_content' ) ); ?></p></div><?php if ( cp_theme_cp_setting( 'home_promo_url' ) ) : ?><a class="cp-button" href="<?php echo esc_url( cp_theme_cp_setting( 'home_promo_url' ) ); ?>"><?php echo esc_html( cp_theme_cp_setting( 'home_promo_button', 'Learn more' ) ); ?></a><?php endif; ?></section><?php }
		}
		if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_grid' );
		cp_theme_preset_home_sections();
		?>

	</div>
</div>

<?php get_footer(); ?>
