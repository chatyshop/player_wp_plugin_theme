<?php
/**
 * Template: Courses — Course Catalog / Archive
 * Udemy-style course catalog with search bar, category filter pills,
 * sort control, course grid with progress bars, and pagination.
 */

$filter_search   = isset( $_GET['s'] )        ? sanitize_text_field( wp_unslash( $_GET['s'] ) )        : '';
$filter_cat      = isset( $_GET['cp_cat'] )   ? absint( $_GET['cp_cat'] )                               : 0;
$filter_sort     = isset( $_GET['cp_sort'] )  ? sanitize_key( wp_unslash( $_GET['cp_sort'] ) )          : 'newest';
$filter_page     = isset( $_GET['cp_page'] )  ? max( 1, absint( $_GET['cp_page'] ) )                    : 1;
$per_page        = 12;
$user_id         = get_current_user_id();
$is_logged_in    = is_user_logged_in();

$args = array(
	'post_type'      => 'cp_course',
	'posts_per_page' => $per_page,
	'paged'          => $filter_page,
	'post_status'    => 'publish',
);

if ( $filter_search ) {
	$args['s'] = $filter_search;
}

if ( $filter_cat ) {
	$args['tax_query'] = array( array(
		'taxonomy' => 'cp_course_cat',
		'field'    => 'term_id',
		'terms'    => $filter_cat,
	) );
}

if ( 'popular' === $filter_sort ) {
	$args['meta_key'] = '_cpwp_views';
	$args['orderby']  = 'meta_value_num';
	$args['order']    = 'DESC';
} else {
	$args['orderby'] = 'date';
	$args['order']   = 'DESC';
}

$query      = new WP_Query( $args );
$categories = get_terms( array( 'taxonomy' => 'cp_course_cat', 'hide_empty' => true ) );
$categories = is_wp_error( $categories ) ? array() : $categories;

// Enrolled courses for progress display.
$enrolled_ids = ( $is_logged_in && class_exists( 'CPWP_Learning' ) ) ? CPWP_Learning::enrolled_courses( $user_id ) : array();

$current_url = get_post_type_archive_link( 'cp_course' );

get_header();
?>

