<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cp_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 240, 'flex-height' => true, 'flex-width' => true ) );
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'cp-theme' ),
		'footer'  => __( 'Footer Menu', 'cp-theme' ),
	) );
}
add_action( 'after_setup_theme', 'cp_theme_setup' );

/**
 * Route single cp_series pages to a site-type-specific template.
 */
function cp_theme_series_template( $template ) {
	if ( is_singular( 'cp_series' ) ) {
		$site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
		$custom    = locate_template( 'templates/' . $site_type . '/single-series.php' );
		if ( $custom ) return $custom;
		$default = locate_template( 'templates/default/single-series.php' );
		if ( $default ) return $default;
	}
	return $template;
}
add_filter( 'single_template', 'cp_theme_series_template' );

/**
 * Route the channel page to a site-type-specific template.
 */
function cp_theme_channel_template( $template ) {
	if ( get_query_var( 'cpwp_public_channel' ) ) {
		$site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
		$custom    = locate_template( 'templates/' . $site_type . '/channel.php' );
		if ( $custom ) return $custom;
		$default = locate_template( 'templates/default/channel.php' );
		if ( $default ) return $default;
	}
	return $template;
}
add_filter( 'template_include', 'cp_theme_channel_template', 99 );

/**
 * Route single cp_course pages to a site-type-specific template.
 */
function cp_theme_course_template( $template ) {
	if ( is_singular( 'cp_course' ) ) {
		$site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
		$custom    = locate_template( 'templates/' . $site_type . '/single-course.php' );
		if ( $custom ) return $custom;
		$default = locate_template( 'templates/default/single-course.php' );
		if ( $default ) return $default;
	}
	return $template;
}
add_filter( 'single_template', 'cp_theme_course_template' );

/**
 * Route single cp_lesson pages to a site-type-specific template.
 */
function cp_theme_lesson_template( $template ) {
	if ( is_singular( 'cp_lesson' ) ) {
		$site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
		$custom    = locate_template( 'templates/' . $site_type . '/single-lesson.php' );
		if ( $custom ) return $custom;
		$default = locate_template( 'templates/default/single-lesson.php' );
		if ( $default ) return $default;
	}
	return $template;
}
add_filter( 'single_template', 'cp_theme_lesson_template' );

/**
 * Route single cp_event pages to a site-type-specific template.
 */
function cp_theme_event_template( $template ) {
	if ( is_singular( 'cp_event' ) ) {
		$site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
		$custom    = locate_template( 'templates/' . $site_type . '/single-event.php' );
		if ( $custom ) return $custom;
		$default = locate_template( 'templates/default/single-event.php' );
		if ( $default ) return $default;
	}
	return $template;
}
add_filter( 'single_template', 'cp_theme_event_template' );

/**
 * Route single cp_group pages to a site-type-specific template.
 */
function cp_theme_group_template( $template ) {
	if ( is_singular( 'cp_group' ) ) {
		$site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
		$custom    = locate_template( 'templates/' . $site_type . '/single-group.php' );
		if ( $custom ) return $custom;
		$default = locate_template( 'templates/default/single-group.php' );
		if ( $default ) return $default;
	}
	return $template;
}
add_filter( 'single_template', 'cp_theme_group_template' );

/**
 * Route single cp_collection pages to a site-type-specific template.
 */
function cp_theme_collection_template( $template ) {
	if ( is_singular( 'cp_collection' ) ) {
		$site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
		$custom    = locate_template( 'templates/' . $site_type . '/single-collection.php' );
		if ( $custom ) return $custom;
		$default = locate_template( 'templates/default/single-collection.php' );
		if ( $default ) return $default;
	}
	return $template;
}
add_filter( 'single_template', 'cp_theme_collection_template' );

/**
 * Route single cp_news pages to a site-type-specific template.
 */
function cp_theme_news_template( $template ) {
	if ( is_singular( 'cp_news' ) ) {
		$site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
		$custom    = locate_template( 'templates/' . $site_type . '/single-news.php' );
		if ( $custom ) return $custom;
		$default = locate_template( 'templates/default/single-news.php' );
		if ( $default ) return $default;
	}
	return $template;
}
add_filter( 'single_template', 'cp_theme_news_template' );

/**
 * Route single cp_person pages to a site-type-specific template.
 */
function cp_theme_person_template( $template ) {
	if ( is_singular( 'cp_person' ) ) {
		$site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
		$custom    = locate_template( 'templates/' . $site_type . '/single-person.php' );
		if ( $custom ) return $custom;
		$default = locate_template( 'templates/default/single-person.php' );
		if ( $default ) return $default;
	}
	return $template;
}
add_filter( 'single_template', 'cp_theme_person_template' );

/**
 * Route single cp_product pages to a site-type-specific template.
 */
function cp_theme_product_template( $template ) {
	if ( is_singular( 'cp_product' ) ) {
		$site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
		$custom    = locate_template( 'templates/' . $site_type . '/single-product.php' );
		if ( $custom ) return $custom;
		$default = locate_template( 'templates/default/single-product.php' );
		if ( $default ) return $default;
	}
	return $template;
}
add_filter( 'single_template', 'cp_theme_product_template' );






function cp_theme_assets() {
	wp_enqueue_style(
		'cp-inter-font',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'cp-theme', get_stylesheet_uri(), array( 'cp-inter-font' ), '1.10.0' );
	$raw_accent  = get_option( 'cpwp_settings', array() )['accent_color'] ?? '#6d5dfc';
	$safe_accent = sanitize_hex_color( $raw_accent ) ?: '#6d5dfc';
	wp_add_inline_style( 'cp-theme', ':root{--cp-accent:' . $safe_accent . ';}' );
	if ( is_singular( 'cp_video' ) ) {
		wp_enqueue_script( 'cp-theme-watch', get_template_directory_uri() . '/assets/watch.js', array(), '1.2.0', true );
	}
	if ( isset( $_GET['cpwp_auth'] ) && 'profile' === sanitize_key( wp_unslash( $_GET['cpwp_auth'] ) ) && class_exists( 'CPWP_Assets' ) ) {
		CPWP_Assets::enqueue_player_assets();
		wp_enqueue_script( 'cpwp-channel', get_template_directory_uri() . '/assets/channel.js', array(), '1.0.0', true );
		wp_localize_script( 'cpwp-channel', 'cpwpChannel', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'cpwp_channel_upload' ) ) );
	}
	if ( is_page_template( 'page-cases.php' ) && class_exists( 'CPWP_Assets' ) ) CPWP_Assets::enqueue_player_assets();
	if ( get_query_var( 'cpwp_channel' ) && class_exists( 'CPWP_Assets' ) ) CPWP_Assets::enqueue_player_assets();
	if ( is_singular( array( 'cp_course', 'cp_lesson', 'cp_quiz' ) ) && class_exists( 'CPWP_Assets' ) ) CPWP_Assets::enqueue_player_assets();
	$suite = get_query_var( 'cpwp_suite' );
	if ( 'studio' === $suite && class_exists( 'CPWP_Assets' ) ) {
		CPWP_Assets::enqueue_player_assets();
		wp_enqueue_script( 'cpwp-studio', get_template_directory_uri() . '/assets/studio.js', array(), '1.0.0', true );
		wp_localize_script( 'cpwp-studio', 'cpwpStudio', array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'restUrl'      => esc_url_raw( rest_url( 'cpwp/v1' ) ),
			'nonce'        => wp_create_nonce( 'wp_rest' ),
			'channelNonce' => wp_create_nonce( 'cpwp_channel_upload' ),
		) );
	}
	if ( 'upload' === $suite && class_exists( 'CPWP_Assets' ) ) {
		CPWP_Assets::enqueue_player_assets();
		wp_enqueue_script( 'cpwp-upload', get_template_directory_uri() . '/assets/upload.js', array(), '1.0.0', true );
		wp_localize_script( 'cpwp-upload', 'cpwpUpload', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'cpwp_channel_upload' ),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'cp_theme_assets' );

function cp_theme_cp_setting( $key, $fallback = '' ) {
	$options = get_option( 'cpwp_settings', array() );
	return $options[ $key ] ?? $fallback;
}

function cp_theme_body_class( $classes ) {
	$site_type = cp_theme_cp_setting( 'site_type' );
	if ( $site_type ) {
		$classes[] = 'cp-site-type-' . sanitize_html_class( $site_type );
	}
	return $classes;
}
add_filter( 'body_class', 'cp_theme_body_class' );

function cp_theme_get_upgrade_url() {
	$custom_url = cp_theme_cp_setting( 'subscription_checkout_url', '' );
	if ( ! empty( $custom_url ) ) {
		return esc_url( $custom_url );
	}

	$plugin = cp_theme_cp_setting( 'subscription_plugin', 'pmpro' );
	if ( 'pmpro' === $plugin && function_exists( 'pmpro_url' ) ) {
		return pmpro_url( 'levels' );
	}
	if ( 'woocommerce' === $plugin && function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'shop' );
	}
	if ( 'memberpress' === $plugin && class_exists( 'MeprProductsHelper' ) ) {
		return home_url( '/register' );
	}

	return wp_registration_url() ?: home_url( '/wp-login.php?action=register' );
}

