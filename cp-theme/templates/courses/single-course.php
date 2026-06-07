<?php
/**
 * Template: Courses — Course Detail Page (single-course.php)
 * Udemy-style course landing page loaded via the single_template filter
 * for the cp_course post type.
 *
 * Sections:
 *   - Hero: thumbnail backdrop, title, instructor, enroll button, progress
 *   - Tab bar: Overview | Curriculum
 *   - Overview: post description + excerpt bullet points
 *   - Curriculum: ordered lesson/quiz list with completion marks
 *   - Instructor card
 */

get_header();
?>

<?php while ( have_posts() ) : the_post();
	$course_id   = get_the_ID();
	$user_id     = get_current_user_id();
	$is_logged   = is_user_logged_in();
	$author_id   = (int) get_post_field( 'post_author', $course_id );
	$author_name = get_the_author_meta( 'display_name', $author_id );
	$author_bio  = get_the_author_meta( 'description',  $author_id );

	// Enrollment & progress.
	$enrolled     = false;
	$assigned     = false;
	$progress     = 0;
	$completed_lessons = array();
	if ( $is_logged && class_exists( 'CPWP_Learning' ) ) {
		$enrolled  = in_array( $course_id, CPWP_Learning::enrolled_courses( $user_id ), true );
		$assigned  = in_array( $course_id, CPWP_Learning::assigned_courses( $user_id ), true );
		$progress  = CPWP_Learning::course_progress( $user_id, $course_id );
		$completed_lessons = CPWP_Learning::lesson_completions( $user_id );
	}

	// Curriculum (lessons + quizzes).
	$curriculum = class_exists( 'CPWP_Site_Modules' ) ? CPWP_Site_Modules::children( $course_id, array( 'cp_lesson', 'cp_quiz' ) ) : array();
	usort( $curriculum, function( $a, $b ) {
		return (int) get_post_meta( $a->ID, '_cpwp_order', true ) <=> (int) get_post_meta( $b->ID, '_cpwp_order', true );
	} );

	// Hero thumbnail.
	$hero_thumb   = get_the_post_thumbnail_url( $course_id, 'full' );
	$lesson_count = count( $curriculum );
?>

