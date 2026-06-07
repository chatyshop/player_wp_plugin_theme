<?php
/**
 * Template: Creator Platform — Homepage
 * YouTube-style homepage: channel spotlight + trending + latest + category rows.
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
$hero_channel     = array();
if ( $featured->have_posts() ) {
	$featured->the_post();
	$fid              = get_the_ID();
	$hero_title       = cp_theme_cp_setting( 'home_hero_title' ) ?: get_the_title();
	$hero_description = cp_theme_cp_setting( 'home_hero_description' ) ?: wp_trim_words( get_the_excerpt() ?: get_the_content(), 26 );
	$hero_permalink   = get_permalink();
	$owner            = absint( get_post_meta( $fid, '_cpwp_channel_owner', true ) );
	$hero_channel     = ( $owner && class_exists( 'CPWP_Channels' ) ) ? CPWP_Channels::get( $owner ) : array();
	ob_start(); the_post_thumbnail( 'full', array( 'loading' => 'eager' ) ); $hero_thumbnail = ob_get_clean();
	wp_reset_postdata();
}

get_header();
?>

<?php if ( is_user_logged_in() ) : ?>
<div class="cp-shell cp-page-layout-with-sidebar">
	<?php get_template_part( 'sidebar', 'logged-in' ); ?>
	<div class="cp-page-content cp-yt-content">
<?php else : ?>
<div class="cp-shell">
	<div class="cp-page-content cp-yt-content">
<?php endif; ?>

		<?php if ( $hero_permalink ) : ?>
		<section class="cp-yt-hero">
			<a class="cp-yt-hero-thumb" href="<?php echo esc_url( $hero_permalink ); ?>">
				<?php echo $hero_thumbnail; ?>
				<span class="cp-yt-hero-play" aria-hidden="true">&#9654;</span>
			</a>
			<div class="cp-yt-hero-info">
				<?php if ( $hero_channel ) : ?>
				<a class="cp-yt-hero-channel" href="<?php echo esc_url( CPWP_Channels::public_url( $hero_channel ) ); ?>">
					<img src="<?php echo esc_url( $hero_channel['logo_url'] ?? get_avatar_url( $hero_channel['user_id'] ?? 0 ) ); ?>" alt="">
					<span><?php echo esc_html( $hero_channel['name'] ); ?></span>
				</a>
				<?php else : ?>
				<span class="cp-kicker"><?php esc_html_e( 'Featured video', 'cp-theme' ); ?></span>
				<?php endif; ?>
				<h1 class="cp-yt-hero-title">
					<a href="<?php echo esc_url( $hero_permalink ); ?>"><?php echo esc_html( $hero_title ); ?></a>
				</h1>
				<p class="cp-yt-hero-desc"><?php echo esc_html( $hero_description ); ?></p>
				<a class="cp-button" href="<?php echo esc_url( $hero_permalink ); ?>">
					&#9654; <?php echo esc_html( cp_theme_cp_setting( 'home_hero_button', 'Watch now' ) ); ?>
				</a>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_hero' ); ?>

		<?php
		// Channel spotlight section.
		if ( class_exists( 'CPWP_Site_Modules' ) ) {
			$channels = CPWP_Site_Modules::channels();
			if ( $channels ) : ?>
		<section class="cp-yt-channels-row">
			<div class="cp-yt-row-head">
				<h2><?php esc_html_e( 'Creator Channels', 'cp-theme' ); ?></h2>
			</div>
			<div class="cp-yt-channel-strip">
				<?php foreach ( $channels as $item ) :
					$ch      = $item['channel'];
					$ch_url  = CPWP_Channels::public_url( $ch );
					$ch_logo = $ch['logo_url'] ?? get_avatar_url( $item['user']->ID );
					$ch_subs = count( CPWP_Channels::followers( $item['user']->ID ) );
				?>
				<a class="cp-yt-channel-chip" href="<?php echo esc_url( $ch_url ); ?>">
					<img src="<?php echo esc_url( $ch_logo ); ?>" alt="">
					<div>
						<strong><?php echo esc_html( $ch['name'] ); ?></strong>
						<small><?php echo esc_html( number_format_i18n( $ch_subs ) ); ?> <?php esc_html_e( 'subscribers', 'cp-theme' ); ?></small>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
		</section>
			<?php endif;
		}
		?>

		<?php
		// Subscribed channels' latest videos.
		if ( is_user_logged_in() && class_exists( 'CPWP_Channels' ) ) {
			$following = CPWP_Channels::following( get_current_user_id() );
			if ( $following ) {
				$sub_videos = get_posts( array(
					'post_type'      => 'cp_video',
					'posts_per_page' => 8,
					'meta_query'     => array( array( 'key' => '_cpwp_channel_owner', 'value' => $following, 'compare' => 'IN' ) ),
				) );
				if ( $sub_videos ) : ?>
		<section class="cp-yt-row">
			<div class="cp-yt-row-head">
				<h2><?php esc_html_e( 'From Subscriptions', 'cp-theme' ); ?></h2>
			</div>
			<div class="cp-yt-grid">
				<?php foreach ( $sub_videos as $v ) : cp_theme_video_card( $v->ID ); endforeach; ?>
			</div>
		</section>
				<?php endif;
			}
		}
		?>

		<?php
		// Standard configurable sections.
		$order = array_filter( array_map( 'sanitize_key', explode( ',', cp_theme_cp_setting( 'home_section_order', 'trending,latest,most_viewed,category_rows' ) ) ) );
		foreach ( $order as $section ) {
			if ( 'trending' === $section && cp_theme_cp_setting( 'home_show_trending', true ) ) : ?>
		<section class="cp-yt-row">
			<div class="cp-yt-row-head">
				<h2><?php echo esc_html( cp_theme_cp_setting( 'home_trending_title', 'Trending now' ) ); ?></h2>
				<a class="cp-yt-row-link" href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>"><?php esc_html_e( 'See all', 'cp-theme' ); ?> →</a>
			</div>
			<div class="cp-yt-grid">
				<?php
				$trending = get_posts( array( 'post_type' => 'cp_video', 'posts_per_page' => 8, 'meta_key' => '_cpwp_views', 'orderby' => 'meta_value_num', 'order' => 'DESC' ) );
				foreach ( $trending as $v ) cp_theme_video_card( $v->ID );
				?>
			</div>
		</section>
			<?php endif;

			if ( 'latest' === $section && cp_theme_cp_setting( 'home_show_latest', true ) ) : ?>
		<section class="cp-yt-row">
			<div class="cp-yt-row-head">
				<h2><?php echo esc_html( cp_theme_cp_setting( 'home_latest_title', 'Latest videos' ) ); ?></h2>
				<a class="cp-yt-row-link" href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>"><?php esc_html_e( 'See all', 'cp-theme' ); ?> →</a>
			</div>
			<div class="cp-yt-grid">
				<?php
				$latest = get_posts( array( 'post_type' => 'cp_video', 'posts_per_page' => 8 ) );
				foreach ( $latest as $v ) cp_theme_video_card( $v->ID );
				?>
			</div>
		</section>
			<?php endif;

			if ( 'category_rows' === $section && cp_theme_cp_setting( 'home_show_category_rows', true ) ) {
				foreach ( array_filter( array_map( 'absint', explode( ',', cp_theme_cp_setting( 'home_category_ids', '' ) ) ) ) as $cat_id ) {
					$cat = get_category( $cat_id );
					if ( ! $cat || is_wp_error( $cat ) ) continue;
					$cat_videos = get_posts( array( 'post_type' => 'cp_video', 'posts_per_page' => 8, 'cat' => $cat_id ) );
					if ( $cat_videos ) : ?>
		<section class="cp-yt-row">
			<div class="cp-yt-row-head">
				<h2><?php echo esc_html( $cat->name ); ?></h2>
				<a class="cp-yt-row-link" href="<?php echo esc_url( get_category_link( $cat_id ) ); ?>"><?php esc_html_e( 'See all', 'cp-theme' ); ?> →</a>
			</div>
			<div class="cp-yt-grid">
				<?php foreach ( $cat_videos as $v ) cp_theme_video_card( $v->ID ); ?>
			</div>
		</section>
					<?php endif;
				}
			}
		}
		?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_grid' ); ?>

	</div><!-- .cp-page-content -->
</div><!-- .cp-shell -->

<?php get_footer(); ?>