function cp_theme_get_pricing_plans() {
	$plugin = cp_theme_cp_setting( 'subscription_plugin', 'pmpro' );
	
	if ( 'pmpro' === $plugin && function_exists( 'pmpro_getAllLevels' ) ) {
		$levels = pmpro_getAllLevels( false, true );
		if ( ! empty( $levels ) ) {
			$plans = array();
			$count = 0;
			foreach ( $levels as $level ) {
				$count++;
				$desc = $level->description;
				if ( empty( $desc ) ) {
					$desc = sprintf( __( 'Unlock %s level content.', 'cp-theme' ), $level->name );
				}
				
				$features = array_filter( array_map( 'trim', explode( "\n", str_replace( "\r", "", $level->description ) ) ) );
				if ( empty( $features ) || count( $features ) <= 1 ) {
					$features = array(
						sprintf( __( 'Access to %s content', 'cp-theme' ), $level->name ),
						__( 'HD playback streaming', 'cp-theme' ),
						__( 'Interactive comments & community', 'cp-theme' )
					);
				} else {
					$desc = sprintf( __( 'Get access to our premium %s membership.', 'cp-theme' ), $level->name );
				}
				
				$price = '';
				if ( pmpro_isLevelFree( $level ) ) {
					$price = __( 'Free', 'cp-theme' );
				} else {
					$price = '$' . number_format_i18n( $level->initial_payment );
					if ( ! empty( $level->billing_amount ) && $level->billing_amount != 0 ) {
						$cycle = '';
						if ( $level->cycle_number == 1 ) {
							$cycle = '/' . $level->cycle_period;
						} else {
							$cycle = '/' . $level->cycle_number . ' ' . $level->cycle_period . 's';
						}
						$price = '$' . number_format_i18n( $level->billing_amount ) . $cycle;
					}
				}

				$plans[] = array(
					'id'          => $level->id,
					'name'        => $level->name,
					'description' => $desc,
					'price'       => $price,
					'url'         => pmpro_url( 'checkout', '?level=' . $level->id ),
					'features'    => $features,
					'button_text' => pmpro_isLevelFree( $level ) ? __( 'Get Started', 'cp-theme' ) : __( 'Upgrade Now', 'cp-theme' ),
					'recommended' => ( 2 === $count ),
				);
			}
			return $plans;
		}
	}

	$free_url = cp_theme_cp_setting( 'pricing_free_url', '' );
	if ( empty( $free_url ) ) {
		$free_url = wp_registration_url() ?: home_url( '/wp-login.php?action=register' );
	}
	
	$pro_url = cp_theme_cp_setting( 'pricing_pro_url', '' );
	if ( empty( $pro_url ) ) {
		$pro_url = cp_theme_get_upgrade_url();
	}

	$premium_url = cp_theme_cp_setting( 'pricing_premium_url', '' );
	if ( empty( $premium_url ) ) {
		$premium_url = cp_theme_get_upgrade_url();
	}

	$free_feats = array_filter( array_map( 'trim', explode( "\n", str_replace( "\r", "", cp_theme_cp_setting( 'pricing_free_features', '' ) ) ) ) );
	$pro_feats = array_filter( array_map( 'trim', explode( "\n", str_replace( "\r", "", cp_theme_cp_setting( 'pricing_pro_features', '' ) ) ) ) );
	$prem_feats = array_filter( array_map( 'trim', explode( "\n", str_replace( "\r", "", cp_theme_cp_setting( 'pricing_premium_features', '' ) ) ) ) );

	return array(
		array(
			'name'        => __( 'Free', 'cp-theme' ),
			'description' => __( 'Perfect to explore and test our platform features.', 'cp-theme' ),
			'price'       => cp_theme_cp_setting( 'pricing_free_price', '$0' ),
			'url'         => esc_url( $free_url ),
			'features'    => $free_feats,
			'button_text' => __( 'Get Started', 'cp-theme' ),
			'recommended' => false,
		),
		array(
			'name'        => __( 'Pro', 'cp-theme' ),
			'description' => __( 'Great for regular viewers and active community members.', 'cp-theme' ),
			'price'       => cp_theme_cp_setting( 'pricing_pro_price', '$9.99 / month' ),
			'url'         => esc_url( $pro_url ),
			'features'    => $pro_feats,
			'button_text' => __( 'Upgrade Now', 'cp-theme' ),
			'recommended' => true,
		),
		array(
			'name'        => __( 'Premium', 'cp-theme' ),
			'description' => __( 'Full unrestricted access for true power users.', 'cp-theme' ),
			'price'       => cp_theme_cp_setting( 'pricing_premium_price', '$19.99 / month' ),
			'url'         => esc_url( $premium_url ),
			'features'    => $prem_feats,
			'button_text' => __( 'Go Unlimited', 'cp-theme' ),
			'recommended' => false,
		),
	);
}

function cp_theme_video_card( $post_id ) {
	$can_access = true;
	if ( class_exists( 'CPWP_Site_Modules' ) ) {
		$can_access = CPWP_Site_Modules::can_access_video( $post_id );
	}

	if ( 'cp_video' !== get_post_type( $post_id ) ) {
		?>
		<article class="cp-theme-card">
			<a class="cp-theme-thumb" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
				<?php echo get_the_post_thumbnail( $post_id, 'medium_large' ); ?>
				<?php if ( ! $can_access ) : ?>
					<span class="cp-card-lock-badge" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.78); color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; display: flex; align-items: center; gap: 4px; border: 1px solid rgba(255,255,255,0.15); line-height: 1; z-index: 2;">
						<svg viewBox="0 0 24 24" width="11" height="11" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round" style="margin-top:-1px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
						<?php esc_html_e( 'LOCKED', 'cp-theme' ); ?>
					</span>
				<?php endif; ?>
			</a>
			<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
			<p class="cp-theme-meta"><?php echo esc_html( get_the_date( '', $post_id ) ); ?></p>
		</article>
		<?php
		return;
	}
	$views = absint( get_post_meta( $post_id, '_cpwp_views', true ) );
	$series = get_post_meta( $post_id, '_cpwp_series_name', true );
	$rating = get_post_meta( $post_id, '_cpwp_age_rating', true );
	?>
	<article class="cp-theme-card">
		<a class="cp-theme-thumb" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<?php echo get_the_post_thumbnail( $post_id, 'medium_large', array( 'loading' => 'lazy' ) ); ?>
			<?php endif; ?>
			<span class="cp-theme-play" aria-hidden="true">&#9654;</span>
			<?php if ( ! $can_access ) : ?>
				<span class="cp-card-lock-badge" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.78); color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; display: flex; align-items: center; gap: 4px; border: 1px solid rgba(255,255,255,0.15); line-height: 1; z-index: 2;">
					<svg viewBox="0 0 24 24" width="11" height="11" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round" style="margin-top:-1px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
					<?php esc_html_e( 'LOCKED', 'cp-theme' ); ?>
				</span>
			<?php endif; ?>
		</a>
		<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
		<?php if ( $series || $rating ) : ?><p class="cp-theme-labels"><?php if ( $series ) : ?><span><?php echo esc_html( $series ); ?></span><?php endif; ?><?php if ( $rating ) : ?><span><?php echo esc_html( $rating ); ?></span><?php endif; ?></p><?php endif; ?>
		<p class="cp-theme-meta"><?php echo esc_html( sprintf( _n( '%s view', '%s views', $views, 'cp-theme' ), number_format_i18n( $views ) ) ); ?> · <?php echo esc_html( get_the_date( '', $post_id ) ); ?></p>
	</article>
	<?php
}

function cp_theme_module_navigation() {
	foreach ( cp_theme_preset_navigation()['discover'] as $link ) printf( '<a href="%s">%s</a>', esc_url( $link['url'] ), esc_html( $link['label'] ) );
}

function cp_theme_preset_navigation() {
	$groups = array(
		'main' => array( array( 'label' => 'Home', 'url' => home_url( '/' ) ), array( 'label' => 'All Videos', 'url' => get_post_type_archive_link( 'cp_video' ) ) ),
		'library' => array(),
		'discover' => array(),
		'account' => array( array( 'label' => 'My Profile', 'url' => add_query_arg( 'cpwp_auth', 'profile', home_url( '/' ) ) ), array( 'label' => 'My Cases', 'url' => cp_theme_get_template_page_url( 'page-cases.php' ) ) ),
	);
	if ( cp_theme_cp_setting( 'enable_favorites_watch_later', true ) ) {
		$groups['library'][] = array( 'label' => 'Watch Later', 'url' => cp_theme_get_template_page_url( 'page-watch-later.php' ) );
		$groups['library'][] = array( 'label' => 'Favorites', 'url' => cp_theme_get_template_page_url( 'page-favorites.php' ) );
	}
	if ( cp_theme_cp_setting( 'enable_continue_watching', true ) && class_exists( 'CPWP_Page_Suites' ) && isset( CPWP_Page_Suites::current_pages()['watch-history'] ) ) $groups['library'][] = array( 'label' => 'Watch History', 'url' => CPWP_Page_Suites::url( 'watch-history' ) );
	if ( class_exists( 'CPWP_Site_Modules' ) ) $groups['discover'] = array_merge( $groups['discover'], CPWP_Site_Modules::navigation() );
	if ( class_exists( 'CPWP_Page_Suites' ) ) foreach ( CPWP_Page_Suites::current_pages() as $slug => $label ) $groups['discover'][] = array( 'label' => $label, 'url' => CPWP_Page_Suites::url( $slug ) );
	$library_labels = array_map( function ( $link ) { return strtolower( $link['label'] ); }, $groups['library'] );
	$groups['discover'] = array_values( array_filter( $groups['discover'], function ( $link ) use ( $library_labels ) { return ! in_array( strtolower( $link['label'] ), $library_labels, true ); } ) );
	foreach ( $groups as $group => $links ) {
		$seen = array(); $groups[ $group ] = array_values( array_filter( $links, function ( $link ) use ( &$seen ) {
			$key = strtolower( trim( $link['label'] ) ); if ( isset( $seen[ $key ] ) ) return false; $seen[ $key ] = true; return ! empty( $link['url'] );
		} ) );
	}
	return $groups;
}

