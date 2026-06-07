<?php
/**
 * Template: Creator Platform — Browse / Archive Page
 * YouTube-style 16:9 grid with search, sort, category filter tabs.
 */

$filter_cat  = isset( $_GET['cp_category'] ) ? absint( $_GET['cp_category'] )                    : 0;
$filter_sort = isset( $_GET['cp_sort'] )     ? sanitize_key( wp_unslash( $_GET['cp_sort'] ) )    : 'newest';
$filter_s    = isset( $_GET['cp_search'] )   ? sanitize_text_field( wp_unslash( $_GET['cp_search'] ) ) : '';
$filter_page = isset( $_GET['cp_page'] )     ? max( 1, absint( $_GET['cp_page'] ) )              : 1;
$per_page    = 20;

$args = array(
	'post_type'      => 'cp_video',
	'posts_per_page' => $per_page,
	'paged'          => $filter_page,
	's'              => $filter_s,
);
if ( $filter_cat ) $args['cat'] = $filter_cat;
if ( 'views' === $filter_sort ) { $args['meta_key'] = '_cpwp_views'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; }
elseif ( 'oldest' === $filter_sort ) $args['order'] = 'ASC';

$query      = new WP_Query( $args );
$categories = get_categories( array( 'hide_empty' => true ) );

get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-yt-content">

		<!-- Search bar -->
		<form class="cp-yt-search-bar" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>">
			<input type="search" name="cp_search" value="<?php echo esc_attr( $filter_s ); ?>" placeholder="<?php esc_attr_e( 'Search videos…', 'cp-theme' ); ?>" class="cp-yt-search-input">
			<button type="submit" class="cp-yt-search-btn">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
			</button>
		</form>

		<!-- Category filter tabs -->
		<div class="cp-yt-filter-tabs">
			<a href="<?php echo esc_url( remove_query_arg( 'cp_category' ) ); ?>" class="cp-yt-filter-tab <?php echo ! $filter_cat ? 'is-active' : ''; ?>"><?php esc_html_e( 'All', 'cp-theme' ); ?></a>
			<?php foreach ( $categories as $cat ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'cp_category', $cat->term_id ) ); ?>" class="cp-yt-filter-tab <?php echo $filter_cat === $cat->term_id ? 'is-active' : ''; ?>"><?php echo esc_html( $cat->name ); ?></a>
			<?php endforeach; ?>
		</div>

		<!-- Sort bar -->
		<div class="cp-yt-sort-bar">
			<span><?php esc_html_e( 'Sort by:', 'cp-theme' ); ?></span>
			<?php foreach ( array( 'newest' => __( 'Newest', 'cp-theme' ), 'views' => __( 'Most viewed', 'cp-theme' ), 'oldest' => __( 'Oldest', 'cp-theme' ) ) as $val => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'cp_sort', $val ) ); ?>" class="cp-yt-sort-link <?php echo $filter_sort === $val ? 'is-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</div>

		<!-- Video grid -->
		<?php if ( $query->have_posts() ) : ?>
		<div class="cp-yt-grid cp-yt-browse-grid">
			<?php while ( $query->have_posts() ) : $query->the_post();
				$vid_id    = get_the_ID();
				$vid_views = absint( get_post_meta( $vid_id, '_cpwp_views', true ) );
				$vid_owner = absint( get_post_meta( $vid_id, '_cpwp_channel_owner', true ) );
				$vid_ch    = ( $vid_owner && class_exists( 'CPWP_Channels' ) ) ? CPWP_Channels::get( $vid_owner ) : array();
			?>
			<article class="cp-yt-card">
				<a class="cp-yt-card-thumb" href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) );
					else echo '<div class="cp-yt-thumb-placeholder">▶</div>'; ?>
					<span class="cp-yt-card-play" aria-hidden="true">▶</span>
				</a>
				<div class="cp-yt-card-body">
					<?php if ( $vid_ch ) : ?>
					<a class="cp-yt-card-avatar" href="<?php echo esc_url( CPWP_Channels::public_url( $vid_ch ) ); ?>">
						<img src="<?php echo esc_url( $vid_ch['logo_url'] ?? get_avatar_url( $vid_owner ) ); ?>" alt="">
					</a>
					<?php endif; ?>
					<div class="cp-yt-card-meta">
						<h2 class="cp-yt-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php if ( $vid_ch ) : ?>
						<a class="cp-yt-card-channel" href="<?php echo esc_url( CPWP_Channels::public_url( $vid_ch ) ); ?>"><?php echo esc_html( $vid_ch['name'] ); ?></a>
						<?php endif; ?>
						<p class="cp-yt-card-stats">
							<?php echo esc_html( number_format_i18n( $vid_views ) ); ?> <?php esc_html_e( 'views', 'cp-theme' ); ?>
							· <?php echo esc_html( get_the_date() ); ?>
						</p>
					</div>
				</div>
			</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<!-- Pagination -->
		<?php if ( $query->max_num_pages > 1 ) : ?>
		<nav class="cp-yt-pagination">
			<?php for ( $p = 1; $p <= $query->max_num_pages; $p++ ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'cp_page', $p ) ); ?>" class="cp-yt-page-btn <?php echo $p === $filter_page ? 'is-active' : ''; ?>"><?php echo esc_html( $p ); ?></a>
			<?php endfor; ?>
		</nav>
		<?php endif; ?>

		<?php else : ?>
		<div class="cp-yt-empty">
			<p><?php esc_html_e( 'No videos found. Try a different search or category.', 'cp-theme' ); ?></p>
		</div>
		<?php endif; ?>

	</div>
</div>

<?php get_footer(); ?>