<div class="cp-shell <?php echo $is_logged ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( $is_logged ) get_template_part( 'sidebar', 'logged-in' ); ?>
	<div class="cp-page-content cp-udemy-content">

		<!-- Course Hero -->
		<header class="cp-udemy-course-hero" <?php if ( $hero_thumb ) printf( 'style="--cp-course-hero-bg:url(\'%s\')"', esc_url( $hero_thumb ) ); ?>>
			<div class="cp-udemy-course-hero-overlay"></div>
			<div class="cp-udemy-course-hero-content">
				<h1 class="cp-udemy-course-hero-title"><?php the_title(); ?></h1>
				<?php if ( $author_name ) : ?>
				<p class="cp-udemy-course-hero-instructor">
					<?php esc_html_e( 'by', 'cp-theme' ); ?> <strong><?php echo esc_html( $author_name ); ?></strong>
				</p>
				<?php endif; ?>
				<?php if ( $lesson_count ) : ?>
				<p class="cp-udemy-course-hero-meta">
					📖 <?php echo esc_html( $lesson_count ); ?> <?php esc_html_e( 'lessons', 'cp-theme' ); ?>
				</p>
				<?php endif; ?>

				<?php if ( $enrolled || $assigned ) : ?>
				<!-- Enrolled / progress state -->
				<div class="cp-udemy-course-hero-actions">
					<?php if ( ! empty( $curriculum ) ) : ?>
					<a href="<?php echo esc_url( get_permalink( $curriculum[0]->ID ) ); ?>" class="cp-button cp-udemy-hero-btn">
						<?php esc_html_e( 'Continue Learning', 'cp-theme' ); ?>
					</a>
					<?php endif; ?>
					<div class="cp-udemy-hero-progress-wrap">
						<div class="cp-udemy-progress-bar" role="progressbar" aria-valuenow="<?php echo esc_attr( $progress ); ?>" aria-valuemin="0" aria-valuemax="100">
							<div class="cp-udemy-progress-fill" style="width:<?php echo esc_attr( $progress ); ?>%"></div>
						</div>
						<span class="cp-udemy-progress-label"><?php echo esc_html( $progress ); ?>% <?php esc_html_e( 'complete', 'cp-theme' ); ?></span>
					</div>
				</div>
				<?php elseif ( $is_logged ) : ?>
				<div class="cp-udemy-course-hero-actions">
					<button class="cp-button cp-udemy-hero-btn" data-cpwp-enroll-course="<?php echo esc_attr( $course_id ); ?>">
						<?php esc_html_e( 'Enroll Now — It\'s Free', 'cp-theme' ); ?>
					</button>
				</div>
				<?php else : ?>
				<div class="cp-udemy-course-hero-actions">
					<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="cp-button cp-udemy-hero-btn">
						<?php esc_html_e( 'Login to Enroll', 'cp-theme' ); ?>
					</a>
				</div>
				<?php endif; ?>
			</div><!-- .cp-udemy-course-hero-content -->
		</header>

		<!-- Tab Navigation -->
		<div class="cp-udemy-tabs" role="tablist">
			<button class="cp-udemy-tab is-active" id="tab-overview" role="tab" aria-controls="panel-overview" aria-selected="true" data-tab="overview">
				<?php esc_html_e( 'Overview', 'cp-theme' ); ?>
			</button>
			<button class="cp-udemy-tab" id="tab-curriculum" role="tab" aria-controls="panel-curriculum" aria-selected="false" data-tab="curriculum">
				<?php esc_html_e( 'Curriculum', 'cp-theme' ); ?>
			</button>
		</div>

		<!-- Overview Panel -->
		<div class="cp-udemy-tab-panel is-active" id="panel-overview" role="tabpanel" aria-labelledby="tab-overview">

			<?php if ( has_excerpt() ) : ?>
			<section class="cp-udemy-learn-section">
				<h2><?php esc_html_e( 'What you\'ll learn', 'cp-theme' ); ?></h2>
				<ul class="cp-udemy-learn-list">
					<?php
					$excerpt_raw = get_the_excerpt();
					$bullets     = array_filter( array_map( 'trim', preg_split( '/\r?\n|[.!?]+(?=\s|$)/', $excerpt_raw ) ) );
					foreach ( array_slice( $bullets, 0, 8 ) as $bullet ) :
					?>
					<li>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
						<?php echo esc_html( $bullet ); ?>
					</li>
					<?php endforeach; ?>
				</ul>
			</section>
			<?php endif; ?>

			<section class="cp-udemy-description">
				<h2><?php esc_html_e( 'About this course', 'cp-theme' ); ?></h2>
				<div class="cp-content"><?php the_content(); ?></div>
			</section>
		</div>

		<!-- Curriculum Panel -->
		<div class="cp-udemy-tab-panel" id="panel-curriculum" role="tabpanel" aria-labelledby="tab-curriculum" hidden>
			<section class="cp-udemy-curriculum-section">
				<h2>
					<?php
					/* translators: %d = lesson count */
					printf( esc_html( _n( '%d Lesson', '%d Lessons', $lesson_count, 'cp-theme' ) ), (int) $lesson_count );
					?>
				</h2>
				<?php if ( $curriculum ) : ?>
				<ol class="cp-udemy-curriculum-list">
					<?php foreach ( $curriculum as $item ) :
						$item_id   = $item->ID;
						$is_done   = in_array( $item_id, $completed_lessons, true );
						$item_order= get_post_meta( $item_id, '_cpwp_order', true );
						$item_type = $item->post_type;
					?>
					<li class="cp-udemy-curriculum-item <?php echo $is_done ? 'is-done' : ''; ?>">
						<a href="<?php echo esc_url( get_permalink( $item_id ) ); ?>">
							<span class="cp-udemy-curriculum-check" aria-hidden="true">
								<?php if ( $is_done ) : ?>
								<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
								<?php else : ?>
								<?php echo $item_order ? esc_html( $item_order ) : '•'; ?>
								<?php endif; ?>
							</span>
							<span class="cp-udemy-curriculum-item-title"><?php echo esc_html( get_the_title( $item_id ) ); ?></span>
							<?php if ( 'cp_quiz' === $item_type ) : ?>
							<span class="cp-udemy-curriculum-type-badge">Quiz</span>
							<?php endif; ?>
						</a>
					</li>
					<?php endforeach; ?>
				</ol>
				<?php else : ?>
				<p class="cp-udemy-empty-note"><?php esc_html_e( 'Curriculum content coming soon.', 'cp-theme' ); ?></p>
				<?php endif; ?>
			</section>
		</div>

		<!-- Instructor Card -->
		<?php if ( $author_name ) : ?>
		<section class="cp-udemy-instructor-card">
			<h2><?php esc_html_e( 'Your Instructor', 'cp-theme' ); ?></h2>
			<div class="cp-udemy-instructor-inner">
				<?php echo get_avatar( $author_id, 80, '', '', array( 'class' => 'cp-udemy-instructor-avatar' ) ); ?>
				<div class="cp-udemy-instructor-info">
					<strong class="cp-udemy-instructor-name"><?php echo esc_html( $author_name ); ?></strong>
					<?php if ( $author_bio ) : ?>
					<p class="cp-udemy-instructor-bio"><?php echo esc_html( $author_bio ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php endif; ?>

	</div><!-- .cp-page-content -->
</div><!-- .cp-shell -->

<?php endwhile; ?>

<script>
(function(){
	var tabs = document.querySelectorAll('.cp-udemy-tab');
	tabs.forEach(function(tab){
		tab.addEventListener('click', function(){
			tabs.forEach(function(t){ t.classList.remove('is-active'); t.setAttribute('aria-selected','false'); });
			document.querySelectorAll('.cp-udemy-tab-panel').forEach(function(p){ p.classList.remove('is-active'); p.hidden=true; });
			tab.classList.add('is-active');
			tab.setAttribute('aria-selected','true');
			var panel = document.getElementById('panel-'+tab.dataset.tab);
			if(panel){ panel.classList.add('is-active'); panel.hidden=false; }
		});
	});
})();
</script>

<?php get_footer(); ?>
