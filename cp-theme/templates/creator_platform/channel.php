<?php
/**
 * Template: Creator Platform — Channel Page
 * Full YouTube-style channel profile:
 * banner, logo, name, subscriber count, follow button, featured video, videos grid.
 * This is loaded via the existing cpwp_channel query var routing.
 */
get_header();
$channel = get_query_var( 'cpwp_public_channel' );
$owner   = absint( get_query_var( 'cpwp_channel_owner' ) );
if ( ! $channel || ! $owner ) {
	echo '<div class="cp-shell"><p>' . esc_html__( 'Channel not found.', 'cp-theme' ) . '</p></div>';
	get_footer();
	return;
}

$subs      = class_exists( 'CPWP_Channels' ) ? count( CPWP_Channels::followers( $owner ) ) : 0;
$following = is_user_logged_in() && class_exists( 'CPWP_Channels' ) && in_array( get_current_user_id(), CPWP_Channels::followers( $owner ), true );
$is_owner  = is_user_logged_in() && ( get_current_user_id() === $owner );
$studio_url = add_query_arg( 'cpwp_suite', 'studio', home_url( '/' ) );

// Videos by this channel.
$videos = new WP_Query( array(
	'post_type'      => 'cp_video',
	'posts_per_page' => 30,
	'meta_key'       => '_cpwp_channel_owner',
	'meta_value'     => $owner,
) );

$filter_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'videos';
?>
<div class="cp-shell cp-yt-channel-shell" style="--cp-accent:<?php echo esc_attr( $channel['accent_color'] ?? '#6d5dfc' ); ?>">

	<!-- Banner -->
	<?php if ( ! empty( $channel['banner_url'] ) ) : ?>
	<div class="cp-yt-channel-banner">
		<img src="<?php echo esc_url( $channel['banner_url'] ); ?>" alt="">
	</div>
	<?php endif; ?>

	<!-- Channel header -->
	<div class="cp-yt-channel-header">
		<img class="cp-yt-channel-logo" src="<?php echo esc_url( $channel['logo_url'] ?: get_avatar_url( $owner ) ); ?>" alt="">
		<div class="cp-yt-channel-info">
			<h1 class="cp-yt-channel-name">
				<?php echo esc_html( $channel['name'] ); ?>
				<?php if ( class_exists( 'CPWP_Creator_Platform' ) && 'approved' === get_user_meta( $owner, CPWP_Creator_Platform::VERIFY_STATUS, true ) ) : ?>
				<span class="cp-yt-verified" title="<?php esc_attr_e( 'Verified creator', 'cp-theme' ); ?>">✓</span>
				<?php endif; ?>
			</h1>
			<p class="cp-yt-channel-handle">@<?php echo esc_html( sanitize_title( $channel['name'] ) ); ?></p>
			<p class="cp-yt-channel-stats">
				<strong><?php echo esc_html( number_format_i18n( $subs ) ); ?></strong> <?php esc_html_e( 'subscribers', 'cp-theme' ); ?>
				· <strong><?php echo esc_html( number_format_i18n( $videos->found_posts ) ); ?></strong> <?php esc_html_e( 'videos', 'cp-theme' ); ?>
			</p>
			<?php if ( $channel['description'] ) : ?>
			<p class="cp-yt-channel-desc"><?php echo esc_html( wp_trim_words( $channel['description'], 30 ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="cp-yt-channel-actions">
			<?php if ( $is_owner ) : ?>
			<a href="<?php echo esc_url( $studio_url ); ?>" class="cp-yt-channel-studio-btn"><?php esc_html_e( 'Manage channel', 'cp-theme' ); ?></a>
			<?php elseif ( is_user_logged_in() ) : ?>
			<button class="cp-yt-subscribe-btn <?php echo $following ? 'is-following' : ''; ?>" data-cpwp-follow-channel="<?php echo esc_attr( $owner ); ?>">
				<?php echo $following ? esc_html__( 'Subscribed', 'cp-theme' ) : esc_html__( 'Subscribe', 'cp-theme' ); ?>
			</button>
			<?php if ( $following ) : ?>
			<button class="cp-yt-preferences-btn" data-cpwp-sub-preferences="<?php echo esc_attr( $owner ); ?>">🔔</button>
			<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>

	<!-- Channel tabs -->
	<nav class="cp-yt-channel-tabs">
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'videos' ) ); ?>" class="cp-yt-channel-tab <?php echo 'videos' === $filter_tab ? 'is-active' : ''; ?>"><?php esc_html_e( 'Videos', 'cp-theme' ); ?></a>
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'about' ) ); ?>" class="cp-yt-channel-tab <?php echo 'about' === $filter_tab ? 'is-active' : ''; ?>"><?php esc_html_e( 'About', 'cp-theme' ); ?></a>
	</nav>

	<div class="cp-yt-channel-content">

		<?php if ( 'about' === $filter_tab ) : ?>
		<!-- About tab -->
		<div class="cp-yt-channel-about">
			<?php if ( $channel['description'] ) : ?>
			<h2><?php esc_html_e( 'Description', 'cp-theme' ); ?></h2>
			<p><?php echo esc_html( $channel['description'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $channel['category'] ) ) : ?>
			<p><strong><?php esc_html_e( 'Category:', 'cp-theme' ); ?></strong> <?php echo esc_html( $channel['category'] ); ?></p>
			<?php endif; ?>
			<p><strong><?php esc_html_e( 'Joined:', 'cp-theme' ); ?></strong> <?php echo esc_html( get_the_author_meta( 'registered', $owner ) ? date_i18n( get_option( 'date_format' ), strtotime( get_the_author_meta( 'registered', $owner ) ) ) : '' ); ?></p>
		</div>

		<?php else : ?>
		<!-- Videos tab -->
		<?php if ( ! empty( $channel['featured_video'] ) ) : ?>
		<div class="cp-yt-channel-featured">
			<h2><?php esc_html_e( 'Featured', 'cp-theme' ); ?></h2>
			<div class="cp-yt-grid">
				<?php cp_theme_video_card( absint( $channel['featured_video'] ) ); ?>
			</div>
		</div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'All videos', 'cp-theme' ); ?></h2>
		<div class="cp-yt-grid">
			<?php while ( $videos->have_posts() ) : $videos->the_post(); cp_theme_video_card( get_the_ID() ); endwhile; wp_reset_postdata(); ?>
		</div>

		<?php if ( ! $videos->have_posts() ) : ?>
		<p class="cp-yt-empty-msg"><?php esc_html_e( 'This channel has no videos yet.', 'cp-theme' ); ?></p>
		<?php endif; ?>
		<?php endif; ?>

	</div>

</div>

<?php get_footer(); ?>
