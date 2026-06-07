<?php
/**
 * Template: Courses — Lesson Watch Page (single-video)
 * Full-width lesson player with a sticky curriculum sidebar on the right.
 * Loaded by the single_template filter for cp_lesson post type.
 */
get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-udemy-content">
		<?php while ( have_posts() ) : the_post();
			$lesson_id  = get_the_ID();
			$course_id  = wp_get_post_parent_id( $lesson_id );
			$user_id    = get_current_user_id();

			// Completion state.
			$completed_ids = ( $user_id && class_exists( 'CPWP_Learning' ) ) ? (array) CPWP_Learning::lesson_completions( $user_id ) : array();
			$is_complete   = in_array( $lesson_id, $completed_ids, true );

			// Build ordered sibling list (lessons + quizzes in the same course).
			$siblings = ( $course_id && class_exists( 'CPWP_Site_Modules' ) )
				? CPWP_Site_Modules::children( $course_id, array( 'cp_lesson', 'cp_quiz' ) )
				: array();

			// Sort by _cpwp_order meta.
			usort( $siblings, function( $a, $b ) {
				$oa = (int) get_post_meta( $a->ID, '_cpwp_order', true );
				$ob = (int) get_post_meta( $b->ID, '_cpwp_order', true );
				return $oa <=> $ob;
			} );

			// Find prev / next in sibling list.
			$prev_url = '';
			$next_url = '';
			$current_index = null;
			foreach ( $siblings as $idx => $sib ) {
				if ( $sib->ID === $lesson_id ) {
					$current_index = $idx;
					break;
				}
			}
			if ( null !== $current_index ) {
				if ( isset( $siblings[ $current_index - 1 ] ) ) $prev_url = get_permalink( $siblings[ $current_index - 1 ]->ID );
				if ( isset( $siblings[ $current_index + 1 ] ) ) $next_url = get_permalink( $siblings[ $current_index + 1 ]->ID );
			}
		?>
		<article class="cp-udemy-lesson-layout">

			<!-- Player / content column -->
			<div class="cp-udemy-lesson-main">

				<!-- Video player -->
				<div class="cp-udemy-player-wrap">
					<?php the_content(); ?>
				</div>

				<!-- Lesson title -->
				<h1 class="cp-udemy-lesson-title"><?php the_title(); ?></h1>

				<!-- Mark-complete button -->
				<?php if ( is_user_logged_in() ) : ?>
				<div class="cp-udemy-lesson-actions">
					<button
						class="cp-button cp-udemy-complete-btn <?php echo $is_complete ? 'is-complete' : ''; ?>"
						data-cpwp-complete-lesson="<?php echo esc_attr( $lesson_id ); ?>"
						<?php disabled( $is_complete, true ); ?>
					>
						<?php if ( $is_complete ) : ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
						<?php esc_html_e( 'Lesson Completed', 'cp-theme' ); ?>
						<?php else : ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
						<?php esc_html_e( 'Mark as Complete', 'cp-theme' ); ?>
						<?php endif; ?>
					</button>

					<!-- Prev / Next navigation -->
					<div class="cp-udemy-lesson-nav">
						<?php if ( $prev_url ) : ?>
						<a href="<?php echo esc_url( $prev_url ); ?>" class="cp-udemy-lesson-nav-btn cp-udemy-prev-btn">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
							<?php esc_html_e( 'Previous Lesson', 'cp-theme' ); ?>
						</a>
						<?php endif; ?>
						<?php if ( $next_url ) : ?>
						<a href="<?php echo esc_url( $next_url ); ?>" class="cp-udemy-lesson-nav-btn cp-udemy-next-btn">
							<?php esc_html_e( 'Next Lesson', 'cp-theme' ); ?>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
						</a>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( $course_id ) : ?>
				<div class="cp-udemy-lesson-breadcrumb">
					<a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>"><?php echo esc_html( get_the_title( $course_id ) ); ?></a>
					<span aria-hidden="true"> › </span>
					<span><?php the_title(); ?></span>
				</div>
				<?php endif; ?>
			</div><!-- .cp-udemy-lesson-main -->

			<!-- Curriculum sidebar -->
			<aside class="cp-udemy-curriculum-aside">
				<h2 class="cp-udemy-curriculum-heading">
					<?php esc_html_e( 'Course Content', 'cp-theme' ); ?>
					<?php if ( $course_id ) : ?>
					<a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>" class="cp-udemy-curriculum-course-link"><?php echo esc_html( get_the_title( $course_id ) ); ?></a>
					<?php endif; ?>
				</h2>
				<ol class="cp-udemy-curriculum-list">
					<?php foreach ( $siblings as $sib ) :
						$sib_id      = $sib->ID;
						$sib_done    = in_array( $sib_id, $completed_ids, true );
						$is_current  = ( $sib_id === $lesson_id );
						$sib_order   = get_post_meta( $sib_id, '_cpwp_order', true );
						$sib_type    = $sib->post_type;
					?>
					<li class="cp-udemy-curriculum-item <?php echo $is_current ? 'is-current' : ''; ?> <?php echo $sib_done ? 'is-done' : ''; ?>">
						<a href="<?php echo esc_url( get_permalink( $sib_id ) ); ?>" <?php if ( $is_current ) echo 'aria-current="step"'; ?>>
							<span class="cp-udemy-curriculum-check" aria-hidden="true">
								<?php if ( $sib_done ) : ?>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
								<?php else : ?>
								<?php echo $sib_order ? esc_html( $sib_order ) : '•'; ?>
								<?php endif; ?>
							</span>
							<span class="cp-udemy-curriculum-item-title"><?php echo esc_html( get_the_title( $sib_id ) ); ?></span>
							<?php if ( 'cp_quiz' === $sib_type ) : ?>
							<span class="cp-udemy-curriculum-type-badge">Quiz</span>
							<?php endif; ?>
						</a>
					</li>
					<?php endforeach; ?>
				</ol>
			</aside><!-- .cp-udemy-curriculum-aside -->

		</article>
		<?php endwhile; ?>
	</div><!-- .cp-page-content -->
</div><!-- .cp-shell -->

<?php get_footer(); ?>
