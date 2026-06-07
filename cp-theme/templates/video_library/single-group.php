<?php
/**
 * Template: Video Library — Single Group
 * Displays a team or agency profile.
 */
get_header();

$group = get_queried_object();
$bg_url = get_the_post_thumbnail_url( $group->ID, 'full' );
?>
<main class="cvl-main">
	
	<!-- Group Hero -->
	<section class="cvl-group-hero">
		<div class="cvl-group-hero__bg" <?php if ( $bg_url ) echo 'style="background-image:url(' . esc_url( $bg_url ) . ')"'; ?>>
			<div class="cvl-group-hero__bg-overlay"></div>
		</div>
		<div class="cvl-group-hero__content cvl-container">
			<div class="cvl-group-hero__avatar">
				<?php if ( $bg_url ) : ?>
					<?php echo get_the_post_thumbnail( $group->ID, 'thumbnail' ); ?>
				<?php else : ?>
					<span><?php echo esc_html( substr( get_the_title(), 0, 1 ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="cvl-group-hero__text">
				<h1 class="cvl-group-hero__title"><?php the_title(); ?></h1>
				<p class="cvl-group-hero__desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
			</div>
		</div>
	</section>

	<!-- Group Content -->
	<section class="cvl-section">
		<div class="cvl-container">
			<div class="cvl-section__header">
				<h2 class="cvl-section__title">Team Videos</h2>
			</div>
			
			<div class="cvl-grid">
				<?php
				$team_videos = get_posts( array(
					'post_type'      => 'cp_video',
					'posts_per_page' => 12,
					'meta_query'     => array(
						array(
							'key'   => '_cpwp_parent_item',
							'value' => $group->ID,
						)
					)
				) );

				if ( $team_videos ) :
					foreach ( $team_videos as $post ) : setup_postdata( $post );
						$author_id = get_post_field( 'post_author', get_the_ID() );
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
					<?php endforeach; wp_reset_postdata();
				else : ?>
					<p class="cvl-empty">This team hasn't uploaded any videos yet.</p>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
