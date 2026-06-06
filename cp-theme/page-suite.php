<?php get_header(); $slug = get_query_var( 'cpwp_suite_slug' ); $title = get_query_var( 'cpwp_suite_title' ); $items = CPWP_Page_Suites::data( $slug ); ?>
<div class="cp-shell"><header class="cp-section-head"><div><span class="cp-kicker"><?php echo esc_html( cp_theme_cp_setting( 'site_type', 'Video platform' ) ); ?></span><h1><?php echo esc_html( $title ); ?></h1></div></header>
<?php cp_theme_render_suite( $slug, $items ); ?></div>
<?php get_footer(); ?>
