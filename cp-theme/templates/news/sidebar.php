<?php
/**
 * Template: News — Sidebar
 * Navigation for news sections, topics, locations.
 */
if ( ! is_user_logged_in() ) return;

$topics = get_terms( array( 'taxonomy' => 'cp_topic', 'hide_empty' => true, 'number' => 8, 'orderby' => 'count', 'order' => 'DESC' ) );
$locations = get_terms( array( 'taxonomy' => 'cp_location', 'hide_empty' => true, 'number' => 5, 'orderby' => 'count', 'order' => 'DESC' ) );
?>
<aside class="cp-sidebar-logged-in cp-news-sidebar">
	
	<nav class="cp-news-sidebar-nav">
		
		<div class="cp-news-nav-section">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cp-news-nav-link is-active">
				<span class="cp-news-nav-icon">📰</span>
				<?php esc_html_e( 'Home', 'cp-theme' ); ?>
			</a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_news' ) ); ?>" class="cp-news-nav-link">
				<span class="cp-news-nav-icon">⚡</span>
				<?php esc_html_e( 'Latest Stories', 'cp-theme' ); ?>
			</a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>" class="cp-news-nav-link">
				<span class="cp-news-nav-icon">▶</span>
				<?php esc_html_e( 'Video News', 'cp-theme' ); ?>
			</a>
		</div>

		<div class="cp-news-nav-divider"></div>

		<?php if ( ! empty( $topics ) && ! is_wp_error( $topics ) ) : ?>
		<div class="cp-news-nav-section">
			<span class="cp-news-nav-label"><?php esc_html_e( 'Top Sections', 'cp-theme' ); ?></span>
			<?php foreach ( $topics as $topic ) : ?>
			<a href="<?php echo esc_url( get_term_link( $topic ) ); ?>" class="cp-news-nav-link cp-news-nav-term">
				<?php echo esc_html( $topic->name ); ?>
			</a>
			<?php endforeach; ?>
		</div>
		<div class="cp-news-nav-divider"></div>
		<?php endif; ?>

		<?php if ( ! empty( $locations ) && ! is_wp_error( $locations ) ) : ?>
		<div class="cp-news-nav-section">
			<span class="cp-news-nav-label"><?php esc_html_e( 'World & Local', 'cp-theme' ); ?></span>
			<?php foreach ( $locations as $loc ) : ?>
			<a href="<?php echo esc_url( get_term_link( $loc ) ); ?>" class="cp-news-nav-link cp-news-nav-term">
				📍 <?php echo esc_html( $loc->name ); ?>
			</a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

	</nav>

</aside>
