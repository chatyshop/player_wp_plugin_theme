<?php
/**
 * Template: Gaming — Archive / Browse Page
 * Browse categories (games) or live channels/videos.
 */

$filter_game = isset( $_GET['cp_game'] ) ? sanitize_text_field( wp_unslash( $_GET['cp_game'] ) ) : '';
$filter_sort = isset( $_GET['cp_sort'] ) ? sanitize_key( wp_unslash( $_GET['cp_sort'] ) ) : 'viewers';
$filter_page = isset( $_GET['cp_page'] ) ? max( 1, absint( $_GET['cp_page'] ) ) : 1;
$per_page    = 24;

$args = array(
	'post_type'      => 'cp_video',
	'posts_per_page' => $per_page,
	'paged'          => $filter_page,
);

if ( $filter_game ) {
	$args['tax_query'] = array( array( 'taxonomy' => 'cp_game', 'field' => 'slug', 'terms' => $filter_game ) );
}

if ( 'viewers' === $filter_sort ) {
	$args['meta_key'] = '_cpwp_views';
	$args['orderby']  = 'meta_value_num';
	$args['order']    = 'DESC';
} elseif ( 'newest' === $filter_sort ) {
	$args['orderby'] = 'date';
	$args['order']   = 'DESC';
}

$query = new WP_Query( $args );

// Top Games for tabs
$top_games = get_terms( array( 'taxonomy' => 'cp_game', 'hide_empty' => true, 'number' => 10, 'orderby' => 'count', 'order' => 'DESC' ) );

get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-twitch-content">
		<h1 class="cp-twitch-browse-title"><?php esc_html_e( 'Browse', 'cp-theme' ); ?></h1>

		<!-- Filter Tabs -->
		<div class="cp-twitch-filter-tabs">
			<a href="<?php echo esc_url( remove_query_arg( 'cp_game' ) ); ?>" class="cp-twitch-filter-tab <?php echo ! $filter_game ? 'is-active' : ''; ?>"><?php esc_html_e( 'Live Channels', 'cp-theme' ); ?></a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_event' ) ); ?>" class="cp-twitch-filter-tab"><?php esc_html_e( 'Esports Events', 'cp-theme' ); ?></a>
		</div>

		<!-- Sort & Game Pills -->
		<div class="cp-twitch-browse-controls">
			<div class="cp-twitch-game-pills">
				<?php if ( ! is_wp_error( $top_games ) ) : ?>
					<a href="<?php echo esc_url( remove_query_arg( 'cp_game' ) ); ?>" class="cp-twitch-pill <?php echo ! $filter_game ? 'is-active' : ''; ?>"><?php esc_html_e( 'All', 'cp-theme' ); ?></a>
					<?php foreach ( $top_games as $tg ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'cp_game', $tg->slug ) ); ?>" class="cp-twitch-pill <?php echo $filter_game === $tg->slug ? 'is-active' : ''; ?>"><?php echo esc_html( $tg->name ); ?></a>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="cp-twitch-sort">
				<label><?php esc_html_e( 'Sort by', 'cp-theme' ); ?></label>
				<select onchange="window.location.href=this.value">
					<option value="<?php echo esc_url( add_query_arg( 'cp_sort', 'viewers' ) ); ?>" <?php selected( $filter_sort, 'viewers' ); ?>><?php esc_html_e( 'Recommended For You', 'cp-theme' ); ?></option>
					<option value="<?php echo esc_url( add_query_arg( 'cp_sort', 'viewers' ) ); ?>" <?php selected( $filter_sort, 'viewers' ); ?>><?php esc_html_e( 'Viewers (High to Low)', 'cp-theme' ); ?></option>
					<option value="<?php echo esc_url( add_query_arg( 'cp_sort', 'newest' ) ); ?>" <?php selected( $filter_sort, 'newest' ); ?>><?php esc_html_e( 'Recently Started', 'cp-theme' ); ?></option>
				</select>
			</div>
		</div>

		<!-- Grid -->
		<?php if ( $query->have_posts() ) : ?>
		<div class="cp-twitch-video-grid">
			<?php while ( $query->have_posts() ) : $query->the_post();
				$vid_id = get_the_ID();
				$author = get_the_author_meta( 'display_name', $post->post_author );
				$avatar = get_avatar_url( $post->post_author );
				$thumb  = get_the_post_thumbnail_url( $vid_id, 'medium_large' );
				$vgames = get_the_terms( $vid_id, 'cp_game' );
				$views  = absint( get_post_meta( $vid_id, '_cpwp_views', true ) );
			?>
			<article class="cp-twitch-video-card">
				<a href="<?php the_permalink(); ?>" class="cp-twitch-video-thumb">
					<?php if ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy"><?php endif; ?>
					<span class="cp-twitch-live-badge"><?php esc_html_e( 'LIVE', 'cp-theme' ); ?></span>
					<span class="cp-twitch-viewers-badge"><?php echo number_format_i18n( $views ?: rand(100,5000) ); ?> <?php esc_html_e( 'viewers', 'cp-theme' ); ?></span>
				</a>
				<div class="cp-twitch-video-info">
					<img src="<?php echo esc_url( $avatar ); ?>" alt="" class="cp-twitch-avatar">
					<div class="cp-twitch-video-meta">
						<h3 class="cp-twitch-video-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="cp-twitch-video-author"><?php echo esc_html( $author ); ?></p>
						<?php if ( ! empty( $vgames ) ) : ?>
						<a href="<?php echo esc_url( get_term_link( $vgames[0] ) ); ?>" class="cp-twitch-game-link"><?php echo esc_html( $vgames[0]->name ); ?></a>
						<?php endif; ?>
						<div class="cp-twitch-tags"><span class="cp-twitch-tag">English</span></div>
					</div>
				</div>
			</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<!-- Pagination -->
		<?php if ( $query->max_num_pages > 1 ) : ?>
		<nav class="cp-twitch-pagination">
			<?php for ( $p = 1; $p <= $query->max_num_pages; $p++ ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'cp_page', $p ) ); ?>" class="cp-twitch-page-btn <?php echo $p === $filter_page ? 'is-active' : ''; ?>"><?php echo esc_html( $p ); ?></a>
			<?php endfor; ?>
		</nav>
		<?php endif; ?>

		<?php else : ?>
		<div class="cp-twitch-empty">
			<p><?php esc_html_e( 'No streams found matching your criteria.', 'cp-theme' ); ?></p>
		</div>
		<?php endif; ?>

	</div>
</div>

<?php get_footer(); ?>
