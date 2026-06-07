<?php
/**
 * Template: Gaming — Single Video (Twitch style)
 * Full width theater mode player + chat/sidebar on right.
 */
get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-twitch-content cp-twitch-watch-content">
		<?php while ( have_posts() ) : the_post();
			$post_id = get_the_ID();
			$author  = get_the_author_meta( 'display_name', $post->post_author );
			$avatar  = get_avatar_url( $post->post_author );
			$views   = absint( get_post_meta( $post_id, '_cpwp_views', true ) );
			$games   = get_the_terms( $post_id, 'cp_game' );
			$owner   = $post->post_author;
			$subs    = class_exists( 'CPWP_Channels' ) ? count( CPWP_Channels::followers( $owner ) ) : rand( 1000, 50000 );
		?>
		<div class="cp-twitch-watch-layout">

			<!-- Stream / Video Area -->
			<div class="cp-twitch-main-col">
				<div class="cp-twitch-player-wrap">
					<?php the_content(); ?>
					<div class="cp-twitch-player-overlay">
						<span class="cp-twitch-live-badge"><?php esc_html_e( 'LIVE', 'cp-theme' ); ?></span>
						<span class="cp-twitch-viewers-badge">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
							<?php echo number_format_i18n( $views ?: rand( 500, 10000 ) ); ?>
						</span>
					</div>
				</div>

				<div class="cp-twitch-watch-info">
					<div class="cp-twitch-info-left">
						<img src="<?php echo esc_url( $avatar ); ?>" alt="" class="cp-twitch-watch-avatar">
						<div class="cp-twitch-stream-details">
							<h1 class="cp-twitch-watch-title"><?php the_title(); ?></h1>
							<div class="cp-twitch-stream-meta">
								<a href="#" class="cp-twitch-author-name"><?php echo esc_html( $author ); ?></a>
								<?php if ( ! empty( $games ) ) : ?>
								<span class="cp-twitch-meta-divider">·</span>
								<a href="<?php echo esc_url( get_term_link( $games[0] ) ); ?>" class="cp-twitch-game-link"><?php echo esc_html( $games[0]->name ); ?></a>
								<?php endif; ?>
							</div>
							<div class="cp-twitch-tags">
								<span class="cp-twitch-tag">English</span>
								<span class="cp-twitch-tag">Competitive</span>
							</div>
						</div>
					</div>

					<div class="cp-twitch-info-right">
						<div class="cp-twitch-follow-wrap">
							<?php if ( is_user_logged_in() && get_current_user_id() !== (int) $owner ) : ?>
							<button class="cp-twitch-btn cp-twitch-btn-follow" data-cpwp-follow-channel="<?php echo esc_attr( $owner ); ?>">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
								<?php esc_html_e( 'Follow', 'cp-theme' ); ?>
							</button>
							<button class="cp-twitch-btn cp-twitch-btn-sub">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
								<?php esc_html_e( 'Subscribe', 'cp-theme' ); ?>
							</button>
							<?php endif; ?>
						</div>
						<div class="cp-twitch-report-wrap">
							<?php if ( is_user_logged_in() ) : ?>
							<button class="cp-twitch-icon-btn" data-cpwp-report="content" data-target-id="<?php the_ID(); ?>" title="<?php esc_attr_e( 'Report', 'cp-theme' ); ?>">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
							</button>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="cp-twitch-about-section">
					<h3><?php esc_html_e( 'About', 'cp-theme' ); ?> <?php echo esc_html( $author ); ?></h3>
					<div class="cp-twitch-about-content">
						<div class="cp-twitch-about-stats">
							<span><strong><?php echo number_format_i18n( $subs ); ?></strong> followers</span>
						</div>
						<div class="cp-twitch-about-desc">
							<?php cp_theme_video_details( $post_id ); ?>
						</div>
					</div>
				</div>
			</div>

			<!-- Chat / Related sidebar -->
			<aside class="cp-twitch-chat-col">
				<div class="cp-twitch-chat-header">
					<h3><?php esc_html_e( 'Stream Chat', 'cp-theme' ); ?></h3>
				</div>
				<div class="cp-twitch-chat-body">
					<?php if ( comments_open() || get_comments_number() ) : ?>
						<?php comments_template(); ?>
					<?php else : ?>
						<p class="cp-twitch-chat-disabled"><?php esc_html_e( 'Chat is disabled for this stream.', 'cp-theme' ); ?></p>
					<?php endif; ?>
				</div>
			</aside>

		</div>
		<?php endwhile; ?>
	</div>
</div>

<?php get_footer(); ?>
