<?php
/**
 * Template: Courses — Homepage
 * Udemy-style homepage with hero search banner, "Continue Learning" row,
 * and a featured-courses grid. Loaded by the site-type router in front-page.php.
 */

$user_id      = get_current_user_id();
$is_logged_in = is_user_logged_in();

// Build "Continue Learning" data for logged-in users.
$continue_courses = array();
if ( $is_logged_in && class_exists( 'CPWP_Learning' ) ) {
	$enrolled = CPWP_Learning::enrolled_courses( $user_id );
	foreach ( $enrolled as $cid ) {
		$progress = CPWP_Learning::course_progress( $user_id, $cid );
		if ( $progress > 0 && $progress < 100 ) {
			$continue_courses[] = array(
				'id'       => $cid,
				'progress' => $progress,
			);
		}
	}
}

// Featured courses: latest 8 cp_course posts.
$featured_courses = get_posts( array(
	'post_type'      => 'cp_course',
	'posts_per_page' => 8,
	'post_status'    => 'publish',
) );

$catalog_url = get_post_type_archive_link( 'cp_course' );

get_header();
?>

<?php if ( $is_logged_in ) : ?>
<div class="cp-shell cp-page-layout-with-sidebar">
	<?php get_template_part( 'sidebar', 'logged-in' ); ?>
	<div class="cp-page-content cp-udemy-content">
<?php else : ?>
<div class="cp-shell">
	<div class="cp-page-content cp-udemy-content">
