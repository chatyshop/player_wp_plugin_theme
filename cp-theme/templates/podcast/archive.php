<?php
/**
 * Template: Podcast — Archive
 * Show artwork grid (Search / All Shows).
 */

$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$paged  = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$args = array(
	'post_type'      => 'cp_series',
	'posts_per_page' => 24,
	'paged'          => $paged,
);

if ( $search ) {
	$args['s'] = $search;
}

$query = new WP_Query( $args );

get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-podcast-content">
		
		<div class="cp-podcast-archive-head">
			<form class="cp-podcast-search" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'cp_series' ) ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
				<input type="text" name="s" placeholder="<?php esc_attr_e( 'What do you want to listen to?', 'cp-theme' ); ?>" value="<?php echo esc_attr( $search ); ?>">
			</form>
		</div>

		<h2><?php echo $search ? esc_html__( 'Search Results', 'cp-theme' ) : esc_html__( 'Browse all shows', 'cp-theme' ); ?></h2>

		<?php if ( $query->have_posts() ) : ?>
		<div class="cp-podcast-show-grid" style="margin-top:24px;">
			<?php while ( $query->have_posts() ) : $query->the_post();
				$pid    = get_the_ID();
				$thumb  = get_the_post_thumbnail_url( $pid, 'medium_large' );
				$author = get_the_author_meta( 'display_name' );
			?>
			<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>" class="cp-podcast-show-card">
				<div class="cp-podcast-show-thumb">
					<?php if ( $thumb ) : ?>
					<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
					<?php else : ?>
					<div class="cp-podcast-placeholder">🎙️</div>
					<?php endif; ?>
				</div>
				<div class="cp-podcast-show-info">
					<h3><?php the_title(); ?></h3>
					<p><?php echo esc_html( $author ); ?></p>
				</div>
			</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<?php if ( $query->max_num_pages > 1 ) : ?>
		<div class="cp-podcast-pagination">
			<?php echo paginate_links( array( 'total' => $query->max_num_pages, 'prev_text' => '←', 'next_text' => '→' ) ); ?>
		</div>
		<?php endif; ?>

		<?php else : ?>
		<div class="cp-podcast-empty">
			<p><?php esc_html_e( 'No shows found.', 'cp-theme' ); ?></p>
		</div>
		<?php endif; ?>

	</div>
</div>
<?php get_footer(); ?>
