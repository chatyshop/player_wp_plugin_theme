<?php
/**
 * Template Name: Watch Later
 */

get_header(); ?>

<div class="cp-shell cp-page-layout-with-sidebar">
	<?php 
	if ( is_user_logged_in() ) {
		get_template_part( 'sidebar', 'logged-in' );
	}
	?>
	<div class="cp-page-content">
		<header class="cp-section-head">
			<div>
				<span class="cp-kicker"><?php esc_html_e( 'Library', 'cp-theme' ); ?></span>
				<h1 class="cp-page-title"><?php esc_html_e( 'Watch Later', 'cp-theme' ); ?></h1>
			</div>
		</header>

		<?php
		if ( ! is_user_logged_in() ) {
			?>
			<div class="cp-empty">
				<p><?php esc_html_e( 'You must be logged in to view your Watch Later list.', 'cp-theme' ); ?></p>
				<a class="cp-button" href="<?php echo esc_url( add_query_arg( 'cpwp_auth', 'login', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log In', 'cp-theme' ); ?></a>
			</div>
			<?php
		} else {
			$user_id = get_current_user_id();
			$watch_later_ids = get_user_meta( $user_id, '_cpwp_watch_later', true );
			
			if ( empty( $watch_later_ids ) || ! is_array( $watch_later_ids ) ) {
				?>
				<div class="cp-empty">
					<p><?php esc_html_e( 'Your Watch Later list is empty.', 'cp-theme' ); ?></p>
					<a class="cp-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Explore Videos', 'cp-theme' ); ?></a>
				</div>
				<?php
			} else {
				$query = new WP_Query( array(
					'post_type'      => 'cp_video',
					'post__in'       => $watch_later_ids,
					'orderby'        => 'post__in',
					'posts_per_page' => -1,
				) );

				if ( $query->have_posts() ) {
					?>
					<div class="cp-theme-grid">
						<?php while ( $query->have_posts() ) : $query->the_post(); ?>
							<?php cp_theme_video_card( get_the_ID() ); ?>
						<?php endwhile; ?>
					</div>
					<?php
				} else {
					?>
					<div class="cp-empty">
						<p><?php esc_html_e( 'No videos found.', 'cp-theme' ); ?></p>
					</div>
					<?php
				}
				wp_reset_postdata();
			}
		}
		?>
	</div>
</div>

<?php get_footer(); ?>