<?php endif; ?>

		<!-- Hero Banner -->
		<section class="cp-udemy-hero">
			<div class="cp-udemy-hero-inner">
				<h1 class="cp-udemy-hero-title"><?php esc_html_e( 'Expand your skills', 'cp-theme' ); ?></h1>
				<p class="cp-udemy-hero-subtitle"><?php esc_html_e( 'Learn from expert-curated courses and earn certificates that matter.', 'cp-theme' ); ?></p>
				<form class="cp-udemy-search-form" role="search" method="get" action="<?php echo esc_url( $catalog_url ); ?>">
					<input
						type="search"
						name="s"
						class="cp-udemy-search-input"
						placeholder="<?php esc_attr_e( 'Search for anything…', 'cp-theme' ); ?>"
						value="<?php echo esc_attr( get_search_query() ); ?>"
						aria-label="<?php esc_attr_e( 'Search courses', 'cp-theme' ); ?>"
					>
					<button type="submit" class="cp-udemy-search-btn" aria-label="<?php esc_attr_e( 'Search', 'cp-theme' ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
						<?php esc_html_e( 'Search', 'cp-theme' ); ?>
					</button>
				</form>
			</div>
		</section>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_hero' ); ?>

		<!-- Continue Learning Row (logged-in + in-progress courses) -->
		<?php if ( $is_logged_in && ! empty( $continue_courses ) ) : ?>
		<section class="cp-udemy-section cp-udemy-continue-section">
			<div class="cp-udemy-section-head">
				<h2><?php esc_html_e( 'Continue Learning', 'cp-theme' ); ?></h2>
			</div>
			<div class="cp-udemy-continue-row">
				<?php foreach ( $continue_courses as $cc ) :
					$cid      = $cc['id'];
					$progress = $cc['progress'];
					$thumb    = get_the_post_thumbnail_url( $cid, 'medium_large' );
					$lessons  = class_exists( 'CPWP_Site_Modules' ) ? CPWP_Site_Modules::children( $cid, array( 'cp_lesson', 'cp_quiz' ) ) : array();
				?>
				<a href="<?php echo esc_url( get_permalink( $cid ) ); ?>" class="cp-udemy-continue-card">
					<div class="cp-udemy-continue-thumb">
						<?php if ( $thumb ) : ?>
						<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
						<?php else : ?>
						<div class="cp-udemy-thumb-placeholder">📚</div>
						<?php endif; ?>
					</div>
					<div class="cp-udemy-continue-info">
						<h3><?php echo esc_html( get_the_title( $cid ) ); ?></h3>
						<div class="cp-udemy-progress-wrap">
							<div class="cp-udemy-progress-bar" role="progressbar" aria-valuenow="<?php echo esc_attr( $progress ); ?>" aria-valuemin="0" aria-valuemax="100">
								<div class="cp-udemy-progress-fill" style="width:<?php echo esc_attr( $progress ); ?>%"></div>
							</div>
							<span class="cp-udemy-progress-label"><?php echo esc_html( $progress ); ?>% <?php esc_html_e( 'complete', 'cp-theme' ); ?></span>
						</div>
						<?php if ( $lessons ) : ?>
						<small class="cp-udemy-lesson-count"><?php echo esc_html( count( $lessons ) ); ?> <?php esc_html_e( 'lessons', 'cp-theme' ); ?></small>
						<?php endif; ?>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<!-- Featured Courses Grid -->
		<?php if ( $featured_courses ) : ?>
		<section class="cp-udemy-section">
			<div class="cp-udemy-section-head">
				<h2><?php esc_html_e( 'Featured Courses', 'cp-theme' ); ?></h2>
				<a class="cp-udemy-section-link" href="<?php echo esc_url( $catalog_url ); ?>"><?php esc_html_e( 'Browse all courses', 'cp-theme' ); ?> →</a>
			</div>
			<div class="cp-udemy-course-grid">
				<?php foreach ( $featured_courses as $course ) :
					$cid      = $course->ID;
					$thumb    = get_the_post_thumbnail_url( $cid, 'medium_large' );
					$lessons  = class_exists( 'CPWP_Site_Modules' ) ? CPWP_Site_Modules::children( $cid, array( 'cp_lesson', 'cp_quiz' ) ) : array();
					$enrolled = $is_logged_in && class_exists( 'CPWP_Learning' ) && in_array( $cid, CPWP_Learning::enrolled_courses( $user_id ), true );
					$progress = ( $enrolled && class_exists( 'CPWP_Learning' ) ) ? CPWP_Learning::course_progress( $user_id, $cid ) : 0;
					$author   = get_the_author_meta( 'display_name', $course->post_author );
				?>
				<article class="cp-udemy-course-card">
					<a href="<?php echo esc_url( get_permalink( $cid ) ); ?>" class="cp-udemy-card-thumb-link" tabindex="-1" aria-hidden="true">
						<div class="cp-udemy-card-thumb">
							<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
							<?php else : ?>
							<div class="cp-udemy-thumb-placeholder">📚</div>
							<?php endif; ?>
						</div>
					</a>
					<div class="cp-udemy-card-body">
						<h3 class="cp-udemy-card-title">
							<a href="<?php echo esc_url( get_permalink( $cid ) ); ?>"><?php echo esc_html( get_the_title( $cid ) ); ?></a>
						</h3>
						<?php if ( $author ) : ?>
						<p class="cp-udemy-card-instructor"><?php echo esc_html( $author ); ?></p>
						<?php endif; ?>
						<?php if ( $lessons ) : ?>
						<p class="cp-udemy-card-meta">
							<span class="cp-udemy-card-lesson-count">
								📖 <?php echo esc_html( count( $lessons ) ); ?> <?php esc_html_e( 'lessons', 'cp-theme' ); ?>
							</span>
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
							<a href="<?php echo esc_url( get_permalink( $cid ) ); ?>" class="cp-button cp-udemy-continue-btn"><?php esc_html_e( 'Continue', 'cp-theme' ); ?></a>
							<?php elseif ( $is_logged_in ) : ?>
							<button class="cp-button cp-udemy-enroll-btn" data-cpwp-enroll-course="<?php echo esc_attr( $cid ); ?>"><?php esc_html_e( 'Enroll', 'cp-theme' ); ?></button>
							<?php else : ?>
							<a href="<?php echo esc_url( get_permalink( $cid ) ); ?>" class="cp-button"><?php esc_html_e( 'View Course', 'cp-theme' ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_grid' ); ?>

	</div><!-- .cp-page-content -->
</div><!-- .cp-shell -->

<?php get_footer(); ?>
