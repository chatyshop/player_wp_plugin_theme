<?php
/**
 * Template: News — Archive
 * News article/video list with topic and location filters.
 */

$filter_topic = isset( $_GET['cp_topic'] ) ? sanitize_text_field( wp_unslash( $_GET['cp_topic'] ) ) : '';
$filter_loc   = isset( $_GET['cp_location'] ) ? sanitize_text_field( wp_unslash( $_GET['cp_location'] ) ) : '';
$paged        = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( isset( $_GET['cp_page'] ) ? max( 1, absint( $_GET['cp_page'] ) ) : 1 );

$args = array(
	'post_type'      => array( 'cp_news', 'cp_video' ),
	'posts_per_page' => 16,
	'paged'          => $paged,
);

$tax_query = array();
if ( $filter_topic ) {
	$tax_query[] = array( 'taxonomy' => 'cp_topic', 'field' => 'slug', 'terms' => $filter_topic );
}
if ( $filter_loc ) {
	$tax_query[] = array( 'taxonomy' => 'cp_location', 'field' => 'slug', 'terms' => $filter_loc );
}
if ( count( $tax_query ) > 1 ) {
	$tax_query['relation'] = 'AND';
}
if ( ! empty( $tax_query ) ) {
	$args['tax_query'] = $tax_query;
}

$query = new WP_Query( $args );

// For filters
$topics = get_terms( array( 'taxonomy' => 'cp_topic', 'hide_empty' => true ) );
$locations = get_terms( array( 'taxonomy' => 'cp_location', 'hide_empty' => true ) );

get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-news-content">
		
		<div class="cp-news-archive-header">
			<h1><?php esc_html_e( 'Latest News', 'cp-theme' ); ?></h1>
			
			<form class="cp-news-filters" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'cp_news' ) ); ?>">
				<div class="cp-news-filter-group">
					<label><?php esc_html_e( 'Topic:', 'cp-theme' ); ?></label>
					<select name="cp_topic" onchange="this.form.submit()">
						<option value=""><?php esc_html_e( 'All Topics', 'cp-theme' ); ?></option>
						<?php if ( ! is_wp_error( $topics ) ) : foreach ( $topics as $t ) : ?>
						<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $filter_topic, $t->slug ); ?>><?php echo esc_html( $t->name ); ?></option>
						<?php endforeach; endif; ?>
					</select>
				</div>
				<div class="cp-news-filter-group">
					<label><?php esc_html_e( 'Location:', 'cp-theme' ); ?></label>
					<select name="cp_location" onchange="this.form.submit()">
						<option value=""><?php esc_html_e( 'All Locations', 'cp-theme' ); ?></option>
						<?php if ( ! is_wp_error( $locations ) ) : foreach ( $locations as $l ) : ?>
						<option value="<?php echo esc_attr( $l->slug ); ?>" <?php selected( $filter_loc, $l->slug ); ?>><?php echo esc_html( $l->name ); ?></option>
						<?php endforeach; endif; ?>
					</select>
				</div>
			</form>
		</div>

		<?php if ( $query->have_posts() ) : ?>
		<div class="cp-news-grid">
			<?php while ( $query->have_posts() ) : $query->the_post();
				$pid     = get_the_ID();
				$thumb   = get_the_post_thumbnail_url( $pid, 'medium_large' );
				$ptopics = get_the_terms( $pid, 'cp_topic' );
			?>
			<article class="cp-news-card">
				<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>" class="cp-news-card-thumb">
					<?php if ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy"><?php else : ?><div class="cp-news-placeholder-sm">📰</div><?php endif; ?>
					<?php if ( 'cp_video' === get_post_type() ) : ?><div class="cp-news-video-icon-sm">▶</div><?php endif; ?>
				</a>
				<div class="cp-news-card-body">
					<?php if ( ! empty( $ptopics ) ) : ?>
					<a href="<?php echo esc_url( get_term_link( $ptopics[0] ) ); ?>" class="cp-news-cat-link"><?php echo esc_html( $ptopics[0]->name ); ?></a>
					<?php endif; ?>
					<h3 class="cp-news-card-title"><a href="<?php echo esc_url( get_permalink( $pid ) ); ?>"><?php the_title(); ?></a></h3>
					<div class="cp-news-meta"><span class="cp-news-time"><?php echo human_time_diff( get_post_time('U', false, $pid), current_time('timestamp') ) . ' ago'; ?></span></div>
				</div>
			</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<?php if ( $query->max_num_pages > 1 ) : ?>
		<nav class="cp-news-pagination">
			<?php for ( $p = 1; $p <= $query->max_num_pages; $p++ ) : 
				$url = add_query_arg( 'cp_page', $p );
			?>
			<a href="<?php echo esc_url( $url ); ?>" class="cp-news-page-btn <?php echo $p === $paged ? 'is-active' : ''; ?>"><?php echo esc_html( $p ); ?></a>
			<?php endfor; ?>
		</nav>
		<?php endif; ?>

		<?php else : ?>
		<div class="cp-news-empty">
			<p><?php esc_html_e( 'No news articles found.', 'cp-theme' ); ?></p>
		</div>
		<?php endif; ?>

	</div>
</div>
<?php get_footer(); ?>
