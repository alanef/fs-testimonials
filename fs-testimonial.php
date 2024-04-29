<?php
/*
Plugin Name: Testimonials for Freemius
Description: Shows plugins/theme testimonials from Freemius
Version: 1.1.0
Plugin URI: https://pootlepress.com/freemius-testimonials
Author: Pootlepress
Author URI: https://pootlepress.com/
Domain: fs-testimonial
@developer shramee.srivastav@gmail.com
*/

class FS_Testimonials {

	/** @var FS_Testimonials Instance */
	private static $_instance;

	/**
	 * Get instance
	 * @return FS_Testimonials Instance
	 */
	public static function instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Returns plugin testimonials
	 * @param int $plugin Plugin id
	 * @return array|mixed|null|object|object[]|string
	 */
	static function get_testimonials( $plugin ) {

		if ( ! class_exists( 'Freemius_Api_WordPress' ) ) {
			include 'vendor/freemius/wordpress-sdk/includes/sdk/FreemiusWordPress.php';
		}

		$settings = get_option( 'fstm_credentials', array() );

		if ( ! $settings ) {
			return (object) array(
				'error' => array( 'message' => 'API credentials not set.', )
			);
		}

		// Init SDK.
		$api = new Freemius_Api_WordPress(
			'developer', //scope
			$settings['dev_id'],
			$settings['dev_public'],
			$settings['dev_secret']
		);

		// Get all products.
		$result = $api->Api( "/plugins/$plugin/reviews.json?is_featured=true" );

		return $result;
	}

	/**
	 * FS_Testimonials constructor.
	 */
	public function __construct() {
		add_shortcode( 'freemius-testimonials', array( $this, 'testimonials' ) );

		add_action( 'admin_init', array( $this, 'admin' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
	}

	public function admin() {

		register_setting( 'general', 'fstm_credentials' );

		add_settings_section(
			'fstm_general_section',
			'',
			array( $this, 'admin_section_render' ),
			'general'
		);

	}

	public function admin_section_render() {
		include "inc/tpl.admin-section.php";
	}

	public function scripts() {
		wp_enqueue_style( 'fmt-style', plugin_dir_url( __FILE__ ) . '/assets/front.css', '', '1.0.0' );
	}

	/**
	 * Renders testimonials
	 * @param array $params
	 * @return string
	 */
	function testimonials( $params = array() ) {

		$params = $params ? $params : array();

		$compress = '';

		if ( empty( $params['plugin'] ) ) {
			return "<p><b>Need plugin id.</b></p>";
		}

		if ( isset( $params['compress'] ) || in_array( 'compress', $params ) ) {
			$compress = 'compress';
		}

		$testimonials = get_transient( "fs_testimonials_$params[plugin]" );
		if ( empty( $testimonials ) ) {
			$testimonials = self::get_testimonials( $params['plugin'] );
			if ( $testimonials && empty( $testimonials->error ) ) {
				set_transient( "fs_testimonials_$params[plugin]", $testimonials, DAY_IN_SECONDS * 7 );
			}
		}

		ob_start();

		if ( ! empty( $testimonials->error ) ) {

			echo "<p><b>Could not get the testimonials.</b></p>";

			if ( WP_DEBUG ) {
				echo "<p><b>{$testimonials->error->message}</b></p>";
			}

		} else {

			if ( $testimonials && $testimonials->reviews ) {
				$reviews = $testimonials->reviews;
				if ( ! empty( $params['order'] ) ) {
					$GLOBALS['freemius_testimonials_order'] = array_flip( preg_split( "/[^0-9]+/", $params['order'] ) );
					usort( $reviews, function ( $a, $b ) {
						$order = $GLOBALS['freemius_testimonials_order'];
						$ordera = isset( $order[ $a->id ] ) ? $order[ $a->id ] : 9999;
						$orderb = isset( $order[ $b->id ] ) ? $order[ $b->id ] : 9999;
						return $ordera > $orderb;
					} );
				}
				$this->render_testimonials( $reviews, $compress );
			}
		}


		return ob_get_clean();
	}

	public function render_testimonials( $testimonials, $compress ) {
		?>
		<div id="fs-testimonials" class="<?php echo $compress ?>">
			<div class="fs-testimonials-outer-wrap">
				<div class="fs-testimonials-wrap">
					<?php
					$divs = [];
					$i = 0;
					foreach ( $testimonials as $r ) {
						$divs[ $i ++ % 3 ] .= $this->testimonial_html( $r );
					}

					echo "<div>$divs[0]</div>";
					echo "<div>$divs[1]</div>";
					echo "<div>$divs[2]</div>";
					?>
				</div>
			</div>
			<?php if ( isset( $_GET['fs-testimonial-ids'] ) && current_user_can( 'edit_posts' ) ) { ?>
				<style>
					#fs-testimonials div[data-id]:after {
						content: attr( data-id );
						position: absolute;
						top: 3px;
						right: 7px;
					}
				</style>
			<?php } ?>
			<?php if ( $compress ) { ?>
				<div
					onclick="jQuery(this).closest('#fs-testimonials').toggleClass('compress-expanded')" class="compress-toggle"
					data-more="<?php _e( 'More testimonials', 'fs-testimonial' ) ?>"
					data-less="<?php _e( 'Less testimonials', 'fs-testimonial' ) ?>">
				</div>
			<?php } ?>
		</div>
		<?php
	}

	function testimonial_html( $r ) {
		$r->picture = $r->picture ? $r->picture : 'https://0.gravatar.com/avatar/65e687353c07dd37523cdb8581cec4a9?s=128&d=mm&f=y&r=g';
		ob_start();
		?>
		<div class="testimonial">
			<div class="quote-container" data-id="<?php echo $r->id ?>">
				<ul class="rate">
					<?php
					for ( $i = 1; $i < $r->rate + 1; $i += 20 ) {
						echo '<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor" style="width:34px;height:34px;"><!--! Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc. --><path d="M287.9 0c9.2 0 17.6 5.2 21.6 13.5l68.6 141.3 153.2 22.6c9 1.3 16.5 7.6 19.3 16.3s.5 18.1-5.9 24.5L433.6 328.4l26.2 155.6c1.5 9-2.2 18.1-9.7 23.5s-17.3 6-25.3 1.7l-137-73.2L151 509.1c-8.1 4.3-17.9 3.7-25.3-1.7s-11.2-14.5-9.7-23.5l26.2-155.6L31.1 218.2c-6.5-6.4-8.7-15.9-5.9-24.5s10.3-14.9 19.3-16.3l153.2-22.6L266.3 13.5C270.4 5.2 278.7 0 287.9 0zm0 79L235.4 187.2c-3.5 7.1-10.2 12.1-18.1 13.3L99 217.9 184.9 303c5.5 5.5 8.1 13.3 6.8 21L171.4 443.7l105.2-56.2c7.1-3.8 15.6-3.8 22.6 0l105.2 56.2L384.2 324.1c-1.3-7.7 1.2-15.5 6.8-21l85.9-85.1L358.6 200.5c-7.8-1.2-14.6-6.1-18.1-13.3L287.9 79z"/></svg></li>';
					}
					?>
				</ul>
				<h4><?php echo $r->title ?></h4>
				<blockquote><p><?php echo $r->text ?></p></blockquote>
				<img class="profile-pic" src="<?php echo $r->picture ?>">
			</div>
			<strong class="name"><?php echo $r->name ?></strong>
		</div>
		<?php
		return ob_get_clean();
	}
}

FS_Testimonials::instance();