function cp_theme_render_suite( $slug, $items ) {
	cp_theme_ensure_default_terms();
	if ( 'studio' === $slug ) {
		if ( ! class_exists( 'CPWP_Channels' ) || ! CPWP_Channels::get() ) {
			echo '<p>' . esc_html__( 'Please create a channel before accessing the Creator Studio.', 'cp-theme' ) . '</p>';
			return;
		}
		$user_id = get_current_user_id();
		$videos = get_posts( array(
			'post_type'      => 'cp_video',
			'posts_per_page' => 100,
			'author'         => $user_id,
			'post_status'    => array( 'publish', 'draft' )
		) );
		$channel = CPWP_Channels::get();
		?>
		<div class="cp-studio-container">
			<nav class="cp-studio-tabs">
				<button class="cp-studio-tab is-active" data-tab="dashboard">Dashboard</button>
				<button class="cp-studio-tab" data-tab="content">Content</button>
				<button class="cp-studio-tab" data-tab="comments">Comments</button>
				<button class="cp-studio-tab" data-tab="customization">Customization</button>
			</nav>

			<!-- Panel: Dashboard -->
			<div class="cp-studio-panel is-active" data-panel="dashboard">
				<?php if ( class_exists( 'CPWP_Creator_Platform' ) ) CPWP_Creator_Platform::render_profile(); ?>
			</div>

			<!-- Panel: Content -->
			<div class="cp-studio-panel" data-panel="content">
				<div class="cp-table-wrap">
					<table class="cp-suite-table cp-studio-meta-table">
						<thead>
							<tr>
								<th>Video</th>
								<th>Status</th>
								<th>Views</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $videos as $video ) : 
								$video_url = get_post_meta( $video->ID, '_cpwp_mp4', true );
								$poster = get_post_meta( $video->ID, '_cpwp_poster_url', true ) ?: get_post_meta( $video->ID, '_cpwp_thumbnail_sprite', true );
								$autoplay = get_post_meta( $video->ID, '_cpwp_autoplay', true );
								$loop = get_post_meta( $video->ID, '_cpwp_loop', true );
								$muted = get_post_meta( $video->ID, '_cpwp_muted', true );
								$allow_comments = $video->comment_status === 'open';
								$preload = get_post_meta( $video->ID, '_cpwp_preload', true ) ?: 'metadata';
								$accent = get_post_meta( $video->ID, '_cpwp_accent_color', true ) ?: '#6d5dfc';
								$chapters = get_post_meta( $video->ID, '_cpwp_chapters', true ) ?: array();
								$subtitles = get_post_meta( $video->ID, '_cpwp_subtitles', true ) ?: array();
								$views = absint( get_post_meta( $video->ID, '_cpwp_views', true ) );

								$genre_ids = wp_get_post_terms( $video->ID, 'cp_genre', array( 'fields' => 'ids' ) );
								$genre_id = ( ! empty( $genre_ids ) && ! is_wp_error( $genre_ids ) ) ? $genre_ids[0] : '';

								$topic_ids = wp_get_post_terms( $video->ID, 'cp_topic', array( 'fields' => 'ids' ) );
								$topic_id = ( ! empty( $topic_ids ) && ! is_wp_error( $topic_ids ) ) ? $topic_ids[0] : '';

								$game_ids = wp_get_post_terms( $video->ID, 'cp_game', array( 'fields' => 'ids' ) );
								$game_id = ( ! empty( $game_ids ) && ! is_wp_error( $game_ids ) ) ? $game_ids[0] : '';

								$tag_names = wp_get_post_terms( $video->ID, 'cp_tag', array( 'fields' => 'names' ) );
								$tag_str = ( ! empty( $tag_names ) && ! is_wp_error( $tag_names ) ) ? implode( ', ', $tag_names ) : '';
							?>
							<tr data-video-id="<?php echo $video->ID; ?>">
								<td><strong><?php echo esc_html( $video->post_title ); ?></strong></td>
								<td class="cp-video-status"><?php echo esc_html( ucfirst( $video->post_status ) ); ?></td>
								<td><?php echo $views; ?></td>
								<td>
									<button class="cp-button cp-btn-toggle"><?php echo $video->post_status === 'publish' ? 'Draft' : 'Publish'; ?></button>
									<button class="cp-button cp-btn-edit" 
											data-id="<?php echo $video->ID; ?>"
											data-title="<?php echo esc_attr( $video->post_title ); ?>"
											data-description="<?php echo esc_attr( $video->post_content ); ?>"
											data-video-url="<?php echo esc_url( $video_url ); ?>"
											data-poster="<?php echo esc_url( $poster ); ?>"
											data-autoplay="<?php echo $autoplay ? '1' : ''; ?>"
											data-loop="<?php echo $loop ? '1' : ''; ?>"
											data-muted="<?php echo $muted ? '1' : ''; ?>"
											data-allow-comments="<?php echo $allow_comments ? '1' : ''; ?>"
											data-preload="<?php echo esc_attr( $preload ); ?>"
											data-accent="<?php echo esc_attr( $accent ); ?>"
											data-genre="<?php echo esc_attr( $genre_id ); ?>"
											data-topic="<?php echo esc_attr( $topic_id ); ?>"
											data-game="<?php echo esc_attr( $game_id ); ?>"
											data-tags="<?php echo esc_attr( $tag_str ); ?>"
											data-chapters="<?php echo esc_attr( json_encode( $chapters ) ); ?>"
											data-subtitles="<?php echo esc_attr( json_encode( $subtitles ) ); ?>">Edit</button>
									<button class="cp-button cp-btn-delete cp-danger-button">Delete</button>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Panel: Comments -->
			<div class="cp-studio-panel" data-panel="comments">
				<div class="cp-table-wrap">
					<table class="cp-suite-table cp-studio-meta-table">
						<thead>
							<tr>
								<th>Comment</th>
								<th>Video</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$my_video_ids = wp_list_pluck( $videos, 'ID' );
							if ( $my_video_ids ) {
								$comments = get_comments( array( 'post__in' => $my_video_ids, 'status' => 'all' ) );
								foreach ( $comments as $comment ) :
									$comment_status = wp_get_comment_status( $comment->comment_ID );
							?>
							<tr data-comment-id="<?php echo $comment->comment_ID; ?>">
								<td>
									<strong><?php echo esc_html( $comment->comment_author ); ?></strong>: 
									<?php echo esc_html( $comment->comment_content ); ?>
								</td>
								<td><a href="<?php echo esc_url( get_permalink( $comment->comment_post_ID ) ); ?>"><?php echo esc_html( get_the_title( $comment->comment_post_ID ) ); ?></a></td>
								<td class="cp-comment-status"><?php echo esc_html( ucfirst( $comment_status ) ); ?></td>
								<td>
									<button class="cp-button cp-btn-comment-toggle" data-action="<?php echo $comment_status === 'approved' ? 'unapprove' : 'approve'; ?>">
										<?php echo $comment_status === 'approved' ? 'Hold' : 'Approve'; ?>
									</button>
									<button class="cp-button cp-btn-comment-delete cp-danger-button">Delete</button>
								</td>
							</tr>
							<?php 
								endforeach;
							} else {
								echo '<tr><td colspan="4">No comments found.</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Panel: Customization -->
			<div class="cp-studio-panel" data-panel="customization">
				<form method="post" class="cp-auth-form">
					<?php wp_nonce_field( 'cpwp_channel', 'cpwp_channel_nonce' ); ?>
					<label><span>Channel Name</span><input name="channel_name" type="text" value="<?php echo esc_attr( $channel['name'] ?? '' ); ?>" required></label>
					<label><span>Description</span><textarea name="channel_description" rows="4"><?php echo esc_html( $channel['description'] ?? '' ); ?></textarea></label>
					<label><span>Channel Logo URL</span><input name="channel_logo_url" type="url" value="<?php echo esc_url( $channel['logo_url'] ?? '' ); ?>"></label>
					<label><span>Channel Banner URL</span><input name="channel_banner_url" type="url" value="<?php echo esc_url( $channel['banner_url'] ?? '' ); ?>"></label>
					<label><span>Category</span><input name="channel_category" type="text" value="<?php echo esc_attr( $channel['category'] ?? '' ); ?>"></label>
					<label><span>Accent Color</span><input name="channel_accent_color" type="color" value="<?php echo esc_attr( $channel['accent_color'] ?? '#6d5dfc' ); ?>"></label>
					<button class="cp-button" name="cpwp_save_channel" value="1" type="submit">Save Channel Settings</button>
				</form>
			</div>

		</div>

		<!-- Edit Video Modal -->
		<div id="cp-edit-modal" class="cp-report-dialog" style="display:none;" role="dialog" aria-modal="true">
			<form id="cp-edit-video-form" class="cp-report-form" style="max-width:800px; width:90%; overflow-y:auto; max-height:90vh;">
				<button type="button" class="cp-dialog-close" id="cp-close-modal" aria-label="Close">×</button>
				<h2>Edit Video Details</h2>
				<input type="hidden" id="edit-video-id" name="video_id">
				
				<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
					<div>
						<label><span>Title</span><input id="edit-video-title" type="text" required></label>
						<label><span>Description</span><textarea id="edit-video-description" rows="4"></textarea></label>
						<label><span>Video URL</span><input id="edit-video-url" type="url" required></label>
						<label><span>Poster Image URL</span><input id="edit-video-poster" type="url"></label>
					</div>
					<div>
						<label><span>Preload Mode</span>
							<select id="edit-video-preload">
								<option value="none">None</option>
								<option value="metadata">Metadata</option>
								<option value="auto">Auto</option>
							</select>
						</label>
						<label><span>Accent Color</span><input id="edit-video-accent" type="color"></label>
						
						<?php 
						$site_type = cp_theme_cp_setting( 'site_type', 'default' );
						if ( in_array( $site_type, array( 'creator_platform', 'gaming', 'podcast' ), true ) ) :
							$genres = get_terms( array( 'taxonomy' => 'cp_genre', 'hide_empty' => false ) );
							if ( ! is_wp_error( $genres ) && ! empty( $genres ) ) :
							?>
							<label><span>Genre</span>
								<select id="edit-video-genre" name="video_genre">
									<option value=""><?php esc_html_e( 'Select Genre...', 'cp-theme' ); ?></option>
									<?php foreach ( $genres as $genre ) : ?>
										<option value="<?php echo $genre->term_id; ?>"><?php echo esc_html( $genre->name ); ?></option>
									<?php endforeach; ?>
								</select>
							</label>
							<?php 
							endif;

							if ( in_array( $site_type, array( 'creator_platform', 'podcast' ), true ) ) :
								$topics = get_terms( array( 'taxonomy' => 'cp_topic', 'hide_empty' => false ) );
								if ( ! is_wp_error( $topics ) && ! empty( $topics ) ) :
								?>
								<label><span>Topic</span>
									<select id="edit-video-topic" name="video_topic">
										<option value=""><?php esc_html_e( 'Select Topic...', 'cp-theme' ); ?></option>
										<?php foreach ( $topics as $topic ) : ?>
											<option value="<?php echo $topic->term_id; ?>"><?php echo esc_html( $topic->name ); ?></option>
										<?php endforeach; ?>
									</select>
								</label>
								<?php 
								endif;
							endif;

							if ( 'gaming' === $site_type ) :
								$games = get_terms( array( 'taxonomy' => 'cp_game', 'hide_empty' => false ) );
								if ( ! is_wp_error( $games ) && ! empty( $games ) ) :
								?>
								<label><span>Game</span>
									<select id="edit-video-game" name="video_game">
										<option value=""><?php esc_html_e( 'Select Game...', 'cp-theme' ); ?></option>
										<?php foreach ( $games as $game ) : ?>
											<option value="<?php echo $game->term_id; ?>"><?php echo esc_html( $game->name ); ?></option>
										<?php endforeach; ?>
									</select>
								</label>
								<?php 
								endif;
							endif;

							if ( in_array( $site_type, array( 'creator_platform', 'gaming' ), true ) ) :
							?>
							<label><span>Video Tags (comma separated)</span>
								<input id="edit-video-tags" name="video_tags" type="text" placeholder="e.g. gameplay, tutorial, review">
							</label>
							<?php 
							endif;
						endif;
						?>

						<label class="cp-filter-check"><input id="edit-video-autoplay" type="checkbox"> <span>Autoplay</span></label>
						<label class="cp-filter-check"><input id="edit-video-loop" type="checkbox"> <span>Loop</span></label>
						<label class="cp-filter-check"><input id="edit-video-muted" type="checkbox"> <span>Start Muted</span></label>
						<label class="cp-filter-check"><input id="edit-video-allow-comments" type="checkbox"> <span>Allow Comments</span></label>
					</div>
				</div>

				<div style="margin-top:20px;">
					<h3>Chapters</h3>
					<table id="edit-chapters-table" class="cp-suite-table">
						<thead>
							<tr><th>Time (seconds)</th><th>Chapter Title</th><th>Action</th></tr>
						</thead>
						<tbody></tbody>
					</table>
					<button type="button" class="cp-button cp-button-secondary" id="edit-add-chapter" style="margin-top:10px;">Add Chapter</button>
				</div>

				<div style="margin-top:20px; margin-bottom:20px;">
					<h3>Subtitles</h3>
					<table id="edit-subtitles-table" class="cp-suite-table">
						<thead>
							<tr><th>Lang Code</th><th>Label</th><th>VTT Source URL</th><th>Default</th><th>Action</th></tr>
						</thead>
						<tbody></tbody>
					</table>
					<button type="button" class="cp-button cp-button-secondary" id="edit-add-subtitle" style="margin-top:10px;">Add Subtitle</button>
				</div>

				<div style="display:flex; gap:10px; justify-content:flex-end;">
					<button type="button" class="cp-button cp-button-secondary" id="cp-cancel-modal">Cancel</button>
					<button type="submit" class="cp-button">Save Changes</button>
				</div>
			</form>
		</div>
		<?php
		return;
	}
	if ( 'upload' === $slug ) {
		if ( ! class_exists( 'CPWP_Channels' ) || ! CPWP_Channels::get() ) {
			echo '<p>' . esc_html__( 'Please create a channel before uploading videos.', 'cp-theme' ) . '</p>';
			return;
		}
		?>
		<div class="cp-upload-wizard">
			<form id="cp-upload-wizard-form" class="cp-auth-form">
				<?php wp_nonce_field( 'cpwp_channel_video', 'cpwp_channel_video_nonce' ); ?>
				
				<!-- Hidden inputs for player defaults -->
				<input type="hidden" name="accent_color" value="<?php echo esc_attr( get_option('cpwp_settings')['accent_color'] ?? '#6d5dfc' ); ?>">
				<input type="hidden" name="preload" value="metadata">
				<input type="hidden" name="autoplay" value="">
				<input type="hidden" name="loop" value="">
				<input type="hidden" name="muted" value="">

				<!-- Step Indicators -->
				<div class="cp-upload-steps-container" style="display:flex; justify-content:space-between; margin-bottom:30px;">
					<div class="cp-upload-step is-active" data-step="1">1. Select File</div>
					<div class="cp-upload-step" data-step="2">2. Details</div>
					<div class="cp-upload-step" data-step="3">3. Options</div>
					<div class="cp-upload-step" data-step="4">4. Interactivity</div>
				</div>

				<!-- Step 1 Panel: Select File -->
				<div class="cp-upload-panel is-active" data-step-panel="1">
					<div id="cp-upload-dropzone" class="cp-upload-dropzone" style="border:2px dashed var(--cp-line); border-radius:12px; padding:60px; text-align:center; cursor:pointer;">
						<p>Drag and drop your video file here</p>
						<p>or</p>
						<button type="button" id="cp-select-file-btn" class="cp-button">Select File</button>
						<input type="file" id="cp-upload-file-input" style="display:none;" accept="video/*">
					</div>
					<div id="cp-progress-container" style="display:none; margin-top:20px;">
						<p id="cp-progress-filename" style="font-weight:bold;"></p>
						<div style="background:var(--cp-soft); border-radius:999px; height:8px; width:100%; margin:10px 0; overflow:hidden;">
							<div id="cp-progress-bar" style="background:var(--cp-accent); height:100%; width:0%;"></div>
						</div>
						<div style="display:flex; justify-content:space-between; font-size:0.85rem; color:var(--cp-muted);">
							<span id="cp-progress-pct">0%</span>
							<span id="cp-progress-speed">0 KB/s</span>
							<span id="cp-progress-eta">ETA: --</span>
						</div>
					</div>
				</div>

				<!-- Step 2 Panel: Video Details -->
				<div class="cp-upload-panel" data-step-panel="2">
					<label><span>Video Title</span><input id="upload-title" name="channel_video_title" type="text" required></label>
					<label><span>Description</span><textarea id="upload-description" name="channel_video_description" rows="4"></textarea></label>
					<label><span>Video File URL</span><input id="upload-video-url" name="channel_video_url" type="url" readonly required></label>
					<label><span>Poster Image URL</span><input id="upload-poster-url" name="poster_url" type="url"></label>
					<button type="button" class="cp-button cp-button-secondary" id="cp-upload-thumbnail-btn">Upload Thumbnail Image</button>
					
					<?php 
					$site_type = cp_theme_cp_setting( 'site_type', 'default' );
					if ( in_array( $site_type, array( 'creator_platform', 'gaming', 'podcast' ), true ) ) :
						$genres = get_terms( array( 'taxonomy' => 'cp_genre', 'hide_empty' => false ) );
						if ( ! is_wp_error( $genres ) && ! empty( $genres ) ) :
						?>
						<label><span>Genre</span>
							<select id="upload-genre" name="video_genre">
								<option value=""><?php esc_html_e( 'Select Genre...', 'cp-theme' ); ?></option>
								<?php foreach ( $genres as $genre ) : ?>
									<option value="<?php echo $genre->term_id; ?>"><?php echo esc_html( $genre->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<?php 
						endif;

						if ( in_array( $site_type, array( 'creator_platform', 'podcast' ), true ) ) :
							$topics = get_terms( array( 'taxonomy' => 'cp_topic', 'hide_empty' => false ) );
							if ( ! is_wp_error( $topics ) && ! empty( $topics ) ) :
							?>
							<label><span>Topic</span>
								<select id="upload-topic" name="video_topic">
									<option value=""><?php esc_html_e( 'Select Topic...', 'cp-theme' ); ?></option>
									<?php foreach ( $topics as $topic ) : ?>
										<option value="<?php echo $topic->term_id; ?>"><?php echo esc_html( $topic->name ); ?></option>
									<?php endforeach; ?>
								</select>
							</label>
							<?php 
							endif;
						endif;

						if ( 'gaming' === $site_type ) :
							$games = get_terms( array( 'taxonomy' => 'cp_game', 'hide_empty' => false ) );
							if ( ! is_wp_error( $games ) && ! empty( $games ) ) :
							?>
							<label><span>Game</span>
								<select id="upload-game" name="video_game">
									<option value=""><?php esc_html_e( 'Select Game...', 'cp-theme' ); ?></option>
									<?php foreach ( $games as $game ) : ?>
										<option value="<?php echo $game->term_id; ?>"><?php echo esc_html( $game->name ); ?></option>
									<?php endforeach; ?>
								</select>
							</label>
							<?php 
							endif;
						endif;

						if ( in_array( $site_type, array( 'creator_platform', 'gaming' ), true ) ) :
						?>
						<label><span>Video Tags (comma separated)</span>
							<input id="upload-tags" name="video_tags" type="text" placeholder="e.g. gameplay, tutorial, review">
						</label>
						<?php 
						endif;
					endif;
					?>
					
					<div style="display:flex; justify-content:space-between; margin-top:20px;">
						<button type="button" class="cp-button cp-button-secondary cp-prev-step">Back</button>
						<button type="button" class="cp-button cp-next-step">Next</button>
					</div>
				</div>

				<!-- Step 3 Panel: Video Options -->
				<div class="cp-upload-panel" data-step-panel="3">
					<label><span>Publishing Status</span>
						<select id="upload-status" name="post_status">
							<option value="publish">Publish Immediately</option>
							<option value="draft">Save as Draft</option>
						</select>
					</label>
					<label class="cp-filter-check"><input id="upload-allow-comments" name="allow_comments" type="checkbox" checked value="1"> <span>Allow Comments</span></label>
					
					<div style="display:flex; justify-content:space-between; margin-top:20px;">
						<button type="button" class="cp-button cp-button-secondary cp-prev-step">Back</button>
						<button type="button" class="cp-button cp-next-step">Next</button>
					</div>
				</div>

				<!-- Step 4 Panel: Subtitles & Chapters -->
				<div class="cp-upload-panel" data-step-panel="4">
					<div style="margin-bottom:20px;">
						<h3>Chapter Markers</h3>
						<table id="upload-chapters-table" class="cp-suite-table">
							<thead>
								<tr><th>Time (seconds)</th><th>Chapter Title</th><th>Action</th></tr>
							</thead>
							<tbody></tbody>
						</table>
						<button type="button" class="cp-button cp-button-secondary" id="upload-add-chapter" style="margin-top:10px;">Add Chapter</button>
					</div>

					<div style="margin-bottom:20px;">
						<h3>Subtitles</h3>
						<table id="upload-subtitles-table" class="cp-suite-table">
							<thead>
								<tr><th>Lang Code</th><th>Label</th><th>VTT Source URL</th><th>Default</th><th>Action</th></tr>
							</thead>
							<tbody></tbody>
						</table>
						<button type="button" class="cp-button cp-button-secondary" id="upload-add-subtitle" style="margin-top:10px;">Add Subtitle</button>
					</div>

					<div style="display:flex; justify-content:space-between; margin-top:20px;">
						<button type="button" class="cp-button cp-button-secondary cp-prev-step">Back</button>
						<button type="submit" class="cp-button" id="cp-publish-btn">Publish Video</button>
					</div>
				</div>

			</form>
		</div>
		<?php
		return;
	}
	if ( 'pricing' === $slug ) {
		$plans = cp_theme_get_pricing_plans();
		?>
		<div class="cp-pricing-grid">
			<?php foreach ( $plans as $plan ) : ?>
				<div class="cp-pricing-card <?php echo ! empty( $plan['recommended'] ) ? 'is-recommended' : ''; ?>">
					<?php if ( ! empty( $plan['recommended'] ) ) : ?>
						<span class="cp-pricing-badge"><?php esc_html_e( 'RECOMMENDED', 'cp-theme' ); ?></span>
					<?php endif; ?>
					<div class="cp-pricing-header">
						<h2><?php echo esc_html( $plan['name'] ); ?></h2>
						<p class="cp-pricing-desc"><?php echo esc_html( $plan['description'] ); ?></p>
						<div class="cp-pricing-price"><?php echo esc_html( $plan['price'] ); ?></div>
					</div>
					<ul class="cp-pricing-features">
						<?php foreach ( $plan['features'] as $feature ) : ?>
							<li>
								<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round">
									<polyline points="20 6 9 17 4 12"></polyline>
								</svg>
								<span><?php echo esc_html( $feature ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
					<div class="cp-pricing-action">
						<a href="<?php echo esc_url( $plan['url'] ); ?>" class="cp-button <?php echo ! empty( $plan['recommended'] ) ? '' : 'cp-button-secondary'; ?>">
							<?php echo esc_html( $plan['button_text'] ); ?>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return;
	}
	if ( in_array( $slug, array( 'topics', 'locations', 'product-categories', 'games' ), true ) ) { echo '<div class="cp-directory-grid">'; foreach ( $items as $term ) printf( '<a href="%s"><strong>%s</strong><span>%s items</span></a>', esc_url( get_term_link( $term ) ), esc_html( $term->name ), esc_html( $term->count ) ); echo '</div>'; return; }
	if ( 'groups' === $slug ) { echo '<div class="cp-directory-grid">'; foreach ( $items as $group ) { $joined = is_user_logged_in() && CPWP_Community::is_member( $group->ID ); printf( '<article><h3><a href="%s">%s</a></h3><p>%s</p><button class="cp-button" data-cpwp-group-membership="%s">%s</button><span data-cpwp-group-count="%s">%s members</span></article>', esc_url( get_permalink( $group ) ), esc_html( get_the_title( $group ) ), esc_html( get_the_excerpt( $group ) ), esc_attr( $group->ID ), esc_html( $joined ? 'Leave group' : 'Join group' ), esc_attr( $group->ID ), esc_html( count( (array) get_post_meta( $group->ID, CPWP_Community::MEMBERS, true ) ) ) ); } echo '</div>'; return; }
	if ( in_array( $slug, array( 'channels', 'following' ), true ) ) { echo '<div class="cp-channel-grid">'; foreach ( $items as $item ) printf( '<a href="%s"><img src="%s" alt=""><h3>%s</h3><p>%s</p></a>', esc_url( CPWP_Channels::public_url( $item['channel'] ) ), esc_url( $item['channel']['logo_url'] ?: get_avatar_url( $item['user']->ID ) ), esc_html( $item['channel']['name'] ), esc_html( $item['channel']['description'] ) ); echo '</div>'; return; }
	if ( in_array( $slug, array( 'watch-history', 'student-progress', 'download-history' ), true ) ) { echo '<div class="cp-history-list">'; foreach ( $items as $item ) { $progress = $item->cpwp_progress ?? array(); $status = 'watch-history' === $slug && ! empty( $progress['completed'] ) ? 'Watched' : round( $progress['percent'] ?? 0 ) . '% complete'; printf( '<a href="%s"><strong>%s</strong><span>%s</span><div><i style="width:%s%%"></i></div></a>', esc_url( get_permalink( $item ) ), esc_html( get_the_title( $item ) ), esc_html( $status ), esc_attr( min( 100, $progress['percent'] ?? 0 ) ) ); } echo '</div>'; return; }
	if ( 'quiz-results' === $slug ) { echo '<div class="cp-table-wrap"><table class="cp-suite-table"><thead><tr><th>Quiz</th><th>Attempts</th><th>Best score</th><th>Status</th></tr></thead><tbody>'; foreach ( $items as $quiz_id => $attempts ) { $scores = wp_list_pluck( $attempts, 'score' ); $best = $scores ? max( $scores ) : 0; printf( '<tr><td><a href="%s">%s</a></td><td>%s</td><td>%s%%</td><td>%s</td></tr>', esc_url( get_permalink( $quiz_id ) ), esc_html( get_the_title( $quiz_id ) ), esc_html( count( $attempts ) ), esc_html( $best ), $best >= 70 ? 'Passed' : 'Needs review' ); } echo '</tbody></table></div>'; return; }
	if ( 'certificates' === $slug ) { echo '<div class="cp-certificate-grid">'; foreach ( $items as $course_id => $certificate ) echo '<article><span>Certificate</span><h2>' . esc_html( get_the_title( $course_id ) ) . '</h2><p>Awarded to ' . esc_html( wp_get_current_user()->display_name ) . '</p><small>' . esc_html( get_bloginfo( 'name' ) . ' · ' . wp_date( get_option( 'date_format' ), $certificate['issued'] ) . ' · ' . $certificate['code'] ) . '</small></article>'; echo '</div>'; return; }
	if ( in_array( $slug, array( 'comparisons', 'coupons' ), true ) ) { echo '<div class="cp-table-wrap"><table class="cp-suite-table"><thead><tr><th>Product</th><th>Merchant</th><th>Price</th><th>Coupon</th><th>Expiry</th><th>Link</th></tr></thead><tbody>'; foreach ( $items as $item ) printf( '<tr><td><a href="%s">%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a class="cp-button" rel="nofollow sponsored" target="_blank" href="%s">View deal</a></td></tr>', esc_url( get_permalink( $item ) ), esc_html( get_the_title( $item ) ), esc_html( get_post_meta( $item->ID, CPWP_Affiliate::MERCHANT, true ) ), esc_html( get_post_meta( $item->ID, CPWP_Affiliate::PRICE, true ) ), esc_html( get_post_meta( $item->ID, CPWP_Affiliate::COUPON, true ) ), esc_html( get_post_meta( $item->ID, CPWP_Affiliate::EXPIRY, true ) ), esc_url( CPWP_Affiliate::url( $item->ID ) ) ); echo '</tbody></table></div>'; return; }
	if ( 'tournaments' === $slug ) { echo '<div class="cp-bracket-grid">'; foreach ( $items as $item ) printf( '<article><span>%s</span><h2>%s</h2><p>%s</p><a href="%s">View tournament</a></article>', esc_html( get_post_meta( $item->ID, '_cpwp_deadline', true ) ), esc_html( get_the_title( $item ) ), esc_html( get_the_excerpt( $item ) ), esc_url( get_permalink( $item ) ) ); echo '</div>'; return; }
	if ( 'manager-reports' === $slug ) { echo '<div class="cp-table-wrap"><table class="cp-suite-table"><thead><tr><th>Employee</th><th>Course</th><th>Progress</th><th>Deadline</th><th>Status</th></tr></thead><tbody>'; foreach ( $items as $row ) printf( '<tr><td>%s</td><td><a href="%s">%s</a></td><td>%s%%</td><td>%s</td><td>%s</td></tr>', esc_html( $row['user']->display_name ), esc_url( get_permalink( $row['course'] ) ), esc_html( get_the_title( $row['course'] ) ), esc_html( $row['progress'] ), esc_html( $row['deadline'] ?: 'Not set' ), $row['complete'] ? 'Complete' : 'Assigned' ); echo '</tbody></table></div>'; return; }
	if ( in_array( $slug, array( 'assignments', 'deadlines' ), true ) ) { echo '<div class="cp-assignment-list">'; foreach ( $items as $item ) printf( '<a href="%s"><time>%s</time><strong>%s</strong><span>%s</span></a>', esc_url( get_permalink( $item ) ), esc_html( get_post_meta( $item->ID, '_cpwp_deadline', true ) ), esc_html( get_the_title( $item ) ), esc_html( get_post_type_object( $item->post_type )->labels->singular_name ) ); echo '</div>'; return; }
	if ( in_array( $slug, array( 'community', 'community-feed' ), true ) ) { echo '<div class="cp-community-feed">'; foreach ( $items as $item ) printf( '<article><header>%s <strong>%s</strong></header><h2><a href="%s">%s</a></h2><p>%s</p></article>', get_avatar( $item->post_author, 42 ), esc_html( get_the_author_meta( 'display_name', $item->post_author ) ), esc_url( get_permalink( $item ) ), esc_html( get_the_title( $item ) ), esc_html( get_the_excerpt( $item ) ) ); echo '</div>'; return; }
	echo '<div class="cp-theme-grid">'; foreach ( $items as $item ) cp_theme_video_card( $item->ID ); echo '</div>';
}

function cp_theme_report_dialog() {
	if ( ! is_user_logged_in() ) return;
	?><dialog class="cp-report-dialog" data-cp-report-dialog aria-labelledby="cp-report-title"><form method="dialog" class="cp-report-form"><button class="cp-dialog-close" value="cancel" aria-label="<?php esc_attr_e( 'Close report form', 'cp-theme' ); ?>">×</button><h2 id="cp-report-title"><?php esc_html_e( 'Submit a report', 'cp-theme' ); ?></h2><input type="hidden" data-report-type><input type="hidden" data-report-target><label><span><?php esc_html_e( 'Reason', 'cp-theme' ); ?></span><select data-report-reason required><option value=""><?php esc_html_e( 'Select a reason', 'cp-theme' ); ?></option><option>Spam or misleading</option><option>Harassment or abuse</option><option>Copyright infringement</option><option>Incorrect moderation decision</option><option>Other</option></select></label><label><span><?php esc_html_e( 'Details', 'cp-theme' ); ?></span><textarea data-report-details rows="6" required></textarea></label><label><span><?php esc_html_e( 'Evidence URL (optional)', 'cp-theme' ); ?></span><input data-report-evidence type="url"></label><p class="cp-report-message" role="status" aria-live="polite"></p><button type="button" class="cp-button" data-report-submit><?php esc_html_e( 'Submit for review', 'cp-theme' ); ?></button></form></dialog><?php
}
add_action( 'wp_footer', 'cp_theme_report_dialog', 5 );

function cp_theme_module_single() {
	$type = get_post_type(); $labels = get_post_type_object( $type ); $site_type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
	?><article class="cp-module-single cp-module-<?php echo esc_attr( $site_type ); ?>"><header class="cp-module-hero"><?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large' ); ?><div><span class="cp-kicker"><?php echo esc_html( $labels->labels->singular_name ?? 'Content' ); ?></span><h1><?php the_title(); ?></h1><?php the_excerpt(); ?></div></header><div class="cp-module-body"><?php the_content(); ?><dl class="cp-module-data"><?php foreach ( array( '_cpwp_order' => 'Order', '_cpwp_deadline' => 'Date / deadline', '_cpwp_badge' => 'Label' ) as $key => $label ) { $value = get_post_meta( get_the_ID(), $key, true ); if ( $value ) printf( '<div><dt>%s</dt><dd>%s</dd></div>', esc_html( $label ), esc_html( $value ) ); } ?></dl></div><?php cp_theme_site_type_details( $type, get_the_ID(), $site_type ); ?></article><?php
}

function cp_theme_site_type_details( $type, $post_id, $site_type ) {
	if ( ! class_exists( 'CPWP_Site_Modules' ) ) return;
	if ( 'streaming' === $site_type && 'cp_series' === $type ) {
		$videos = class_exists( 'CPWP_Streaming' ) ? CPWP_Streaming::episodes( $post_id ) : CPWP_Site_Modules::related_videos( get_the_title( $post_id ) ); $seasons = array();
		foreach ( $videos as $video ) $seasons[ absint( get_post_meta( $video->ID, '_cpwp_season', true ) ) ?: 1 ][] = $video;
		echo '<section class="cp-preset-panel"><h2>Seasons and episodes</h2>'; foreach ( $seasons as $season => $episodes ) { echo '<details open><summary>Season ' . esc_html( $season ) . '</summary><div class="cp-episode-list">'; foreach ( $episodes as $episode ) cp_theme_episode_row( $episode ); echo '</div></details>'; } echo '</section>';
	} elseif ( in_array( $site_type, array( 'courses', 'business_training' ), true ) && 'cp_course' === $type ) {
		$lessons = CPWP_Site_Modules::children( $post_id, array( 'cp_lesson', 'cp_quiz' ) ); $user_id = get_current_user_id(); $completed = $user_id ? CPWP_Learning::lesson_completions( $user_id ) : array(); $attempts = $user_id ? CPWP_Learning::attempts( $user_id ) : array(); $enrolled = in_array( $post_id, CPWP_Learning::enrolled_courses( $user_id ), true ); $assigned = in_array( $post_id, CPWP_Learning::assigned_courses( $user_id ), true );
		echo '<section class="cp-preset-panel"><h2>Curriculum</h2>'; if ( $user_id ) { if ( ! $enrolled && ! $assigned ) echo '<button class="cp-button" data-cpwp-enroll-course="' . esc_attr( $post_id ) . '">Enroll in course</button>'; else echo '<p><strong>' . esc_html( $assigned ? 'Assigned training' : 'Enrolled' ) . ' · ' . esc_html( CPWP_Learning::course_progress( $user_id, $post_id ) ) . '% complete</strong></p>'; } echo '<div class="cp-curriculum">'; foreach ( $lessons as $item ) { $done = 'cp_lesson' === $item->post_type ? in_array( $item->ID, $completed, true ) : ! empty( array_filter( $attempts[ $item->ID ] ?? array(), function ( $a ) { return ! empty( $a['passed'] ); } ) ); echo '<a href="' . esc_url( get_permalink( $item ) ) . '"><span>' . ( $done ? '&#10003;' : esc_html( get_post_meta( $item->ID, '_cpwp_order', true ) ?: '•' ) ) . '</span><strong>' . esc_html( get_the_title( $item ) ) . '</strong><small>' . esc_html( get_post_type_object( $item->post_type )->labels->singular_name ) . '</small></a>'; } echo '</div><div class="cp-instructor">'; echo get_avatar( get_post_field( 'post_author', $post_id ), 64 ) . '<div><strong>Instructor</strong><p>' . esc_html( get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ) ) . '</p></div></div></section>';
	} elseif ( in_array( $site_type, array( 'courses', 'business_training' ), true ) && 'cp_lesson' === $type && is_user_logged_in() ) {
		$done = in_array( $post_id, CPWP_Learning::lesson_completions(), true ); echo '<section class="cp-preset-panel"><h2>Lesson completion</h2><button class="cp-button" data-cpwp-complete-lesson="' . esc_attr( $post_id ) . '"' . disabled( $done, true, false ) . '>' . esc_html( $done ? 'Lesson completed' : 'Mark lesson complete' ) . '</button></section>';
	} elseif ( in_array( $site_type, array( 'courses', 'business_training' ), true ) && 'cp_quiz' === $type && is_user_logged_in() ) {
		$questions = (array) get_post_meta( $post_id, CPWP_Learning::QUIZ_QUESTIONS, true ); echo '<section class="cp-preset-panel"><h2>Take quiz</h2><form data-cpwp-quiz="' . esc_attr( $post_id ) . '">'; foreach ( $questions as $index => $question ) { echo '<fieldset><legend>' . esc_html( $question['question'] ) . '</legend>'; foreach ( $question['options'] as $option_index => $option ) echo '<label><input required type="radio" name="answer-' . esc_attr( $index ) . '" value="' . esc_attr( $option_index ) . '"> ' . esc_html( $option ) . '</label>'; echo '</fieldset>'; } echo '<button class="cp-button">Submit quiz</button><p data-cpwp-quiz-result aria-live="polite"></p></form></section>';
	} elseif ( 'podcast' === $site_type && 'cp_series' === $type ) {
		$episodes = CPWP_Site_Modules::related_videos( get_the_title( $post_id ) ); $guests = CPWP_Site_Modules::children( $post_id, 'cp_person' );
		cp_theme_people_and_items( 'Guests', $guests ); echo '<section class="cp-preset-panel"><h2>Episodes</h2><div class="cp-episode-list">'; foreach ( $episodes as $episode ) cp_theme_episode_row( $episode ); echo '</div></section>';
	} elseif ( 'news' === $site_type ) {
		$topics = get_the_terms( $post_id, 'cp_topic' ); $locations = get_the_terms( $post_id, 'cp_location' ); $correction = get_post_meta( $post_id, '_cpwp_correction', true );
		echo '<section class="cp-preset-panel cp-news-details"><h2>Story details</h2>'; if ( $topics ) echo '<p><strong>Topics:</strong> ' . esc_html( implode( ', ', wp_list_pluck( $topics, 'name' ) ) ) . '</p>'; if ( $locations ) echo '<p><strong>Locations:</strong> ' . esc_html( implode( ', ', wp_list_pluck( $locations, 'name' ) ) ) . '</p>'; if ( $correction ) echo '<aside><strong>Correction:</strong> ' . esc_html( $correction ) . '</aside>'; echo '</section>';
	} elseif ( 'affiliate' === $site_type && 'cp_product' === $type ) {
		$url = get_post_meta( $post_id, '_cpwp_external_url', true ); echo '<section class="cp-preset-panel cp-product-panel"><p class="cp-disclosure">This page may contain affiliate links.</p><dl class="cp-module-data">'; foreach ( array( CPWP_Affiliate::MERCHANT => 'Merchant', CPWP_Affiliate::PRICE => 'Price', CPWP_Affiliate::COUPON => 'Coupon', CPWP_Affiliate::EXPIRY => 'Coupon expiry' ) as $key => $label ) { $value = get_post_meta( $post_id, $key, true ); if ( $value ) printf( '<div><dt>%s</dt><dd>%s</dd></div>', esc_html( $label ), esc_html( $value ) ); } echo '</dl>'; if ( $url ) echo '<a class="cp-button" rel="nofollow sponsored" target="_blank" href="' . esc_url( CPWP_Affiliate::url( $post_id ) ) . '">View product</a>'; echo '</section>';
	} elseif ( 'gaming' === $site_type ) {
		$games = get_the_terms( $post_id, 'cp_game' ); if ( $games ) echo '<section class="cp-preset-panel"><h2>Game</h2><div class="cp-category-row">' . implode( '', array_map( function ( $game ) { return '<a class="cp-category-pill" href="' . esc_url( get_term_link( $game ) ) . '">' . esc_html( $game->name ) . '</a>'; }, $games ) ) . '</div></section>';
	} elseif ( 'cp_group' === $type && class_exists( 'CPWP_Community' ) ) {
		$joined = is_user_logged_in() && CPWP_Community::is_member( $post_id ); echo '<section class="cp-preset-panel"><h2>Group community</h2><button class="cp-button" data-cpwp-group-membership="' . esc_attr( $post_id ) . '">' . esc_html( $joined ? 'Leave group' : 'Join group' ) . '</button><span data-cpwp-group-count="' . esc_attr( $post_id ) . '">' . esc_html( count( (array) get_post_meta( $post_id, CPWP_Community::MEMBERS, true ) ) ) . ' members</span>'; if ( $joined || CPWP_Community::can_moderate( $post_id ) ) { echo '<div class="cp-community-feed">'; foreach ( CPWP_Site_Modules::children( $post_id, 'cp_community' ) as $post ) echo '<article><h3><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></h3><p>' . esc_html( get_the_excerpt( $post ) ) . '</p></article>'; echo '</div>'; } echo '</section>';
	}
}

function cp_theme_episode_row( $episode ) { printf( '<a class="cp-episode-row" href="%s"><span>%s</span><strong>%s</strong><small>%s</small></a>', esc_url( get_permalink( $episode ) ), esc_html( get_post_meta( $episode->ID, '_cpwp_episode', true ) ?: '▶' ), esc_html( get_the_title( $episode ) ), esc_html( get_the_date( '', $episode ) ) ); }
function cp_theme_people_and_items( $title, $items ) { if ( ! $items ) return; echo '<section class="cp-preset-panel"><h2>' . esc_html( $title ) . '</h2><div class="cp-people-grid">'; foreach ( $items as $item ) echo '<a href="' . esc_url( get_permalink( $item ) ) . '">' . get_the_post_thumbnail( $item, 'thumbnail' ) . '<strong>' . esc_html( get_the_title( $item ) ) . '</strong></a>'; echo '</div></section>'; }

function cp_theme_preset_home_sections() {
	if ( ! class_exists( 'CPWP_Site_Modules' ) ) return; $type = cp_theme_cp_setting( 'site_type', 'creator_platform' );
	if ( 'creator_platform' === $type ) { echo '<section class="cp-section"><div class="cp-section-head"><h2>Creator channels</h2></div><div class="cp-channel-grid">'; foreach ( CPWP_Site_Modules::channels() as $item ) echo '<a href="' . esc_url( CPWP_Channels::public_url( $item['channel'] ) ) . '"><img src="' . esc_url( $item['channel']['logo_url'] ?? get_avatar_url( $item['user']->ID ) ) . '" alt=""><h3>' . esc_html( $item['channel']['name'] ) . '</h3><p>' . esc_html( $item['channel']['description'] ) . '</p></a>'; echo '</div></section>'; }
	if ( 'streaming' === $type && class_exists( 'CPWP_Page_Suites' ) ) {
		cp_theme_content_section( 'Featured series', 'cp_series' );
		cp_theme_video_section( 'Trending Movies', array( 'meta_query' => array( array( 'key' => '_cpwp_streaming_type', 'value' => 'movie' ) ), 'meta_key' => '_cpwp_views', 'orderby' => 'meta_value_num' ), CPWP_Page_Suites::url( 'movies' ) );
		cp_theme_video_section( 'Latest TV Episodes', array( 'meta_query' => array( array( 'key' => '_cpwp_streaming_type', 'value' => 'episode' ) ) ), CPWP_Page_Suites::url( 'tv-shows' ) );
	} else {
		foreach ( array( 'streaming' => array( 'cp_series', 'Featured series' ), 'courses' => array( 'cp_course', 'Courses' ), 'podcast' => array( 'cp_series', 'Podcast shows' ), 'news' => array( 'cp_news', 'Breaking and latest news' ), 'affiliate' => array( 'cp_product', 'Featured products' ), 'gaming' => array( 'cp_event', 'Tournaments and events' ), 'business_training' => array( 'cp_course', 'Assigned training' ) ) as $preset => $config ) if ( $type === $preset ) cp_theme_content_section( $config[1], $config[0] );
	}
	if ( 'business_training' === $type && is_user_logged_in() ) cp_theme_training_dashboard();
}

function cp_theme_content_section( $title, $post_type ) { $items = get_posts( array( 'post_type' => $post_type, 'posts_per_page' => 8 ) ); if ( ! $items ) return; echo '<section class="cp-section"><div class="cp-section-head"><h2>' . esc_html( $title ) . '</h2><a class="cp-section-link" href="' . esc_url( get_post_type_archive_link( $post_type ) ) . '">View all</a></div><div class="cp-theme-grid">'; foreach ( $items as $item ) cp_theme_video_card( $item->ID ); echo '</div></section>'; }
function cp_theme_training_dashboard() { $courses = CPWP_Learning::assigned_courses(); $complete = count( array_filter( $courses, function ( $course ) { return CPWP_Learning::course_complete( get_current_user_id(), $course ); } ) ); echo '<section class="cp-training-dashboard"><div><strong>' . esc_html( count( $courses ) ) . '</strong><span>Assigned courses</span></div><div><strong>' . esc_html( $complete ) . '</strong><span>Completed</span></div><div><strong>' . esc_html( count( $courses ) - $complete ) . '</strong><span>Outstanding</span></div></section>'; }

function cp_theme_apply_video_filters( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ( ! $query->is_search() && ! $query->is_post_type_archive( 'cp_video' ) ) ) return;
	$query->set( 'post_type', 'cp_video' ); $meta = array();
	foreach ( array( 'series' => '_cpwp_series_name', 'rating' => '_cpwp_age_rating' ) as $param => $key ) if ( ! empty( $_GET[ $param ] ) ) $meta[] = array( 'key' => $key, 'value' => sanitize_text_field( wp_unslash( $_GET[ $param ] ) ), 'compare' => 'LIKE' );
	if ( ! empty( $_GET['vertical'] ) ) $meta[] = array( 'key' => '_cpwp_vertical', 'value' => '1' );
	if ( $meta ) $query->set( 'meta_query', $meta );
	if ( 'views' === sanitize_key( wp_unslash( $_GET['sort'] ?? '' ) ) ) { $query->set( 'meta_key', '_cpwp_views' ); $query->set( 'orderby', 'meta_value_num' ); $query->set( 'order', 'DESC' ); }
}
add_action( 'pre_get_posts', 'cp_theme_apply_video_filters', 20 );

