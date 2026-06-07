<?php get_header(); ?>
<div class="cp-shell"><section class="cp-state-page" role="alert">
	<span class="cp-state-code">403</span>
	<h1><?php 
		if ( 'cp_video' === get_post_type() ) {
			esc_html_e( 'This video is unavailable', 'cp-theme' );
		} else {
			esc_html_e( 'This content is unavailable', 'cp-theme' );
		}
	?></h1>
	<p><?php echo esc_html( get_query_var( 'cpwp_unavailable_message', __( 'You do not have permission to view this content.', 'cp-theme' ) ) ); ?></p>

	<?php if ( class_exists( 'CPWP_Settings' ) && CPWP_Settings::get( 'enable_subscriptions' ) ) : ?>
		<div class="cp-premium-cta" style="margin-top: 30px; padding: 25px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; backdrop-filter: blur(10px); max-width: 500px; margin-left: auto; margin-right: auto; text-align: center;">
			<div class="cp-premium-icon" style="font-size: 2.5rem; margin-bottom: 15px; color: var(--cp-accent, #6d5dfc);">✨</div>
			<h2 style="margin: 0 0 10px 0; font-size: 1.5rem; font-weight: 700; color: #fff;"><?php esc_html_e( 'Unlock Premium Access', 'cp-theme' ); ?></h2>
			<p style="margin: 0 0 20px 0; font-size: 0.95rem; color: var(--cp-muted, #a0aec0); line-height: 1.5;">
				<?php esc_html_e( 'This content is restricted to members with an active subscription plan. Join today to unlock this and other premium pages, courses, videos, and community features.', 'cp-theme' ); ?>
			</p>
			<div style="display: flex; flex-direction: column; gap: 10px; align-items: center;">
				<a class="cp-button cp-premium-upgrade-btn" href="<?php echo esc_url( cp_theme_get_upgrade_url() ); ?>" style="width: 100%; max-width: 280px; padding: 12px 24px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; background: linear-gradient(135deg, var(--cp-accent, #6d5dfc) 0%, #a25dfc 100%); border: none; box-shadow: 0 4px 15px rgba(109, 93, 252, 0.35); transition: transform 0.2s, box-shadow 0.2s; color: #fff; text-decoration: none; border-radius: 8px;">
					<?php esc_html_e( 'Choose a Plan', 'cp-theme' ); ?>
				</a>
				<?php if ( ! is_user_logged_in() ) : ?>
					<span style="font-size: 0.85rem; color: var(--cp-muted, #a0aec0); margin-top: 5px;">
						<?php esc_html_e( 'Already a member?', 'cp-theme' ); ?> 
						<a href="<?php echo esc_url( add_query_arg( 'cpwp_auth', 'login', home_url( '/' ) ) ); ?>" style="color: var(--cp-accent, #6d5dfc); font-weight: 600; text-decoration: none;"><?php esc_html_e( 'Log in here', 'cp-theme' ); ?></a>
					</span>
				<?php endif; ?>
			</div>
		</div>
	<?php else : ?>
		<div>
			<a class="cp-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return home', 'cp-theme' ); ?></a>
			<?php if ( ! is_user_logged_in() ) : ?>
				<a class="cp-button cp-button-secondary" href="<?php echo esc_url( add_query_arg( 'cpwp_auth', 'login', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log in', 'cp-theme' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</section></div>
<?php get_footer(); ?>
