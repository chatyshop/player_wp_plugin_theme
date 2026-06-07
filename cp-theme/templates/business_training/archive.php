<?php
/**
 * Template: Business Training — Archive
 * Company library grid.
 */

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$args  = array(
	'post_type'      => 'cp_video',
	'posts_per_page' => 16,
	'paged'          => $paged,
);
$query = new WP_Query( $args );

get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-business-content">
		
		<?php if ( ! is_user_logged_in() ) : ?>
		<div class="cp-business-empty-state">
			<h2><?php esc_html_e( 'Employee Login Required', 'cp-theme' ); ?></h2>
			<p><?php esc_html_e( 'The company library is restricted to authorized personnel only.', 'cp-theme' ); ?></p>
			<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="cp-business-btn cp-business-btn--primary"><?php esc_html_e( 'Log In', 'cp-theme' ); ?></a>
		</div>
		<?php else : ?>

		<div class="cp-business-archive-header">
			<h1><?php esc_html_e( 'Company Training Library', 'cp-theme' ); ?></h1>
			<form class="cp-business-archive-search-form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="hidden" name="post_type" value="cp_video">
				<input type="text" name="s" placeholder="<?php esc_attr_e( 'Search the library...', 'cp-theme' ); ?>">
				<button type="submit" class="cp-business-btn cp-business-btn--primary"><?php esc_html_e( 'Search', 'cp-theme' ); ?></button>
			</form>
		</div>

		<div class="cp-business-archive-body">
			<?php if ( $query->have_posts() ) : ?>
			<div class="cp-business-archive-grid">
				<?php while ( $query->have_posts() ) : $query->the_post();
					$vid     = get_the_ID();
					$thumb   = get_the_post_thumbnail_url( $vid, 'medium_large' );
				?>
				<a href="<?php echo esc_url( get_permalink( $vid ) ); ?>" class="cp-business-archive-card">
					<?php if ( $thumb ) : ?>
						<img src="<?php echo esc_url( $thumb ); ?>" alt="" class="cp-business-archive-card__thumb" loading="lazy">
					<?php else : ?>
						<div class="cp-business-archive-card__thumb" style="display:flex;align-items:center;justify-content:center;color:#94a3b8;background:linear-gradient(135deg, #dde3ef, #c5cfe4);">📘</div>
					<?php endif; ?>
					<div class="cp-business-archive-card__body">
						<h3 class="cp-business-archive-card__title"><?php the_title(); ?></h3>
						<span class="cp-business-archive-card__meta"><?php echo esc_html( get_the_date() ); ?></span>
					</div>
				</a>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>

			<?php if ( $query->max_num_pages > 1 ) : ?>
			<div style="margin-top: 32px;">
				<?php echo paginate_links( array( 'total' => $query->max_num_pages ) ); ?>
			</div>
			<?php endif; ?>

			<?php else : ?>
			<div class="cp-business-empty-state">
				<p><?php esc_html_e( 'No training modules found.', 'cp-theme' ); ?></p>
			</div>
			<?php endif; ?>
		</div>

		<?php endif; ?>
	</div>
</div>
<?php get_footer(); ?>
