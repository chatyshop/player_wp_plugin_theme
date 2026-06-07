<?php
/**
 * Template: Video Library — Homepage
 * Vimeo-style homepage focusing on high-quality curation, staff picks, and sleek presentation.
 */
get_header();
?>
<main class="cvl-main">
	
	<!-- Hero Section -->
	<section class="cvl-hero">
		<div class="cvl-hero__bg">
			<!-- In a real site, this would be a featured video or a dynamic background -->
			<div class="cvl-hero__bg-overlay"></div>
		</div>
		<div class="cvl-hero__content cvl-container">
			<h1 class="cvl-hero__title">Discover the world's best videos.</h1>
			<p class="cvl-hero__subtitle">Join a community of passionate creators. High-quality, ad-free streaming.</p>
			<div class="cvl-hero__actions">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_video' ) ); ?>" class="cvl-btn cvl-btn--primary">Explore videos</a>
				<?php if ( ! is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wp_login_url() ); ?>" class="cvl-btn cvl-btn--outline">Join now</a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- Staff Picks / Featured -->
	<section class="cvl-section">
		<div class="cvl-container">
			<div class="cvl-section__header">
				<h2 class="cvl-section__title">Staff Picks</h2>
				<p class="cvl-section__desc">The best videos of the week, hand-picked by our curation team.</p>
			</div>
			
			<div class="cvl-grid">
				<?php
				$staff_picks = get_posts( array(
					'post_type'      => 'cp_video',
					'posts_per_page' => 4,
					'meta_query'     => array(
						array(
							'key'     => '_cpwp_badge',
							'value'   => 'Staff Pick',
							'compare' => 'LIKE'
						)
					)
				) );
				
				// Fallback to latest if no staff picks
				if ( ! $staff_picks ) {
					$staff_picks = get_posts( array(
						'post_type'      => 'cp_video',
						'posts_per_page' => 4
					) );
				}

				foreach ( $staff_picks as $post ) : setup_postdata( $post );
					$author_id = get_post_field( 'post_author', $post->ID );
					$author_name = get_the_author_meta( 'display_name', $author_id );
					?>
					<article class="cvl-card">
						<a href="<?php the_permalink(); ?>" class="cvl-card__thumb-link">
							<div class="cvl-card__thumb">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'medium_large' ); ?>
								<?php else : ?>
									<div class="cvl-card__thumb-placeholder"></div>
								<?php endif; ?>
								<div class="cvl-card__overlay">
									<span class="cvl-play-icon">▶</span>
								</div>
							</div>
						</a>
						<div class="cvl-card__info">
							<div class="cvl-card__author-avatar">
								<?php echo get_avatar( $author_id, 32 ); ?>
							</div>
							<div class="cvl-card__text">
								<h3 class="cvl-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p class="cvl-card__author">by <a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( $author_name ); ?></a></p>
							</div>
						</div>
					</article>
				<?php endforeach; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>

	<!-- Categories / Collections -->
	<section class="cvl-section cvl-section--alt">
		<div class="cvl-container">
			<div class="cvl-section__header">
				<h2 class="cvl-section__title">Curated Collections</h2>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'cp_collection' ) ); ?>" class="cvl-section__link">View all collections</a>
			</div>
			
			<div class="cvl-collections-grid">
				<?php
				$collections = get_posts( array(
					'post_type'      => 'cp_collection',
					'posts_per_page' => 3
				) );

				if ( $collections ) :
					foreach ( $collections as $post ) : setup_postdata( $post );
					?>
					<a href="<?php the_permalink(); ?>" class="cvl-collection-card">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium_large', array( 'class' => 'cvl-collection-card__bg' ) ); ?>
						<?php else : ?>
							<div class="cvl-collection-card__bg cvl-collection-card__bg--placeholder"></div>
						<?php endif; ?>
						<div class="cvl-collection-card__content">
							<h3 class="cvl-collection-card__title"><?php the_title(); ?></h3>
							<span class="cvl-btn cvl-btn--sm cvl-btn--glass">View collection</span>
						</div>
					</a>
					<?php endforeach; wp_reset_postdata();
				else : ?>
					<p class="cvl-empty">No collections available yet.</p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- Groups / Teams -->
	<section class="cvl-section">
		<div class="cvl-container">
			<div class="cvl-section__header">
				<h2 class="cvl-section__title">Teams and Agencies</h2>
			</div>
			<div class="cvl-groups-grid">
				<?php
				$groups = get_posts( array(
					'post_type'      => 'cp_group',
					'posts_per_page' => 4
				) );

				if ( $groups ) :
					foreach ( $groups as $post ) : setup_postdata( $post );
					?>
					<a href="<?php the_permalink(); ?>" class="cvl-group-card">
						<div class="cvl-group-card__avatar">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'thumbnail' ); ?>
							<?php else : ?>
								<div class="cvl-group-card__avatar-placeholder">
									<?php echo esc_html( substr( get_the_title(), 0, 1 ) ); ?>
								</div>
							<?php endif; ?>
						</div>
						<h3 class="cvl-group-card__title"><?php the_title(); ?></h3>
					</a>
					<?php endforeach; wp_reset_postdata();
				else : ?>
					<p class="cvl-empty">No teams found.</p>
				<?php endif; ?>
			</div>
		</div>
	</section>

</main>
<?php get_footer(); ?>
