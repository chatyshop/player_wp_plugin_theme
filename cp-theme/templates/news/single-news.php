<?php
/**
 * Template: News — Single Article
 * Full news article page (similar layout to single-video but for text-led stories).
 */
get_header();
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-news-content">
		<?php while ( have_posts() ) : the_post();
			$post_id   = get_the_ID();
			$topics    = get_the_terms( $post_id, 'cp_topic' );
			$locations = get_the_terms( $post_id, 'cp_location' );
			$author    = get_the_author_meta( 'display_name', $post->post_author );
			$date      = get_the_date();
			$time      = get_the_time();
			$thumb     = get_the_post_thumbnail_url( $post_id, 'full' );
		?>
		
		<div class="cp-news-article-layout">
			<div class="cp-news-article-main">
				
				<header class="cp-news-article-header">
					<?php if ( ! empty( $topics ) ) : ?>
					<a href="<?php echo esc_url( get_term_link( $topics[0] ) ); ?>" class="cp-news-cat-label"><?php echo esc_html( $topics[0]->name ); ?></a>
					<?php endif; ?>
					
					<h1 class="cp-news-article-title"><?php the_title(); ?></h1>
					<p class="cp-news-article-excerpt"><?php echo wp_strip_all_tags( get_the_excerpt() ); ?></p>
					
					<div class="cp-news-article-byline">
						<span class="cp-news-author"><?php esc_html_e( 'By', 'cp-theme' ); ?> <?php echo esc_html( $author ); ?></span>
						<span class="cp-news-meta-divider">·</span>
						<span class="cp-news-datetime"><?php echo esc_html( $date . ' ' . $time ); ?></span>
						
						<?php if ( ! empty( $locations ) ) : ?>
						<span class="cp-news-meta-divider">·</span>
						<span class="cp-news-location">📍 <?php echo esc_html( $locations[0]->name ); ?></span>
						<?php endif; ?>
					</div>
				</header>

				<?php if ( $thumb ) : ?>
				<div class="cp-news-article-featured-image">
					<img src="<?php echo esc_url( $thumb ); ?>" alt="">
				</div>
				<?php endif; ?>

				<div class="cp-news-article-body">
					<?php the_content(); ?>
				</div>

				<?php if ( comments_open() || get_comments_number() ) : ?>
				<div class="cp-news-comments">
					<?php comments_template(); ?>
				</div>
				<?php endif; ?>

			</div>

			<!-- Related Stories Sidebar -->
			<aside class="cp-news-article-aside">
				<h3 class="cp-news-aside-heading"><?php esc_html_e( 'Related Stories', 'cp-theme' ); ?></h3>
				<div class="cp-news-related-list">
					<?php
					$related = get_posts( array(
						'post_type'      => array( 'cp_news', 'cp_video' ),
						'posts_per_page' => 5,
						'post__not_in'   => array( $post_id ),
						'tax_query'      => ! empty( $topics ) ? array( array( 'taxonomy' => 'cp_topic', 'field' => 'term_id', 'terms' => $topics[0]->term_id ) ) : array(),
					) );
					foreach ( $related as $rel ) :
						$rthumb = get_the_post_thumbnail_url( $rel->ID, 'medium' );
					?>
					<article class="cp-news-related-item">
						<a href="<?php echo esc_url( get_permalink( $rel->ID ) ); ?>" class="cp-news-related-thumb">
							<?php if ( $rthumb ) : ?><img src="<?php echo esc_url( $rthumb ); ?>" alt=""><?php endif; ?>
						</a>
						<div class="cp-news-related-info">
							<h4><a href="<?php echo esc_url( get_permalink( $rel->ID ) ); ?>"><?php echo esc_html( $rel->post_title ); ?></a></h4>
							<span class="cp-news-time"><?php echo human_time_diff( get_post_time('U', false, $rel->ID), current_time('timestamp') ) . ' ago'; ?></span>
						</div>
					</article>
					<?php endforeach; ?>
				</div>
			</aside>
		</div>

		<?php endwhile; ?>
	</div>
</div>
<?php get_footer(); ?>
