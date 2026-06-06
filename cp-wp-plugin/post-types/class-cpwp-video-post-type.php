<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_Video_Post_Type {
	public static function register() {
		register_post_type(
			'cp_video',
			array(
				'labels'       => array(
					'name'          => __( 'CP Videos', 'cp-wp-plugin' ),
					'singular_name' => __( 'CP Video', 'cp-wp-plugin' ),
					'add_new_item'  => __( 'Add New Video', 'cp-wp-plugin' ),
					'edit_item'     => __( 'Edit Video', 'cp-wp-plugin' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-video-alt3',
				'has_archive'   => true,
				'rewrite'       => array( 'slug' => 'videos' ),
				'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments' ),
				'taxonomies'    => array( 'category', 'post_tag' ),
			)
		);
	}
}
