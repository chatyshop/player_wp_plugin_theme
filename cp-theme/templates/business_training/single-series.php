<?php
/**
 * Template: Business Training — Single Series (Department / Learning Path)
 * Shows the course/module list for a department or training track.
 */
get_header();

$series    = get_queried_object();
$thumb_url = get_the_post_thumbnail_url( $series->ID, 'full' );
$excerpt   = get_the_excerpt( $series );

// Fetch all videos (lessons/modules) in this series.
$modules = get_posts( array(
	'post_type'      => 'cp_video',
	'posts_per_page' => -1,
	'meta_query'     => array(
		array(
			'key'   => 'cp_series_id',
			'value' => $series->ID,
		),
	),
	'orderby'        => 'menu_order',
	'order'          => 'ASC',
) );

$total_modules    = count( $modules );
$total_duration   = 0;
foreach ( $modules as $m ) {
	$total_duration += (int) get_post_meta( $m->ID, 'cp_video_duration', true );
}
$hours   = floor( $total_duration / 3600 );
$minutes = floor( ( $total_duration % 3600 ) / 60 );
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) : ?>
		<?php get_template_part( 'templates/business_training/sidebar' ); ?>
	<?php endif; ?>

	<main class="cp-main">

		<!-- Hero Banner -->
		<section class="cbt-series-hero" <?php if ( $thumb_url ) : ?>style="background-image:url('<?php echo esc_url( $thumb_url ); ?>')"<?php endif; ?>>
			<div class="cbt-series-hero__overlay">
				<div class="cbt-series-hero__content">
					<span class="cbt-badge cbt-badge--dept">Learning Path</span>
					<h1 class="cbt-series-hero__title"><?php echo esc_html( get_the_title( $series ) ); ?></h1>
					<?php if ( $excerpt ) : ?>
						<p class="cbt-series-hero__excerpt"><?php echo esc_html( $excerpt ); ?></p>
					<?php endif; ?>
					<div class="cbt-series-hero__meta">
						<span class="cbt-meta-chip">
							<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
							<?php echo esc_html( $total_modules ); ?> Modules
						</span>
						<?php if ( $total_duration > 0 ) : ?>
						<span class="cbt-meta-chip">
							<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
							<?php echo $hours > 0 ? esc_html( $hours . 'h ' ) : ''; ?><?php echo esc_html( $minutes . 'm' ); ?>
						</span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>

		<!-- Module List -->
		<section class="cbt-module-list-section">
			<div class="cbt-container">
				<h2 class="cbt-section-title">Course Modules</h2>

				<?php if ( $modules ) : ?>
					<ol class="cbt-module-list">
						<?php
						$idx = 0;
						foreach ( $modules as $module ) :
							$idx++;
							$duration_sec = (int) get_post_meta( $module->ID, 'cp_video_duration', true );
							$m_min        = $duration_sec > 0 ? floor( $duration_sec / 60 ) . 'm' : '';
							$m_thumb      = get_the_post_thumbnail_url( $module->ID, 'thumbnail' );
							?>
							<li class="cbt-module-item">
								<span class="cbt-module-item__num"><?php echo esc_html( $idx ); ?></span>
								<?php if ( $m_thumb ) : ?>
									<img src="<?php echo esc_url( $m_thumb ); ?>" alt="" class="cbt-module-item__thumb" />
								<?php endif; ?>
								<div class="cbt-module-item__info">
									<a href="<?php the_permalink( $module->ID ); ?>" class="cbt-module-item__title">
										<?php echo esc_html( get_the_title( $module ) ); ?>
									</a>
									<p class="cbt-module-item__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $module ), 15 ) ); ?></p>
								</div>
								<div class="cbt-module-item__actions">
									<?php if ( $m_min ) : ?>
										<span class="cbt-module-item__duration"><?php echo esc_html( $m_min ); ?></span>
									<?php endif; ?>
									<a href="<?php the_permalink( $module->ID ); ?>" class="cbt-btn cbt-btn--sm cbt-btn--primary">Start</a>
								</div>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php else : ?>
					<div class="cbt-empty-state">
						<p>No modules available yet. Check back soon.</p>
					</div>
				<?php endif; ?>
			</div>
		</section>

	</main>
</div>
<?php get_footer(); ?>
