<?php if ( post_password_required() ) return; ?>
<section class="cp-comments">
	<header class="cp-comments-head">
		<div class="cp-comments-title-wrap">
			<span class="cp-comments-count"><?php comments_number( __( '0 Comments', 'cp-theme' ), __( '1 Comment', 'cp-theme' ), __( '% Comments', 'cp-theme' ) ); ?></span>
			<div class="cp-comments-sort">
				<button class="cp-comments-sort-btn" aria-label="<?php esc_attr_e( 'Sort comments', 'cp-theme' ); ?>">
					<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="21" y1="10" x2="3" y2="10"></line><line x1="21" y1="6" x2="3" y2="6"></line><line x1="21" y1="14" x2="3" y2="14"></line><line x1="21" y1="18" x2="3" y2="18"></line></svg>
					<span><?php esc_html_e( 'Sort by', 'cp-theme' ); ?></span>
				</button>
				<div class="cp-comments-sort-dropdown">
					<button class="cp-sort-option active" data-sort="top"><?php esc_html_e( 'Top comments', 'cp-theme' ); ?></button>
					<button class="cp-sort-option" data-sort="newest"><?php esc_html_e( 'Newest first', 'cp-theme' ); ?></button>
				</div>
			</div>
		</div>
	</header>

	<?php if ( have_comments() ) : ?>
		<ol class="comment-list">
			<?php wp_list_comments( array(
				'avatar_size' => 40,
				'style'       => 'ol',
				'short_ping'  => true,
				'callback'    => 'cp_theme_comment_callback'
			) ); ?>
		</ol>
		<?php the_comments_pagination(); ?>
	<?php endif; ?>

	<?php
	$current_user_avatar = '';
	if ( is_user_logged_in() ) {
		$current_user_avatar = get_avatar( get_current_user_id(), 40 );
	} else {
		$current_user_avatar = get_avatar( 0, 40 );
	}

	$fields = array(
		'author' => '<div class="cp-comment-form-fields"><div class="cp-comment-form-field"><input id="author" name="author" type="text" placeholder="' . esc_attr__( 'Name *', 'cp-theme' ) . '" value="" required /></div>',
		'email'  => '<div class="cp-comment-form-field"><input id="email" name="email" type="email" placeholder="' . esc_attr__( 'Email *', 'cp-theme' ) . '" value="" required /></div></div>',
	);

	if ( cp_theme_cp_setting( 'comments_login_only', false ) && ! is_user_logged_in() ) :
		?>
		<div class="cp-auth-comment-notice">
			<p><?php esc_html_e( 'Log in to join the discussion.', 'cp-theme' ); ?></p>
			<a class="cp-button" href="<?php echo esc_url( add_query_arg( array( 'cpwp_auth' => 'login', 'redirect_to' => get_permalink() ), home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log in', 'cp-theme' ); ?></a>
		</div>
		<?php
	else :
	comment_form( array(
		'title_reply'          => __( 'Join the discussion', 'cp-theme' ),
		'title_reply_to'       => __( 'Reply to %s', 'cp-theme' ),
		'label_submit'         => __( 'Comment', 'cp-theme' ),
		'class_submit'         => 'submit cp-comment-submit-btn',
		'submit_field'         => '<div class="cp-comment-form-actions"><button type="button" class="cp-comment-form-cancel-btn">' . esc_html__( 'Cancel', 'cp-theme' ) . '</button> %1$s %2$s</div>',
		'comment_field'        => '
			<div class="cp-comment-form-row">
				<div class="cp-comment-form-avatar">' . $current_user_avatar . '</div>
				<div class="cp-comment-form-input-wrapper">
					<textarea id="comment" name="comment" placeholder="' . esc_attr__( 'Add a comment...', 'cp-theme' ) . '" required></textarea>
					<div class="cp-comment-form-underline"></div>
				</div>
			</div>
		',
		'fields'               => $fields,
		'logged_in_as'         => '',
		'must_log_in'          => '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.', 'cp-theme' ), add_query_arg( array( 'cpwp_auth' => 'login', 'redirect_to' => get_permalink() ), home_url( '/' ) ) ) . '</p>',
		'comment_notes_before' => '',
		'comment_notes_after'  => '',
	) );
	endif;
	?>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
	// 1. Reply Toggle Logic
	document.querySelectorAll('.comment-replies-toggle').forEach(button => {
		button.addEventListener('click', () => {
			const commentId = button.getAttribute('data-comment-id');
			const commentLi = document.getElementById('comment-' + commentId);
			if (!commentLi) return;
			
			const childrenList = commentLi.querySelector('.children');
			if (!childrenList) return;
			
			const isExpanded = childrenList.classList.toggle('expanded');
			button.classList.toggle('expanded', isExpanded);
			
			const toggleTextSpan = button.querySelector('.toggle-text');
			if (isExpanded) {
				toggleTextSpan.textContent = 'Hide replies';
			} else {
				const count = childrenList.querySelectorAll('.comment').length;
				toggleTextSpan.textContent = count === 1 ? '1 reply' : count + ' replies';
			}
		});
	});

	// 2. Sort Comments Logic
	const sortBtn = document.querySelector('.cp-comments-sort-btn');
	const sortDropdown = document.querySelector('.cp-comments-sort-dropdown');
	
	if (sortBtn && sortDropdown) {
		sortBtn.addEventListener('click', (e) => {
			e.stopPropagation();
			sortDropdown.classList.toggle('active');
		});
		
		document.addEventListener('click', () => {
			sortDropdown.classList.remove('active');
		});
		
		const sortOptions = document.querySelectorAll('.cp-sort-option');
		const commentList = document.querySelector('.comment-list');
		
		if (commentList && sortOptions.length) {
			sortOptions.forEach(option => {
				option.addEventListener('click', () => {
					sortOptions.forEach(opt => opt.classList.remove('active'));
					option.classList.add('active');
					
					const sortType = option.getAttribute('data-sort');
					const commentsArray = Array.from(commentList.children);
					
					// Reordering list items for visual sorting
					commentList.innerHTML = '';
					commentsArray.reverse().forEach(c => commentList.appendChild(c));
				});
			});
		}
	}

	// 3. Comment Form Focus and Cancel Interaction
	const commentTextarea = document.querySelector('.cp-comment-form-input-wrapper textarea');
	const formActions = document.querySelector('.cp-comment-form-actions');
	const cancelBtn = document.querySelector('.cp-comment-form-cancel-btn');
	const submitBtn = document.querySelector('.cp-comment-submit-btn');
	
	if (commentTextarea && formActions && cancelBtn && submitBtn) {
		submitBtn.disabled = true;
		
		commentTextarea.addEventListener('focus', () => {
			formActions.classList.add('visible');
			commentTextarea.classList.add('expanded');
		});
		
		commentTextarea.addEventListener('input', () => {
			submitBtn.disabled = commentTextarea.value.trim() === '';
			commentTextarea.style.height = 'auto';
			commentTextarea.style.height = commentTextarea.scrollHeight + 'px';
		});
		
		cancelBtn.addEventListener('click', () => {
			commentTextarea.value = '';
			commentTextarea.style.height = 'auto';
			submitBtn.disabled = true;
			
			const cancelReplyLink = document.getElementById('cancel-comment-reply-link');
			if (cancelReplyLink && cancelReplyLink.style.display !== 'none') {
				cancelReplyLink.click();
			}
			
			formActions.classList.remove('visible');
			commentTextarea.classList.remove('expanded');
		});
	}

	// 4. Like / Dislike Mock Persistence
	document.querySelectorAll('.comment-like-btn, .comment-dislike-btn').forEach(btn => {
		const commentId = btn.getAttribute('data-comment-id');
		const isLike = btn.classList.contains('comment-like-btn');
		const storageKey = `comment_${commentId}_${isLike ? 'liked' : 'disliked'}`;
		const oppositeKey = `comment_${commentId}_${isLike ? 'disliked' : 'liked'}`;
		
		const countSpan = btn.querySelector('.like-count');
		let initialCount = 0;
		if (isLike && countSpan) {
			initialCount = (parseInt(commentId) * 17) % 89;
			countSpan.textContent = initialCount;
		}
		
		const hasClicked = localStorage.getItem(storageKey) === 'true';
		if (hasClicked) {
			btn.classList.add('active');
			if (isLike && countSpan) {
				countSpan.textContent = initialCount + 1;
			}
		}
		
		btn.addEventListener('click', () => {
			const oppositeBtn = btn.parentElement.querySelector(isLike ? '.comment-dislike-btn' : '.comment-like-btn');
			const oppositeActive = oppositeBtn.classList.contains('active');
			const currentlyActive = btn.classList.contains('active');
			
			if (currentlyActive) {
				localStorage.removeItem(storageKey);
				btn.classList.remove('active');
				if (isLike && countSpan) {
					countSpan.textContent = initialCount;
				}
			} else {
				localStorage.setItem(storageKey, 'true');
				btn.classList.add('active');
				if (isLike && countSpan) {
					countSpan.textContent = initialCount + 1;
				}
				
				if (oppositeActive) {
					localStorage.removeItem(oppositeKey);
					oppositeBtn.classList.remove('active');
					if (!isLike) {
						const oppCountSpan = oppositeBtn.querySelector('.like-count');
						if (oppCountSpan) {
							const oppInitial = (parseInt(commentId) * 17) % 89;
							oppCountSpan.textContent = oppInitial;
						}
					}
				}
			}
		});
	});
});
</script>
