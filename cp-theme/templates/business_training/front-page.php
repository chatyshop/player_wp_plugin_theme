<?php
/**
 * Template: Business Training — Homepage
 * Employee dashboard: Assigned training, upcoming deadlines.
 */
get_header();

$user_id = get_current_user_id();

// Fetch videos as "Assigned Training" (demo logic)
$assigned = get_posts( array(
	'post_type'      => 'cp_video',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
) );

// Fetch groups as "Departments"
$departments = class_exists( 'CPWP_Community' ) ? CPWP_Community::groups() : array();

?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-business-content">

		<?php if ( ! is_user_logged_in() ) : ?>
		<div class="cp-business-welcome-banner" style="margin:40px;">
			<div>
				<h1 class="cp-business-welcome-banner__name"><?php esc_html_e( 'Corporate Training Portal', 'cp-theme' ); ?></h1>
				<p class="cp-business-welcome-banner__sub"><?php esc_html_e( 'Please log in with your employee credentials to access your assigned training and company library.', 'cp-theme' ); ?></p>
			</div>
			<a href="<?php echo esc_url( wp_login_url() ); ?>" class="cp-business-btn cp-business-btn--primary cp-business-btn--lg"><?php esc_html_e( 'Employee Login', 'cp-theme' ); ?></a>
		</div>
		<?php else : ?>

		<div class="cp-business-welcome-banner">
			<div>
				<div class="cp-business-welcome-banner__greeting"><?php esc_html_e( 'Welcome back,', 'cp-theme' ); ?></div>
				<h1 class="cp-business-welcome-banner__name"><?php echo esc_html( wp_get_current_user()->display_name ); ?></h1>
				<p class="cp-business-welcome-banner__sub"><?php esc_html_e( 'Here is your training overview.', 'cp-theme' ); ?></p>
			</div>
			<div class="cp-business-welcome-stats">
				<div class="cp-business-stat-card">
					<span class="cp-business-stat-card__val">3</span>
					<span class="cp-business-stat-card__label"><?php esc_html_e( 'Pending Tasks', 'cp-theme' ); ?></span>
				</div>
				<div class="cp-business-stat-card">
					<span class="cp-business-stat-card__val">12</span>
					<span class="cp-business-stat-card__label"><?php esc_html_e( 'Completed', 'cp-theme' ); ?></span>
				</div>
				<div class="cp-business-stat-card">
					<span class="cp-business-stat-card__val">100%</span>
					<span class="cp-business-stat-card__label"><?php esc_html_e( 'Compliance', 'cp-theme' ); ?></span>
				</div>
			</div>
		</div>

		<?php if ( $assigned ) : ?>
		<section class="cp-business-training-section">
			<div class="cp-business-training-section__header">
				<h2 class="cp-business-section-title"><?php esc_html_e( 'Required Training', 'cp-theme' ); ?></h2>
				<span class="cp-business-badge cp-business-badge--dept"><?php esc_html_e( 'Action Needed', 'cp-theme' ); ?></span>
			</div>
			<ul class="cp-business-deadline-list">
				<?php foreach ( $assigned as $task ) : ?>
				<li class="cp-business-deadline-item">
					<a href="<?php echo esc_url( get_permalink( $task->ID ) ); ?>">📘 <?php echo esc_html( $task->post_title ); ?></a>
					<span class="cp-business-deadline-date"><?php esc_html_e( 'Due by end of month', 'cp-theme' ); ?></span>
					<a href="<?php echo esc_url( get_permalink( $task->ID ) ); ?>" class="cp-business-btn cp-business-btn--primary cp-business-btn--sm"><?php esc_html_e( 'Start Module', 'cp-theme' ); ?></a>
				</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php endif; ?>

		<?php if ( ! empty( $departments ) ) : ?>
		<section class="cp-business-training-section" style="margin-bottom: 40px;">
			<div class="cp-business-training-section__header">
				<h2 class="cp-business-section-title"><?php esc_html_e( 'Company Departments', 'cp-theme' ); ?></h2>
				<a href="<?php echo esc_url( class_exists('CPWP_Page_Suites') ? CPWP_Page_Suites::url('groups') : '#' ); ?>" class="cp-business-link"><?php esc_html_e( 'View all', 'cp-theme' ); ?> →</a>
			</div>
			<div class="cp-business-training-grid">
				<?php foreach ( array_slice( $departments, 0, 4 ) as $dept ) : ?>
				<a href="<?php echo esc_url( get_permalink( $dept->ID ) ); ?>" class="cp-business-training-card">
					<div class="cp-business-training-card__thumb-placeholder">🏢</div>
					<div class="cp-business-training-card__body">
						<h3 class="cp-business-training-card__title"><?php echo esc_html( $dept->post_title ); ?></h3>
					</div>
					<div class="cp-business-training-card__footer">
						<span class="cp-business-link"><?php esc_html_e( 'Access Hub', 'cp-theme' ); ?> →</span>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_grid' ); ?>

		<?php endif; ?>
	</div>
</div>
<?php get_footer(); ?>
