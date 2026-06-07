<?php
/**
 * Template: Default — Archive / Browse Page
 * Generic video archive used by all non-streaming site types.
 */
get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>
	<div class="cp-page-content">
		<?php cp_theme_filter_form(); ?>
		<header class="cp-section-head">
			<div>
				<span class="cp-kicker"><?php esc_html_e( 'Browse', 'cp-theme' ); ?></span>
				<h1 class="cp-page-title"><?php post_type_archive_title(); ?></h1>
			</div>
		</header>
		<?php echo do_shortcode( '[cp_video_grid limit="12"]' ); ?>
	</div>
</div>
<?php get_footer(); ?>
