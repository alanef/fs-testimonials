<?php
/*
Plugin Name: Freemius Testimonials
Description: Shows plugins/theme reviews from Freemius
Version: 1.0
Author: Shramee Srivastav
Author URI: http://shramee.com
Author Email: shramee.srivastav@gmail.com
Domain: fs-testimonial
*/

class FS_Testimonials {

	/**
	 * FS_Testimonials constructor.
	 */
	public function __construct() {
		add_shortcode( 'freemius-testimonials', [ $this, 'testimonials' ] );

	}

	/**
	 * Renders testimonials
	 * @param array $params
	 * @return string
	 */
	function testimonials( $params = [] ) {
		echo 'Shortcode works!';
	}

}

new FS_Testimonials();