<div class="cp-shell <?php echo $is_logged_in ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( $is_logged_in ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-udemy-content">

		<header class="cp-udemy-catalog-header">
			<h1 class="cp-udemy-catalog-title"><?php esc_html_e( 'All Courses', 'cp-theme' ); ?></h1>
		</header>

		<!-- Search Bar -->
		<form class="cp-udemy-catalog-search" method="get" role="search" action="<?php echo esc_url( $current_url ); ?>">
			<div class="cp-udemy-search-bar">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
				<input
					type="search"
					name="s"
					placeholder="<?php esc_attr_e( 'Search courses…', 'cp-theme' ); ?>"
					value="<?php echo esc_attr( $filter_search ); ?>"
					class="cp-udemy-search-input"
					aria-label="<?php esc_attr_e( 'Search courses', 'cp-theme' ); ?>"
				>
			</div>
		</form>

		<!-- Category filter pills + sort -->
		<div class="cp-udemy-filter-bar">
			<?php if ( $categories ) : ?>
			<div class="cp-udemy-filter-pills" role="list" aria-label="<?php esc_attr_e( 'Filter by category', 'cp-theme' ); ?>">
				<a
					role="listitem"
					href="<?php echo esc_url( remove_query_arg( 'cp_cat', $current_url ) ); ?>"
					class="cp-udemy-pill <?php echo ! $filter_cat ? 'is-active' : ''; ?>"
				><?php esc_html_e( 'All', 'cp-theme' ); ?></a>
				<?php foreach ( $categories as $cat ) : ?>
				<a
					role="listitem"
					href="<?php echo esc_url( add_query_arg( 'cp_cat', $cat->term_id, $current_url ) ); ?>"
					class="cp-udemy-pill <?php echo $filter_cat === $cat->term_id ? 'is-active' : ''; ?>"
				><?php echo esc_html( $cat->name ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<div class="cp-udemy-sort-wrap">
				<label for="cp-udemy-sort" class="cp-udemy-sort-label"><?php esc_html_e( 'Sort:', 'cp-theme' ); ?></label>
				<select id="cp-udemy-sort" name="cp_sort" class="cp-udemy-sort-select" onchange="this.form && this.form.submit(); location.href='<?php echo esc_js( add_query_arg( 'cp_sort', '' ) ); ?>'.replace('cp_sort=','cp_sort='+this.value)">
					<option value="newest" <?php selected( $filter_sort, 'newest' ); ?>><?php esc_html_e( 'Newest', 'cp-theme' ); ?></option>
					<option value="popular" <?php selected( $filter_sort, 'popular' ); ?>><?php esc_html_e( 'Most Popular', 'cp-theme' ); ?></option>
				</select>
			</div>
		</div>

		<!-- Course Grid -->
		<?php if ( $query->have_posts() ) : ?>
		<div class="cp-udemy-course-grid">
			<?php while ( $query->have_posts() ) : $query->the_post();
				$cid      = get_the_ID();
				$thumb    = get_the_post_thumbnail_url( $cid, 'medium_large' );
				$author   = get_the_author_meta( 'display_name', get_post_field( 'post_author', $cid ) );
				$lessons  = class_exists( 'CPWP_Site_Modules' ) ? CPWP_Site_Modules::children( $cid, array( 'cp_lesson', 'cp_quiz' ) ) : array();
				$enrolled = in_array( $cid, $enrolled_ids, true );
				$progress = ( $enrolled && class_exists( 'CPWP_Learning' ) ) ? CPWP_Learning::course_progress( $user_id, $cid ) : 0;
			?>
			<article class="cp-udemy-course-card">
				<a href="<?php the_permalink(); ?>" class="cp-udemy-card-thumb-link" tabindex="-1" aria-hidden="true">
					<div class="cp-udemy-card-thumb">
						<?php if ( $thumb ) : ?>
						<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
						<?php else : ?>
						<div class="cp-udemy-thumb-placeholder">📚</div>
						<?php endif; ?>
					</div>
				</a>
				<div class="cp-udemy-card-body">
					<h2 class="cp-udemy-card-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<?php if ( $author ) : ?>
					<p class="cp-udemy-card-instructor"><?php echo esc_html( $author ); ?></p>
					<?php endif; ?>
					<?php if ( $lessons ) : ?>
					<p class="cp-udemy-card-meta">
						<span>📖 <?php echo esc_html( count( $lessons ) ); ?> <?php esc_html_e( 'lessons', 'cp-theme' ); ?></span>
					</p>
					<?php endif; ?>
					<?php if ( $enrolled && $progress > 0 ) : ?>
					<div class="cp-udemy-progress-wrap">
						<div class="cp-udemy-progress-bar" role="progressbar" aria-valuenow="<?php echo esc_attr( $progress ); ?>" aria-valuemin="0" aria-valuemax="100">
							<div class="cp-udemy-progress-fill" style="width:<?php echo esc_attr( $progress ); ?>%"></div>
						</div>
						<span class="cp-udemy-progress-label"><?php echo esc_html( $progress ); ?>%</span>
					</div>
					<?php endif; ?>
					<div class="cp-udemy-card-actions">
						<?php if ( $enrolled ) : ?>
						<a href="<?php the_permalink(); ?>" class="cp-button"><?php esc_html_e( 'Continue', 'cp-theme' ); ?></a>
						<?php elseif ( $is_logged_in ) : ?>
						<button class="cp-button" data-cpwp-enroll-course="<?php echo esc_attr( $cid ); ?>"><?php esc_html_e( 'Enroll', 'cp-theme' ); ?></button>
						<?php else : ?>
						<a href="<?php the_permalink(); ?>" class="cp-button"><?php esc_html_e( 'View Course', 'cp-theme' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<!-- Pagination -->
		<?php if ( $query->max_num_pages > 1 ) : ?>
		<nav class="cp-udemy-pagination" aria-label="<?php esc_attr_e( 'Course pages', 'cp-theme' ); ?>">
			<?php for ( $p = 1; $p <= $query->max_num_pages; $p++ ) : ?>
			<a
				href="<?php echo esc_url( add_query_arg( 'cp_page', $p ) ); ?>"
				class="cp-udemy-page-btn <?php echo $p === $filter_page ? 'is-active' : ''; ?>"
				<?php echo $p === $filter_page ? 'aria-current="page"' : ''; ?>
			><?php echo esc_html( $p ); ?></a>
			<?php endfor; ?>
		</nav>
		<?php endif; ?>

		<?php else : ?>
		<div class="cp-udemy-empty">
			<span class="cp-udemy-empty-icon">🔍</span>
			<p><?php esc_html_e( 'No courses found. Try adjusting your filters.', 'cp-theme' ); ?></p>
		</div>
		<?php endif; ?>

	</div><!-- .cp-page-content -->
</div><!-- .cp-shell -->

<?php get_footer(); ?>
