<?php
/**
 * Template: Courses — Individual Lesson Page (single-lesson.php)
 * Full-width player layout for cp_lesson post type.
 * Loaded via the single_template filter.
 *
 * Features:
 *   - Breadcrumb: Course name → Lesson name
 *   - Lesson title (h1)
 *   - Video player (the_content())
 *   - Mark-complete button (data-cpwp-complete-lesson)
 *   - Green "Lesson completed" state if already done
 *   - Next lesson link
 *   - Progress note: "X of Y lessons complete in this course"
 */
get_header();
?>

<?php while ( have_posts() ) : the_post();
	$lesson_id = get_the_ID();
	$course_id = wp_get_post_parent_id( $lesson_id );
	$user_id   = get_current_user_id();
	$is_logged = is_user_logged_in();

	// Completion state.
	$completed_ids = ( $is_logged && class_exists( 'CPWP_Learning' ) ) ? (array) CPWP_Learning::lesson_completions( $user_id ) : array();
	$is_complete   = in_array( $lesson_id, $completed_ids, true );

	// Sibling lessons (ordered).
	$siblings = ( $course_id && class_exists( 'CPWP_Site_Modules' ) )
		? CPWP_Site_Modules::children( $course_id, array( 'cp_lesson', 'cp_quiz' ) )
		: array();

	usort( $siblings, function( $a, $b ) {
		return (int) get_post_meta( $a->ID, '_cpwp_order', true ) <=> (int) get_post_meta( $b->ID, '_cpwp_order', true );
	} );

	$total_lessons   = count( $siblings );
	$next_lesson_url = '';
	$current_index   = null;

	foreach ( $siblings as $idx => $sib ) {
		if ( $sib->ID === $lesson_id ) {
			$current_index = $idx;
			break;
		}
	}
	if ( null !== $current_index && isset( $siblings[ $current_index + 1 ] ) ) {
		$next_lesson_url = get_permalink( $siblings[ $current_index + 1 ]->ID );
	}

	// Completed lesson count for progress note.
	$lessons_done = 0;
	if ( $total_lessons > 0 ) {
		foreach ( $siblings as $sib ) {
			if ( in_array( $sib->ID, $completed_ids, true ) ) $lessons_done++;
		}
	}
?>

<div class="cp-shell cp-udemy-single-lesson-wrap">

	<!-- Breadcrumb -->
	<nav class="cp-udemy-lesson-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'cp-theme' ); ?>">
		<?php if ( $course_id ) : ?>
		<a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>"><?php echo esc_html( get_the_title( $course_id ) ); ?></a>
		<span aria-hidden="true"> › </span>
		<?php endif; ?>
		<span aria-current="page"><?php the_title(); ?></span>
	</nav>

	<article class="cp-udemy-single-lesson">

		<!-- Lesson Title -->
		<h1 class="cp-udemy-lesson-page-title"><?php the_title(); ?></h1>

		<!-- Video Player -->
		<div class="cp-udemy-lesson-player">
			<?php the_content(); ?>
		</div>

		<?php if ( $is_logged ) : ?>
		<!-- Completion State & Button -->
		<div class="cp-udemy-lesson-completion <?php echo $is_complete ? 'is-complete' : ''; ?>">
			<?php if ( $is_complete ) : ?>
			<div class="cp-udemy-complete-badge" role="status">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
				<?php esc_html_e( 'Lesson completed', 'cp-theme' ); ?>
			</div>
			<?php else : ?>
			<button
				class="cp-button cp-udemy-complete-btn"
				data-cpwp-complete-lesson="<?php echo esc_attr( $lesson_id ); ?>"
				id="cp-complete-btn-<?php echo esc_attr( $lesson_id ); ?>"
			>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
				<?php esc_html_e( 'Mark Lesson as Complete', 'cp-theme' ); ?>
			</button>
			<?php endif; ?>
		</div>

		<!-- Progress Note -->
		<?php if ( $total_lessons > 0 ) : ?>
		<p class="cp-udemy-progress-note">
			<?php
			printf(
				/* translators: 1: completed count, 2: total count */
				esc_html__( '%1$d of %2$d lessons complete in this course', 'cp-theme' ),
				(int) $lessons_done,
				(int) $total_lessons
			);
			?>
		</p>
		<?php endif; ?>
		<?php endif; ?>

		<!-- Next Lesson Link -->
		<?php if ( $next_lesson_url ) : ?>
		<div class="cp-udemy-next-lesson">
			<a href="<?php echo esc_url( $next_lesson_url ); ?>" class="cp-button cp-udemy-next-lesson-btn">
				<?php esc_html_e( 'Next Lesson', 'cp-theme' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
			</a>
		</div>
		<?php endif; ?>

	</article>
</div><!-- .cp-shell -->

<?php endwhile; ?>

<?php get_footer(); ?>
