<?php
/**
 * Template: News — Homepage (BBC/CNN style)
 * Breaking news hero, top stories grid, and category/topic rows.
 */
get_header();

// Fetch latest news/videos
$latest = get_posts( array(
	'post_type'      => array( 'cp_news', 'cp_video' ),
	'posts_per_page' => 13,
	'post_status'    => 'publish',
) );

$hero_post = ! empty( $latest ) ? array_shift( $latest ) : null;
$top_stories = array_slice( $latest, 0, 4 );
$more_news = array_slice( $latest, 4 );

// Fetch top topics
$topics = get_terms( array(
	'taxonomy'   => 'cp_topic',
	'hide_empty' => true,
	'number'     => 5,
	'orderby'    => 'count',
	'order'      => 'DESC',
) );
?>
<div class="cp-shell <?php echo is_user_logged_in() ? 'cp-page-layout-with-sidebar' : ''; ?>">
	<?php if ( is_user_logged_in() ) get_template_part( 'sidebar', 'logged-in' ); ?>

	<div class="cp-page-content cp-news-content">
		
		<div class="cp-news-header-strip">
			<span class="cp-news-breaking-label"><?php esc_html_e( 'Breaking', 'cp-theme' ); ?></span>
			<marquee class="cp-news-ticker" truespeed scrollamount="3">
				<?php foreach ( $top_stories as $st ) : ?>
					<a href="<?php echo esc_url( get_permalink( $st->ID ) ); ?>"><?php echo esc_html( $st->post_title ); ?></a>
				<?php endforeach; ?>
			</marquee>
		</div>

		<div class="cp-news-top-section">
			<!-- Hero News -->
			<?php if ( $hero_post ) : 
				$hthumb = get_the_post_thumbnail_url( $hero_post->ID, 'full' );
				$htopics = get_the_terms( $hero_post->ID, 'cp_topic' );
			?>
			<article class="cp-news-hero-card">
				<a href="<?php echo esc_url( get_permalink( $hero_post->ID ) ); ?>" class="cp-news-hero-thumb">
					<?php if ( $hthumb ) : ?>
					<img src="<?php echo esc_url( $hthumb ); ?>" alt="">
					<?php else : ?>
					<div class="cp-news-placeholder">📰</div>
					<?php endif; ?>
					<?php if ( 'cp_video' === $hero_post->post_type ) : ?>
					<div class="cp-news-video-icon">▶</div>
					<?php endif; ?>
				</a>
				<div class="cp-news-hero-info">
					<?php if ( ! empty( $htopics ) ) : ?>
					<a href="<?php echo esc_url( get_term_link( $htopics[0] ) ); ?>" class="cp-news-category-label"><?php echo esc_html( $htopics[0]->name ); ?></a>
					<?php endif; ?>
					<h1 class="cp-news-hero-title">
						<a href="<?php echo esc_url( get_permalink( $hero_post->ID ) ); ?>"><?php echo esc_html( $hero_post->post_title ); ?></a>
					</h1>
					<p class="cp-news-hero-excerpt"><?php echo wp_trim_words( get_the_excerpt( $hero_post->ID ), 25 ); ?></p>
					<div class="cp-news-meta">
						<span class="cp-news-time"><?php echo human_time_diff( get_post_time('U', false, $hero_post->ID), current_time('timestamp') ) . ' ago'; ?></span>
					</div>
				</div>
			</article>
			<?php endif; ?>

			<!-- Sidebar Stories -->
			<div class="cp-news-top-sidebar">
				<h2 class="cp-news-sidebar-title"><?php esc_html_e( 'Top Stories', 'cp-theme' ); ?></h2>
				<div class="cp-news-story-list">
					<?php foreach ( $top_stories as $ts ) : 
						$ttopics = get_the_terms( $ts->ID, 'cp_topic' );
					?>
					<article class="cp-news-list-item">
						<h3 class="cp-news-list-title">
							<a href="<?php echo esc_url( get_permalink( $ts->ID ) ); ?>"><?php echo esc_html( $ts->post_title ); ?></a>
						</h3>
						<div class="cp-news-meta">
							<?php if ( ! empty( $ttopics ) ) : ?>
							<span class="cp-news-cat-text"><?php echo esc_html( $ttopics[0]->name ); ?></span> · 
							<?php endif; ?>
							<span class="cp-news-time"><?php echo human_time_diff( get_post_time('U', false, $ts->ID), current_time('timestamp') ) . ' ago'; ?></span>
							<?php if ( 'cp_video' === $ts->post_type ) : ?>
							<span class="cp-news-icon-small">▶</span>
							<?php endif; ?>
						</div>
					</article>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_hero' ); ?>

		<!-- More News Grid -->
		<?php if ( $more_news ) : ?>
		<section class="cp-news-section">
			<h2 class="cp-news-section-title"><span><?php esc_html_e( 'More News', 'cp-theme' ); ?></span></h2>
			<div class="cp-news-grid">
				<?php foreach ( $more_news as $mn ) :
					$mthumb  = get_the_post_thumbnail_url( $mn->ID, 'medium_large' );
					$mtopics = get_the_terms( $mn->ID, 'cp_topic' );
				?>
				<article class="cp-news-card">
					<a href="<?php echo esc_url( get_permalink( $mn->ID ) ); ?>" class="cp-news-card-thumb">
						<?php if ( $mthumb ) : ?>
						<img src="<?php echo esc_url( $mthumb ); ?>" alt="" loading="lazy">
						<?php else : ?>
						<div class="cp-news-placeholder-sm">📰</div>
						<?php endif; ?>
						<?php if ( 'cp_video' === $mn->post_type ) : ?>
						<div class="cp-news-video-icon-sm">▶</div>
						<?php endif; ?>
					</a>
					<div class="cp-news-card-body">
						<h3 class="cp-news-card-title"><a href="<?php echo esc_url( get_permalink( $mn->ID ) ); ?>"><?php echo esc_html( $mn->post_title ); ?></a></h3>
						<p class="cp-news-card-excerpt"><?php echo wp_trim_words( get_the_excerpt( $mn->ID ), 15 ); ?></p>
						<div class="cp-news-meta">
							<span class="cp-news-time"><?php echo human_time_diff( get_post_time('U', false, $mn->ID), current_time('timestamp') ) . ' ago'; ?></span>
							<?php if ( ! empty( $mtopics ) ) : ?>
							· <a href="<?php echo esc_url( get_term_link( $mtopics[0] ) ); ?>" class="cp-news-cat-link"><?php echo esc_html( $mtopics[0]->name ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<!-- Topics Rows -->
		<?php if ( ! empty( $topics ) && ! is_wp_error( $topics ) ) : 
			foreach ( $topics as $topic ) :
				$topic_posts = get_posts( array(
					'post_type'      => array( 'cp_news', 'cp_video' ),
					'tax_query'      => array( array( 'taxonomy' => 'cp_topic', 'field' => 'term_id', 'terms' => $topic->term_id ) ),
					'posts_per_page' => 4
				) );
				if ( ! $topic_posts ) continue;
		?>
		<section class="cp-news-section">
			<h2 class="cp-news-section-title">
				<span><a href="<?php echo esc_url( get_term_link( $topic ) ); ?>"><?php echo esc_html( $topic->name ); ?></a></span>
			</h2>
			<div class="cp-news-grid">
				<?php foreach ( $topic_posts as $tp ) :
					$tthumb = get_the_post_thumbnail_url( $tp->ID, 'medium' );
				?>
				<article class="cp-news-card cp-news-card--compact">
					<a href="<?php echo esc_url( get_permalink( $tp->ID ) ); ?>" class="cp-news-card-thumb">
						<?php if ( $tthumb ) : ?><img src="<?php echo esc_url( $tthumb ); ?>" alt="" loading="lazy"><?php endif; ?>
					</a>
					<div class="cp-news-card-body">
						<h3 class="cp-news-card-title"><a href="<?php echo esc_url( get_permalink( $tp->ID ) ); ?>"><?php echo esc_html( $tp->post_title ); ?></a></h3>
						<div class="cp-news-meta"><span class="cp-news-time"><?php echo human_time_diff( get_post_time('U', false, $tp->ID), current_time('timestamp') ) . ' ago'; ?></span></div>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endforeach; endif; ?>

		<?php if ( class_exists( 'CPWP_Monetization' ) ) echo CPWP_Monetization::render( 'home_grid' ); ?>

	</div>
</div>
<?php get_footer(); ?>
