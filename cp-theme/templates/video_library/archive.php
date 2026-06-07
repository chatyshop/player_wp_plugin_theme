<?php
/**
 * Template: Video Library — Archive
 * Clean grid of videos.
 */
get_header();

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$is_search = is_search();
?>
<main class="cvl-main">
	<div class="cvl-container cvl-archive-header">
		<?php if ( $is_search ) : ?>
			<h1 class="cvl-archive-title">Search results for: "<?php echo esc_html( get_search_query() ); ?>"</h1>
		<?php else : ?>
			<h1 class="cvl-archive-title">
				<?php 
				if ( is_post_type_archive( 'cp_video' ) ) echo 'All Videos';
				elseif ( is_post_type_archive( 'cp_collection' ) ) echo 'Collections';
				elseif ( is_post_type_archive( 'cp_group' ) ) echo 'Teams and Groups';
				else echo post_type_archive_title( '', false ); 
				?>
			</h1>
		<?php endif; ?>
	</div>

	<div class="cvl-container cvl-archive-body">
		<?php if ( have_posts() ) : ?>
			<?php if ( is_post_type_archive( 'cp_collection' ) ) : ?>
				<div class="cvl-collections-grid">
					<?php while ( have_posts() ) : the_post(); ?>
						<a href="<?php the_permalink(); ?>" class="cvl-collection-card">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium_large', array( 'class' => 'cvl-collection-card__bg' ) ); ?>
							<?php else : ?>
								<div class="cvl-collection-card__bg cvl-collection-card__bg--placeholder"></div>
							<?php endif; ?>
							<div class="cvl-collection-card__content">
								<h3 class="cvl-collection-card__title"><?php the_title(); ?></h3>
							</div>
						</a>
					<?php endwhile; ?>
				</div>
			<?php else : ?>
				<div class="cvl-grid">
					<?php while ( have_posts() ) : the_post(); 
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
					<?php endwhile; ?>
				</div>
			<?php endif; ?>

			<div class="cp-pagination">
				<?php 
				echo paginate_links( array(
					'prev_text' => '← Previous',
					'next_text' => 'Next →',
				) ); 
				?>
			</div>
		<?php else : ?>
			<div class="cvl-empty">
				<p>No content found.</p>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
