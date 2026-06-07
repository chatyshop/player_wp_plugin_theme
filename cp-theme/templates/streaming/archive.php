<?php
/**
 * Template: Streaming — Browse / Archive Page
 * Poster grid with Movie / Episode / Genre filters.
 */

$filter_type  = isset( $_GET['cp_type'] )   ? sanitize_key( wp_unslash( $_GET['cp_type'] ) )   : '';
$filter_genre = isset( $_GET['cp_genre'] )  ? absint( $_GET['cp_genre'] )                       : 0;
$filter_sort  = isset( $_GET['cp_sort'] )   ? sanitize_key( wp_unslash( $_GET['cp_sort'] ) )    : 'newest';
$filter_page  = isset( $_GET['cp_page'] )   ? max( 1, absint( $_GET['cp_page'] ) )              : 1;
$per_page     = 24;

$args = array(
	'post_type'      => 'cp_video',
	'posts_per_page' => $per_page,
	'paged'          => $filter_page,
);

if ( $filter_type ) {
	$args['meta_query'] = array( array( 'key' => '_cpwp_streaming_type', 'value' => $filter_type ) );
}

if ( $filter_genre ) {
	$args['tax_query'] = array( array( 'taxonomy' => 'cp_genre', 'field' => 'term_id', 'terms' => $filter_genre ) );
}

if ( 'views' === $filter_sort ) {
	$args['meta_key'] = '_cpwp_views';
	$args['orderby']  = 'meta_value_num';
	$args['order']    = 'DESC';
} elseif ( 'oldest' === $filter_sort ) {
	$args['order'] = 'ASC';
}

$query   = new WP_Query( $args );
$genres  = get_terms( array( 'taxonomy' => 'cp_genre', 'hide_empty' => true ) );
$genres  = is_wp_error( $genres ) ? array() : $genres;

get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-streaming-content">

		<header class="cp-ott-browse-header">
			<h1 class="cp-ott-browse-title">
				<?php
				if ( 'movie' === $filter_type ) esc_html_e( 'Movies', 'cp-theme' );
				elseif ( 'episode' === $filter_type ) esc_html_e( 'TV Shows', 'cp-theme' );
				else esc_html_e( 'Browse All', 'cp-theme' );
				?>
			</h1>
		</header>

		<!-- Filters -->
		<form class="cp-ott-filters" method="get">
			<div class="cp-ott-filter-group">
				<label class="cp-ott-filter-label"><?php esc_html_e( 'Type', 'cp-theme' ); ?></label>
				<div class="cp-ott-filter-pills">
					<a href="<?php echo esc_url( remove_query_arg( 'cp_type' ) ); ?>" class="cp-ott-pill <?php echo ! $filter_type ? 'is-active' : ''; ?>"><?php esc_html_e( 'All', 'cp-theme' ); ?></a>
					<a href="<?php echo esc_url( add_query_arg( 'cp_type', 'movie' ) ); ?>" class="cp-ott-pill <?php echo 'movie' === $filter_type ? 'is-active' : ''; ?>">🎬 <?php esc_html_e( 'Movies', 'cp-theme' ); ?></a>
					<a href="<?php echo esc_url( add_query_arg( 'cp_type', 'episode' ) ); ?>" class="cp-ott-pill <?php echo 'episode' === $filter_type ? 'is-active' : ''; ?>">📺 <?php esc_html_e( 'TV Episodes', 'cp-theme' ); ?></a>
				</div>
			</div>

			<?php if ( $genres ) : ?>
			<div class="cp-ott-filter-group">
				<label class="cp-ott-filter-label"><?php esc_html_e( 'Genre', 'cp-theme' ); ?></label>
				<div class="cp-ott-filter-pills">
					<a href="<?php echo esc_url( remove_query_arg( 'cp_genre' ) ); ?>" class="cp-ott-pill <?php echo ! $filter_genre ? 'is-active' : ''; ?>"><?php esc_html_e( 'All', 'cp-theme' ); ?></a>
					<?php foreach ( $genres as $genre ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'cp_genre', $genre->term_id ) ); ?>" class="cp-ott-pill <?php echo $filter_genre === $genre->term_id ? 'is-active' : ''; ?>"><?php echo esc_html( $genre->name ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<div class="cp-ott-filter-group cp-ott-filter-sort">
				<label class="cp-ott-filter-label"><?php esc_html_e( 'Sort', 'cp-theme' ); ?></label>
				<select name="cp_sort" onchange="this.form.submit()">
					<option value="newest" <?php selected( $filter_sort, 'newest' ); ?>><?php esc_html_e( 'Newest', 'cp-theme' ); ?></option>
					<option value="views"  <?php selected( $filter_sort, 'views' ); ?>><?php esc_html_e( 'Most Viewed', 'cp-theme' ); ?></option>
					<option value="oldest" <?php selected( $filter_sort, 'oldest' ); ?>><?php esc_html_e( 'Oldest', 'cp-theme' ); ?></option>
				</select>
			</div>
		</form>

		<!-- Poster Grid -->
		<?php if ( $query->have_posts() ) : ?>
		<div class="cp-ott-poster-grid cp-ott-browse-grid">
			<?php while ( $query->have_posts() ) : $query->the_post();
				$vid_id   = get_the_ID();
				$vid_type = get_post_meta( $vid_id, '_cpwp_streaming_type', true );
				$vid_series = get_post_meta( $vid_id, '_cpwp_series_name', true );
				$vid_rating = get_post_meta( $vid_id, '_cpwp_age_rating', true );
				$vid_views  = absint( get_post_meta( $vid_id, '_cpwp_views', true ) );
			?>
			<article class="cp-ott-poster">
				<a href="<?php the_permalink(); ?>" class="cp-ott-poster-link">
					<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
					<?php else : ?>
					<div class="cp-ott-poster-placeholder"><span><?php the_title(); ?></span></div>
					<?php endif; ?>
					<div class="cp-ott-poster-overlay">
						<h2 class="cp-ott-poster-title"><?php the_title(); ?></h2>
						<?php if ( $vid_series ) : ?><small class="cp-ott-series-label"><?php echo esc_html( $vid_series ); ?></small><?php endif; ?>
						<div class="cp-ott-poster-meta">
							<?php if ( $vid_rating ) : ?><span class="cp-ott-rating"><?php echo esc_html( $vid_rating ); ?></span><?php endif; ?>
							<?php if ( $vid_type ) : ?>
							<span class="cp-ott-badge cp-ott-badge-<?php echo esc_attr( $vid_type ); ?>">
								<?php echo 'movie' === $vid_type ? '🎬' : '📺'; ?>
							</span>
							<?php endif; ?>
						</div>
						<?php if ( $vid_views ) : ?>
						<small><?php echo esc_html( number_format_i18n( $vid_views ) ); ?> <?php esc_html_e( 'views', 'cp-theme' ); ?></small>
						<?php endif; ?>
					</div>
				</a>
			</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<?php if ( $query->max_num_pages > 1 ) : ?>
		<nav class="cp-ott-pagination">
			<?php for ( $p = 1; $p <= $query->max_num_pages; $p++ ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'cp_page', $p ) ); ?>" class="cp-ott-page-btn <?php echo $p === $filter_page ? 'is-active' : ''; ?>"><?php echo esc_html( $p ); ?></a>
			<?php endfor; ?>
		</nav>
		<?php endif; ?>

		<?php else : ?>
		<div class="cp-ott-empty">
			<p><?php esc_html_e( 'No content found. Try changing your filters.', 'cp-theme' ); ?></p>
		</div>
		<?php endif; ?>

	</div>
</div>

<?php get_footer(); ?>
