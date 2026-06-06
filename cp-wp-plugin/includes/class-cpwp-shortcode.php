<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CPWP_Shortcode {
	public static function render( $attributes ) {
		$attributes = shortcode_atts( array( 'video' => get_the_ID() ), $attributes, 'cp_player' );
		return CPWP_Player_Renderer::render( absint( $attributes['video'] ) );
	}
}
