<?php
/*
Plugin Name: Testimonials for Freemius
Description: Shows plugins/theme testimonials from Freemius
Version: 1.0.0
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

		if ( ! class_exists( 'Freemius_API' ) ) {
			include 'inc/freemius/Freemius.php';
		}

		$settings = get_option( 'fstm_credentials', array() );

		if ( ! $settings ) {
			return (object) array(
				'error' => array( 'message' => 'API credentials not set.', )
			);
		}

		// Init SDK.
		$api = new Freemius_Api(
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
		wp_enqueue_style( 'fmt-script', plugin_dir_url( __FILE__ ) . '/assets/front.css', '', '1.0.0' );
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
					var_dump( $GLOBALS['freemius_testimonials_order'] );
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
					foreach ( $testimonials as $r ) {
						$this->testimonial_html( $r );
					} ?>
				</div>
			</div>
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
		$r->picture = $r->picture ? $r->picture : 'http://1.gravatar.com/avatar/d28eae9f3dcdcba08ac685b112b006aa?s=128&d=mm&f=y&r=g';
		?>
		<div class="testimonial" data-id="<?php echo $r->id ?>">
			<div class="quote-container">
				<ul class="rate">
					<?php
					for ( $i = 1; $i < $r->rate + 1; $i += 20 ) {
						echo '<li><i class="fa fa-star"></i></li>';
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
	}
}

FS_Testimonials::instance();