function cp_theme_filter_form() {
	?><form class="cp-advanced-filters" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"><input type="hidden" name="post_type" value="cp_video"><label><span><?php esc_html_e( 'Search', 'cp-theme' ); ?></span><input name="s" value="<?php echo esc_attr( get_search_query() ); ?>"></label><label><span><?php esc_html_e( 'Series or show', 'cp-theme' ); ?></span><input name="series" value="<?php echo esc_attr( wp_unslash( $_GET['series'] ?? '' ) ); ?>"></label><label><span><?php esc_html_e( 'Age rating', 'cp-theme' ); ?></span><input name="rating" value="<?php echo esc_attr( wp_unslash( $_GET['rating'] ?? '' ) ); ?>"></label><label><span><?php esc_html_e( 'Sort', 'cp-theme' ); ?></span><select name="sort"><option value="date">Newest</option><option value="views" <?php selected( wp_unslash( $_GET['sort'] ?? '' ), 'views' ); ?>>Most viewed</option></select></label><label class="cp-filter-check"><input type="checkbox" name="vertical" value="1" <?php checked( ! empty( $_GET['vertical'] ) ); ?>> <span><?php esc_html_e( 'Vertical videos only', 'cp-theme' ); ?></span></label><button class="cp-button"><?php esc_html_e( 'Apply filters', 'cp-theme' ); ?></button></form><?php
}

