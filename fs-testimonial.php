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
						echo '<li><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor" style="width:34px;height:34px;"><!--! Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc. --><path d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"/></svg></li>';
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
