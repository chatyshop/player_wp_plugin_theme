</main>
<footer class="cp-site-footer"><div class="cp-shell cp-footer-inner"><strong><?php echo esc_html( cp_theme_cp_setting( 'platform_name', get_bloginfo( 'name' ) ) ); ?></strong><span><?php echo esc_html( cp_theme_cp_setting( 'tagline', get_bloginfo( 'description' ) ) ); ?></span><span><?php echo esc_html( cp_theme_cp_setting( 'footer_text', '© ' . gmdate( 'Y' ) ) ); ?><?php if ( cp_theme_cp_setting( 'facebook_url' ) ) : ?> · <a href="<?php echo esc_url( cp_theme_cp_setting( 'facebook_url' ) ); ?>">Facebook</a><?php endif; ?><?php if ( cp_theme_cp_setting( 'x_url' ) ) : ?> · <a href="<?php echo esc_url( cp_theme_cp_setting( 'x_url' ) ); ?>">X</a><?php endif; ?></span></div></footer>
<?php if ( is_user_logged_in() ) : ?>
<script>
(function() {
	var toggleBtn = document.querySelector('.cp-sidebar-toggle');
	var sidebar   = document.querySelector('.cp-sidebar-logged-in');

	// If this page has no sidebar element, the toggle button should
	// always clear the hidden state so the next page with a sidebar will show it.
	if (toggleBtn && !sidebar) {
		// Pages with no sidebar: clicking toggle just removes the hidden flag.
		toggleBtn.addEventListener('click', function() {
			document.documentElement.classList.remove('cp-sidebar-hidden');
			localStorage.removeItem('cp_sidebar_hidden');
		});
		return;
	}

	if (toggleBtn && sidebar) {
		toggleBtn.addEventListener('click', function() {
			// toggle() returns true when class was ADDED (sidebar now hidden)
			var nowHidden = document.documentElement.classList.toggle('cp-sidebar-hidden');
			if (nowHidden) {
				localStorage.setItem('cp_sidebar_hidden', 'true');
			} else {
				localStorage.removeItem('cp_sidebar_hidden');
			}
		});
	}
})();
</script>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