function cp_theme_video_details( $post_id ) {
	$items = array( '_cpwp_series_name' => 'Series / Course / Show', '_cpwp_season' => 'Season / Section', '_cpwp_episode' => 'Episode / Lesson', '_cpwp_age_rating' => 'Age rating', '_cpwp_correction' => 'Fact-check / Correction' );
	echo '<div class="cp-module-details">';
	foreach ( $items as $key => $label ) { $value = get_post_meta( $post_id, $key, true ); if ( $value ) printf( '<p><strong>%s:</strong> %s</p>', esc_html( $label ), esc_html( $value ) ); }
	$download = get_post_meta( $post_id, '_cpwp_download_url', true ); $affiliate = get_post_meta( $post_id, '_cpwp_affiliate_url', true );
	if ( $download && class_exists( 'CPWP_Security' ) ) printf( '<a class="cp-button" href="%s">%s</a> ', esc_url( CPWP_Security::download_url( $post_id ) ), esc_html__( 'Download', 'cp-theme' ) );
	if ( $affiliate ) printf( '<a class="cp-button" href="%s" rel="nofollow sponsored" target="_blank">%s</a>', esc_url( $affiliate ), esc_html__( 'View product', 'cp-theme' ) );
	echo '</div>';
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
					<?php if ( cp_theme_cp_setting( 'enable_comment_reactions', true ) ) : ?>
					<button class="comment-like-btn" aria-label="Like" data-comment-id="<?php comment_ID(); ?>">
						<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
						<span class="like-count"><?php echo esc_html( absint( get_comment_meta( $comment->comment_ID, '_cpwp_comment_likes', true ) ) ); ?></span>
					</button>
					<button class="comment-dislike-btn" aria-label="Dislike" data-comment-id="<?php comment_ID(); ?>">
						<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"></path></svg>
						<span class="dislike-count"><?php echo esc_html( absint( get_comment_meta( $comment->comment_ID, '_cpwp_comment_dislikes', true ) ) ); ?></span>
					</button>
					<?php endif; ?>
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

function cp_theme_ensure_default_terms() {
	$site_type = cp_theme_cp_setting( 'site_type', 'default' );
	
	// Ensure cp_genre terms
	$genres = get_terms( array( 'taxonomy' => 'cp_genre', 'hide_empty' => false ) );
	if ( empty( $genres ) || is_wp_error( $genres ) ) {
		wp_insert_term( 'Entertainment', 'cp_genre' );
		wp_insert_term( 'Music', 'cp_genre' );
		wp_insert_term( 'Gaming', 'cp_genre' );
		wp_insert_term( 'Education', 'cp_genre' );
	}
	
	// Ensure cp_topic terms
	if ( in_array( $site_type, array( 'creator_platform', 'podcast' ), true ) ) {
		$topics = get_terms( array( 'taxonomy' => 'cp_topic', 'hide_empty' => false ) );
		if ( empty( $topics ) || is_wp_error( $topics ) ) {
			wp_insert_term( 'Tech & Gadgets', 'cp_topic' );
			wp_insert_term( 'Daily Vlogs', 'cp_topic' );
			wp_insert_term( 'Comedy & Memes', 'cp_topic' );
			wp_insert_term( 'Interviews', 'cp_topic' );
		}
	}
	
	// Ensure cp_game terms
	if ( 'gaming' === $site_type ) {
		$games = get_terms( array( 'taxonomy' => 'cp_game', 'hide_empty' => false ) );
		if ( empty( $games ) || is_wp_error( $games ) ) {
			wp_insert_term( 'Valorant', 'cp_game' );
			wp_insert_term( 'Minecraft', 'cp_game' );
			wp_insert_term( 'Fortnite', 'cp_game' );
			wp_insert_term( 'Apex Legends', 'cp_game' );
		}
	}
}

function cp_theme_footer_menu_fallback() {
	$links = array();
	
	// Add Privacy Policy if configured in WP
	$privacy_url = get_privacy_policy_url();
	if ( $privacy_url ) {
		$links[] = '<a href="' . esc_url( $privacy_url ) . '">' . esc_html__( 'Privacy Policy', 'cp-theme' ) . '</a>';
	}
	
	// Add standard pages like About, Contact, Terms if they exist
	$pages = get_pages( array(
		'post_status' => 'publish',
		'number'      => 10
	) );
	
	$seen_urls = array();
	if ( $privacy_url ) {
		$seen_urls[] = $privacy_url;
	}
	
	foreach ( $pages as $page ) {
		$permalink = get_permalink( $page->ID );
		if ( in_array( $permalink, $seen_urls, true ) ) {
			continue;
		}
		
		$title_lower = strtolower( $page->post_title );
		// Filter for common footer links
		if ( preg_match( '/(about|contact|terms|privacy|tos|support|faq|help|service)/', $title_lower ) ) {
			$links[] = '<a href="' . esc_url( $permalink ) . '">' . esc_html( $page->post_title ) . '</a>';
			$seen_urls[] = $permalink;
		}
	}
	
	// Fallback to top-level pages if still empty
	if ( empty( $links ) ) {
		foreach ( array_slice( $pages, 0, 3 ) as $page ) {
			$permalink = get_permalink( $page->ID );
			if ( ! in_array( $permalink, $seen_urls, true ) ) {
				$links[] = '<a href="' . esc_url( $permalink ) . '">' . esc_html( $page->post_title ) . '</a>';
			}
		}
	}
	
	if ( ! empty( $links ) ) {
		echo '<nav class="cp-footer-nav">';
		echo implode( '', $links );
		echo '</nav>';
	}
}

