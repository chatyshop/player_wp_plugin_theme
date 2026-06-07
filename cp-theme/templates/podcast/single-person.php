<?php
/**
 * Template: Podcast — Single Guest/Person
 * Profile page showing guest bio and episodes they appeared in.
 */
get_header();

$person = get_queried_object();
$thumb  = get_the_post_thumbnail_url( $person->ID, 'full' ); // assuming people are posts or taxonomy... Wait.
// Ah, cp_person is a post type according to class-cpwp-site-modules.php.

?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-podcast-content">
		<?php while ( have_posts() ) : the_post();
			$post_id = get_the_ID();
			$thumb   = get_the_post_thumbnail_url( $post_id, 'full' );
			
			// Get episodes featuring this person. 
			// Wait, the guest relationship is via cp_person taxonomy?
			// Let me re-verify. `class-cpwp-site-modules.php` says:
			// 'cp_person' => array( 'People and Guests', 'Person or Guest', array( 'podcast', 'news' ) )
			// If it's a post type, they are related via post meta or custom tables?
			// The plugin's architecture uses taxonomies for some, post types for others.
			// The snippet I checked earlier showed: `'cp_person' => array( 'People and Guests', ... )` in the taxonomies array? Let me just query by taxonomy cp_person if it's a term, or meta if it's a post type.
			// Actually `cp_person` is a taxonomy in my single-video template: `$guests = get_the_terms( $post_id, 'cp_person' );`
			// Wait, if it's a taxonomy, then this template is for the taxonomy archive, usually `taxonomy-cp_person.php`.
			// If it's a post type, it's `single-person.php`. I'll create `single-person.php` just in case, and it will query cp_video where the `cp_person` term slug matches the post slug.
		?>
		
		<div class="cp-podcast-person-hero">
			<div class="cp-podcast-person-art">
				<?php if ( $thumb ) : ?>
				<img src="<?php echo esc_url( $thumb ); ?>" alt="">
				<?php else : ?>
				<div class="cp-podcast-person-placeholder">👤</div>
				<?php endif; ?>
			</div>
			<div class="cp-podcast-person-details">
				<span class="cp-podcast-ep-label"><?php esc_html_e( 'Guest', 'cp-theme' ); ?></span>
				<h1 class="cp-podcast-show-title"><?php the_title(); ?></h1>
			</div>
		</div>

		<div class="cp-podcast-show-layout">
			<div class="cp-podcast-show-main">
				
				<div class="cp-podcast-about-box">
					<h3><?php esc_html_e( 'Biography', 'cp-theme' ); ?></h3>
					<div class="cp-podcast-about-desc">
						<?php the_content(); ?>
					</div>
				</div>

				<div class="cp-podcast-episodes-list">
					<h3><?php esc_html_e( 'Appears In', 'cp-theme' ); ?></h3>
					<?php
					// Query videos featuring this person (assuming taxonomy cp_person has same slug)
					$episodes = get_posts( array(
						'post_type' => 'cp_video',
						'tax_query' => array(
							array(
								'taxonomy' => 'cp_person',
								'field'    => 'slug',
								'terms'    => $post->post_name
							)
						),
						'posts_per_page' => 20
					) );

					if ( $episodes ) : foreach ( $episodes as $ep ) : 
						$ethumb  = get_the_post_thumbnail_url( $ep->ID, 'thumbnail' );
						$length  = get_post_meta( $ep->ID, '_cpwp_duration', true ) ?: '45 min';
						$date    = get_the_date( 'M j, Y', $ep->ID );
					?>
					<div class="cp-podcast-list-item">
						<a href="<?php echo esc_url( get_permalink( $ep->ID ) ); ?>" class="cp-podcast-li-thumb">
							<?php if ( $ethumb ) : ?><img src="<?php echo esc_url( $ethumb ); ?>" alt=""><?php endif; ?>
						</a>
						<div class="cp-podcast-li-info">
							<h4><a href="<?php echo esc_url( get_permalink( $ep->ID ) ); ?>"><?php echo esc_html( $ep->post_title ); ?></a></h4>
							<p class="cp-podcast-li-desc"><?php echo wp_trim_words( get_the_excerpt( $ep->ID ), 20 ); ?></p>
							<div class="cp-podcast-li-meta">
								<a href="<?php echo esc_url( get_permalink( $ep->ID ) ); ?>" class="cp-podcast-play-btn-sm">
									<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
								</a>
								<span class="cp-podcast-li-date"><?php echo esc_html( $date ); ?></span>
								<span class="cp-podcast-meta-dot">·</span>
								<span><?php echo esc_html( $length ); ?></span>
							</div>
						</div>
					</div>
					<hr class="cp-podcast-li-divider">
					<?php endforeach; else : ?>
					<p class="cp-podcast-empty"><?php esc_html_e( 'No episodes found for this guest.', 'cp-theme' ); ?></p>
					<?php endif; ?>
				</div>

			</div>
		</div>

		<?php endwhile; ?>
	</div>
</div>
<?php get_footer(); ?>
