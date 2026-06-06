<?php

if ( ! defined( 'ABSPATH' ) ) exit;

final class CPWP_Learning {
	const ENROLLMENTS = '_cpwp_course_enrollments';
	const ASSIGNMENTS = '_cpwp_course_assignments';
	const QUIZ_QUESTIONS = '_cpwp_quiz_questions';
	const QUIZ_ATTEMPTS = '_cpwp_quiz_attempts';
	const CERTIFICATES = '_cpwp_certificates';
	const LESSON_COMPLETIONS = '_cpwp_lesson_completions';

	public static function register_routes() {
		register_rest_route( 'cpwp/v1', '/learning/enroll/(?P<course_id>\d+)', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'enroll_route' ), 'permission_callback' => 'is_user_logged_in' ) );
		register_rest_route( 'cpwp/v1', '/learning/quiz/(?P<quiz_id>\d+)', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'quiz_route' ), 'permission_callback' => 'is_user_logged_in' ) );
		register_rest_route( 'cpwp/v1', '/learning/lesson/(?P<lesson_id>\d+)', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'lesson_route' ), 'permission_callback' => 'is_user_logged_in' ) );
	}

	public static function add_meta_boxes() {
		add_meta_box( 'cpwp-quiz-builder', __( 'Quiz Questions', 'cp-wp-plugin' ), array( __CLASS__, 'render_quiz_builder' ), 'cp_quiz', 'normal', 'high' );
		add_meta_box( 'cpwp-course-assignments', __( 'Course Assignments', 'cp-wp-plugin' ), array( __CLASS__, 'render_assignments' ), 'cp_course', 'normal', 'default' );
		add_meta_box( 'cpwp-group-members', __( 'Group Members', 'cp-wp-plugin' ), array( __CLASS__, 'render_group_members' ), 'cp_group', 'normal', 'default' );
	}

	public static function save( $post_id ) {
		if ( ! isset( $_POST['cpwp_learning_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cpwp_learning_nonce'] ) ), 'cpwp_learning' ) || ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( 'cp_quiz' === get_post_type( $post_id ) ) {
			$questions = array();
			foreach ( (array) ( $_POST['cpwp_questions'] ?? array() ) as $row ) { $question = sanitize_text_field( wp_unslash( $row['question'] ?? '' ) ); $options = array_values( array_filter( array_map( 'sanitize_text_field', (array) ( $row['options'] ?? array() ) ) ) ); $answer = absint( $row['answer'] ?? 0 ); if ( $question && count( $options ) >= 2 && isset( $options[ $answer ] ) ) $questions[] = compact( 'question', 'options', 'answer' ); }
			update_post_meta( $post_id, self::QUIZ_QUESTIONS, $questions );
		}
		if ( 'cp_course' === get_post_type( $post_id ) ) {
			$assignments = array( 'users' => array_values( array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['cpwp_assignment_users'] ?? '' ) ) ) ) ) ), 'groups' => array_values( array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['cpwp_assignment_groups'] ?? '' ) ) ) ) ) ) );
			update_post_meta( $post_id, self::ASSIGNMENTS, $assignments );
		}
		if ( 'cp_group' === get_post_type( $post_id ) ) update_post_meta( $post_id, '_cpwp_group_members', array_values( array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['cpwp_group_members'] ?? '' ) ) ) ) ) ) );
	}

	public static function render_quiz_builder( $post ) {
		wp_nonce_field( 'cpwp_learning', 'cpwp_learning_nonce' ); $questions = (array) get_post_meta( $post->ID, self::QUIZ_QUESTIONS, true );
		echo '<p>Enter one question per row. Separate answers with | and enter the correct answer number starting from 1.</p>';
		for ( $i = 0; $i < max( 5, count( $questions ) + 1 ); $i++ ) { $q = $questions[ $i ] ?? array(); echo '<p><input style="width:40%" name="cpwp_questions[' . esc_attr( $i ) . '][question]" placeholder="Question" value="' . esc_attr( $q['question'] ?? '' ) . '"> <input style="width:40%" name="cpwp_questions[' . esc_attr( $i ) . '][options_text]" placeholder="Answers separated by |" value="' . esc_attr( implode( '|', $q['options'] ?? array() ) ) . '"> <input style="width:12%" type="number" min="1" name="cpwp_questions[' . esc_attr( $i ) . '][answer_display]" placeholder="Correct #" value="' . esc_attr( isset( $q['answer'] ) ? $q['answer'] + 1 : '' ) . '"></p>'; }
		echo '<script>document.addEventListener("submit",function(){document.querySelectorAll("[name$=\\"[options_text]\\"]").forEach(function(el){var base=el.name.replace("[options_text]","");el.value.split("|").forEach(function(v,i){var h=document.createElement("input");h.type="hidden";h.name=base+"[options]["+i+"]";h.value=v;el.form.appendChild(h)});var a=el.parentNode.querySelector("[name$=\\"[answer_display]\\"]");if(a){var h=document.createElement("input");h.type="hidden";h.name=base+"[answer]";h.value=Math.max(0,Number(a.value)-1);el.form.appendChild(h)}})}, {once:true});</script>';
	}

	public static function render_assignments( $post ) {
		wp_nonce_field( 'cpwp_learning', 'cpwp_learning_nonce' ); $a = (array) get_post_meta( $post->ID, self::ASSIGNMENTS, true );
		echo '<p><label>User IDs (comma separated)<br><input style="width:100%" name="cpwp_assignment_users" value="' . esc_attr( implode( ',', $a['users'] ?? array() ) ) . '"></label></p><p><label>Group IDs (comma separated)<br><input style="width:100%" name="cpwp_assignment_groups" value="' . esc_attr( implode( ',', $a['groups'] ?? array() ) ) . '"></label></p>';
	}

	public static function render_group_members( $post ) {
		wp_nonce_field( 'cpwp_learning', 'cpwp_learning_nonce' );
		echo '<p><label>User IDs (comma separated)<br><input style="width:100%" name="cpwp_group_members" value="' . esc_attr( implode( ',', (array) get_post_meta( $post->ID, '_cpwp_group_members', true ) ) ) . '"></label></p>';
	}

	public static function enroll_route( WP_REST_Request $request ) { $course = absint( $request['course_id'] ); if ( 'cp_course' !== get_post_type( $course ) ) return new WP_Error( 'invalid_course', 'Invalid course.', array( 'status' => 404 ) ); self::enroll( get_current_user_id(), $course ); return rest_ensure_response( array( 'enrolled' => true ) ); }
	public static function enroll( $user_id, $course_id ) { $ids = self::enrolled_courses( $user_id ); if ( ! in_array( $course_id, $ids, true ) ) { $ids[] = $course_id; update_user_meta( $user_id, self::ENROLLMENTS, $ids ); } }
	public static function enrolled_courses( $user_id = 0 ) { return array_values( array_filter( array_map( 'absint', (array) get_user_meta( $user_id ?: get_current_user_id(), self::ENROLLMENTS, true ) ) ) ); }

	public static function assigned_courses( $user_id = 0 ) {
		$user_id = $user_id ?: get_current_user_id(); $groups = get_posts( array( 'post_type' => 'cp_group', 'posts_per_page' => -1, 'meta_key' => '_cpwp_group_members', 'meta_value' => '"' . $user_id . '"', 'meta_compare' => 'LIKE' ) ); $group_ids = wp_list_pluck( $groups, 'ID' ); $result = array();
		foreach ( get_posts( array( 'post_type' => 'cp_course', 'posts_per_page' => -1 ) ) as $course ) { $a = (array) get_post_meta( $course->ID, self::ASSIGNMENTS, true ); if ( in_array( $user_id, $a['users'] ?? array(), true ) || array_intersect( $group_ids, $a['groups'] ?? array() ) ) $result[] = $course->ID; } return $result;
	}

	public static function quiz_route( WP_REST_Request $request ) {
		$quiz = absint( $request['quiz_id'] ); $questions = (array) get_post_meta( $quiz, self::QUIZ_QUESTIONS, true ); if ( 'cp_quiz' !== get_post_type( $quiz ) || ! $questions ) return new WP_Error( 'invalid_quiz', 'Quiz unavailable.', array( 'status' => 404 ) );
		$answers = (array) $request['answers']; $correct = 0; foreach ( $questions as $i => $q ) if ( isset( $answers[ $i ] ) && absint( $answers[ $i ] ) === absint( $q['answer'] ) ) $correct++;
		$score = round( ( $correct / count( $questions ) ) * 100 ); $attempts = (array) get_user_meta( get_current_user_id(), self::QUIZ_ATTEMPTS, true ); $attempts[ $quiz ][] = array( 'score' => $score, 'correct' => $correct, 'total' => count( $questions ), 'time' => time(), 'passed' => $score >= 70 ); update_user_meta( get_current_user_id(), self::QUIZ_ATTEMPTS, $attempts );
		self::refresh_certificates( get_current_user_id() ); return rest_ensure_response( end( $attempts[ $quiz ] ) );
	}

	public static function lesson_route( WP_REST_Request $request ) {
		$lesson = absint( $request['lesson_id'] );
		if ( 'cp_lesson' !== get_post_type( $lesson ) ) return new WP_Error( 'invalid_lesson', 'Invalid lesson.', array( 'status' => 404 ) );
		$completed = self::lesson_completions();
		if ( ! in_array( $lesson, $completed, true ) ) $completed[] = $lesson;
		update_user_meta( get_current_user_id(), self::LESSON_COMPLETIONS, $completed );
		self::refresh_certificates( get_current_user_id() );
		return rest_ensure_response( array( 'completed' => true ) );
	}

	public static function lesson_completions( $user_id = 0 ) { return array_values( array_filter( array_map( 'absint', (array) get_user_meta( $user_id ?: get_current_user_id(), self::LESSON_COMPLETIONS, true ) ) ) ); }

	public static function course_complete( $user_id, $course_id ) {
		$items = CPWP_Site_Modules::children( $course_id, array( 'cp_lesson', 'cp_quiz' ) ); if ( ! $items ) return false; $attempts = (array) get_user_meta( $user_id, self::QUIZ_ATTEMPTS, true ); $lessons = self::lesson_completions( $user_id );
		foreach ( $items as $item ) { if ( 'cp_quiz' === $item->post_type ) { $passed = array_filter( $attempts[ $item->ID ] ?? array(), function ( $a ) { return ! empty( $a['passed'] ); } ); if ( ! $passed ) return false; } elseif ( ! in_array( $item->ID, $lessons, true ) ) return false; } return true;
	}

	public static function course_progress( $user_id, $course_id ) {
		$items = CPWP_Site_Modules::children( $course_id, array( 'cp_lesson', 'cp_quiz' ) ); if ( ! $items ) return 0; $done = 0; $attempts = self::attempts( $user_id ); $lessons = self::lesson_completions( $user_id );
		foreach ( $items as $item ) { if ( 'cp_lesson' === $item->post_type && in_array( $item->ID, $lessons, true ) ) $done++; if ( 'cp_quiz' === $item->post_type && array_filter( $attempts[ $item->ID ] ?? array(), function ( $a ) { return ! empty( $a['passed'] ); } ) ) $done++; }
		return round( ( $done / count( $items ) ) * 100 );
	}

	public static function manager_report() {
		$rows = array();
		foreach ( get_users( array( 'number' => 500 ) ) as $user ) foreach ( self::assigned_courses( $user->ID ) as $course_id ) $rows[] = array( 'user' => $user, 'course' => get_post( $course_id ), 'progress' => self::course_progress( $user->ID, $course_id ), 'complete' => self::course_complete( $user->ID, $course_id ), 'deadline' => get_post_meta( $course_id, '_cpwp_deadline', true ) );
		return $rows;
	}

	public static function refresh_certificates( $user_id ) { $certs = (array) get_user_meta( $user_id, self::CERTIFICATES, true ); foreach ( array_unique( array_merge( self::enrolled_courses( $user_id ), self::assigned_courses( $user_id ) ) ) as $course ) if ( self::course_complete( $user_id, $course ) && empty( $certs[ $course ] ) ) $certs[ $course ] = array( 'issued' => time(), 'code' => strtoupper( wp_generate_password( 10, false, false ) ) ); update_user_meta( $user_id, self::CERTIFICATES, $certs ); }
	public static function certificates( $user_id = 0 ) { self::refresh_certificates( $user_id ?: get_current_user_id() ); return (array) get_user_meta( $user_id ?: get_current_user_id(), self::CERTIFICATES, true ); }
	public static function attempts( $user_id = 0 ) { return (array) get_user_meta( $user_id ?: get_current_user_id(), self::QUIZ_ATTEMPTS, true ); }
}
