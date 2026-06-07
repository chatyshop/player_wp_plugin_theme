<?php
/**
 * Template Name: Favorites
 */

get_header(); ?>

<div class="cp-shell cp-page-layout-with-sidebar">
	<?php 
	if ( is_user_logged_in() ) {
		get_template_part( 'sidebar', 'logged-in' );
	}
	?>
	<div class="cp-page-content">
		<?php
		$user_id = get_current_user_id();
		$favorite_ids = is_user_logged_in() ? get_user_meta( $user_id, '_cpwp_favorites', true ) : array();
		$count = ! empty( $favorite_ids ) && is_array( $favorite_ids ) ? count( $favorite_ids ) : 0;
		?>
		<header class="cp-library-hero favorites-hero">
			<div class="cp-library-hero-icon">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
			</div>
			<div class="cp-library-hero-content">
				<span class="cp-kicker"><?php esc_html_e( 'Library', 'cp-theme' ); ?></span>
				<h1 class="cp-page-title"><?php esc_html_e( 'Favorites', 'cp-theme' ); ?></h1>
				<div class="cp-library-hero-meta">
					<span><?php printf( esc_html( _n( '%s video', '%s videos', $count, 'cp-theme' ) ), number_format_i18n( $count ) ); ?></span>
					<span>•</span>
					<span><?php esc_html_e( 'Private', 'cp-theme' ); ?></span>
				</div>
				<?php if ( $count > 0 ) : ?>
					<div class="cp-library-hero-actions">
						<a href="<?php echo esc_url( get_permalink( $favorite_ids[0] ) ); ?>" class="cp-button">
							<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
							<?php esc_html_e( 'Play All', 'cp-theme' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</header>

		<?php
		if ( ! is_user_logged_in() ) {
			?>
			<div class="cp-empty">
				<p><?php esc_html_e( 'You must be logged in to view your Favorites list.', 'cp-theme' ); ?></p>
				<a class="cp-button" href="<?php echo esc_url( add_query_arg( 'cpwp_auth', 'login', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log In', 'cp-theme' ); ?></a>
			</div>
			<?php
		} else {
			$user_id = get_current_user_id();
			$favorite_ids = get_user_meta( $user_id, '_cpwp_favorites', true );
			
			if ( empty( $favorite_ids ) || ! is_array( $favorite_ids ) ) {
				?>
				<div class="cp-empty">
					<p><?php esc_html_e( 'Your Favorites list is empty.', 'cp-theme' ); ?></p>
					<a class="cp-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Explore Videos', 'cp-theme' ); ?></a>
				</div>
				<?php
			} else {
				$query = new WP_Query( array(
					'post_type'      => 'cp_video',
					'post__in'       => $favorite_ids,
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
