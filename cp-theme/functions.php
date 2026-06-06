<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cp_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 240, 'flex-height' => true, 'flex-width' => true ) );
	register_nav_menus( array( 'primary' => __( 'Primary Menu', 'cp-theme' ) ) );
}
add_action( 'after_setup_theme', 'cp_theme_setup' );

function cp_theme_assets() {
	wp_enqueue_style(
		'cp-inter-font',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'cp-theme', get_stylesheet_uri(), array( 'cp-inter-font' ), '1.2.0' );
	$raw_accent  = get_option( 'cpwp_settings', array() )['accent_color'] ?? '#6d5dfc';
	$safe_accent = sanitize_hex_color( $raw_accent ) ?: '#6d5dfc';
	wp_add_inline_style( 'cp-theme', ':root{--cp-accent:' . $safe_accent . ';}' );
	if ( is_singular( 'cp_video' ) ) {
		wp_enqueue_script( 'cp-theme-watch', get_template_directory_uri() . '/assets/watch.js', array(), '1.2.0', true );
	}
	if ( isset( $_GET['cpwp_auth'] ) && 'profile' === sanitize_key( wp_unslash( $_GET['cpwp_auth'] ) ) && class_exists( 'CPWP_Assets' ) ) {
		CPWP_Assets::enqueue_player_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'cp_theme_assets' );

function cp_theme_cp_setting( $key, $fallback = '' ) {
	$options = get_option( 'cpwp_settings', array() );
	return $options[ $key ] ?? $fallback;
}

function cp_theme_video_card( $post_id ) {
	if ( 'cp_video' !== get_post_type( $post_id ) ) {
		?>
		<article class="cp-theme-card">
			<a class="cp-theme-thumb" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo get_the_post_thumbnail( $post_id, 'medium_large' ); ?></a>
			<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
			<p class="cp-theme-meta"><?php echo esc_html( get_the_date( '', $post_id ) ); ?></p>
		</article>
		<?php
		return;
	}
	$views = absint( get_post_meta( $post_id, '_cpwp_views', true ) );
	?>
	<article class="cp-theme-card">
		<a class="cp-theme-thumb" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<?php echo get_the_post_thumbnail( $post_id, 'medium_large', array( 'loading' => 'lazy' ) ); ?>
			<?php endif; ?>
			<span class="cp-theme-play" aria-hidden="true">&#9654;</span>
		</a>
		<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
		<p class="cp-theme-meta"><?php echo esc_html( sprintf( _n( '%s view', '%s views', $views, 'cp-theme' ), number_format_i18n( $views ) ) ); ?> · <?php echo esc_html( get_the_date( '', $post_id ) ); ?></p>
	</article>
	<?php
}

function cp_theme_video_section( $title, $args, $link = '' ) {
	$count = absint( cp_theme_cp_setting( 'home_videos_per_section', 8 ) );
	$query = new WP_Query( array_merge( array( 'post_type' => 'cp_video', 'posts_per_page' => max( 1, $count ) ), $args ) );
	if ( ! $query->have_posts() ) {
		return;
	}
	?>
	<section class="cp-section">
		<div class="cp-section-head"><h2><?php echo esc_html( $title ); ?></h2><?php if ( $link ) : ?><a class="cp-section-link" href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'View all', 'cp-theme' ); ?> →</a><?php endif; ?></div>
		<div class="cp-theme-grid">
			<?php while ( $query->have_posts() ) : $query->the_post(); cp_theme_video_card( get_the_ID() ); endwhile; ?>
		</div>
	</section>
	<?php
	wp_reset_postdata();
}

function cp_theme_comment_callback( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
	?>
	<<?php echo $tag; ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID(); ?>">
		<div id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<div class="comment-avatar">
				<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
			</div>
			<div class="comment-main">
				<div class="comment-header">
					<span class="comment-author-name"><?php comment_author_link(); ?></span>
					<span class="comment-date">
						<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
							<?php printf( esc_html__( '%s ago', 'cp-theme' ), human_time_diff( get_comment_date( 'U' ), current_time( 'timestamp' ) ) ); ?>
						</a>
					</span>
				</div>
				<div class="comment-content">
					<?php if ( '0' == $comment->comment_approved ) : ?>
						<em class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'cp-theme' ); ?></em>
					<?php endif; ?>
					<?php comment_text(); ?>
				</div>
				<div class="comment-actions">
					<button class="comment-like-btn" aria-label="Like" data-comment-id="<?php comment_ID(); ?>">
						<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
						<span class="like-count">0</span>
					</button>
					<button class="comment-dislike-btn" aria-label="Dislike" data-comment-id="<?php comment_ID(); ?>">
						<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"></path></svg>
					</button>
					<div class="comment-reply-action">
						<?php
						comment_reply_link( array_merge( $args, array(
							'add_below' => 'div-comment',
							'depth'     => $depth,
							'max_depth' => $args['max_depth'],
							'reply_text'=> __( 'Reply', 'cp-theme' )
						) ) );
						?>
					</div>
				</div>
				<?php
				$children = $comment->get_children(array('status' => 'approve'));
				if ( ! empty( $children ) ) :
					$children_count = count($children);
					?>
					<button class="comment-replies-toggle" data-comment-id="<?php comment_ID(); ?>">
						<svg class="toggle-icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						<span class="toggle-text"><?php
							if ($children_count == 1) {
								printf(esc_html__('1 reply', 'cp-theme'));
							} else {
								printf(esc_html__('%d replies', 'cp-theme'), $children_count);
							}
						?></span>
					</button>
				<?php endif; ?>
			</div>
		</div>
	<?php
}

function cp_theme_get_template_page_url( $template_name ) {
	$pages = get_pages( array(
		'meta_key'   => '_wp_page_template',
		'meta_value' => $template_name,
		'number'     => 1,
	) );
	if ( ! empty( $pages ) ) {
		return get_permalink( $pages[0]->ID );
	}
	return home_url( '/' );
}

