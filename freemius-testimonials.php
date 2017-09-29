<?php
/*
Plugin Name: Freemius Testimonials
Description: Shows plugins/theme testimonials from Freemius
Version: 1.0
Author: Shramee Srivastav
Author URI: http://shramee.com
Author Email: shramee.srivastav@gmail.com
Domain: fs-testimonial
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
			include 'freemius/Freemius.php';
		}

		$settings = get_option( 'fstm_credentials', [] );

		if ( ! $settings ) {
			return (object) [
				'error' => [ 'message' => 'API credentials not set.', ]
			];
		}

		// Init SDK.
		$api = new Freemius_Api(
			'developer', //scope
			$settings['dev_id'],
			$settings['dev_public'],
			$settings['dev_secret']
		);

		// Get all products.
		$result = $api->Api( "/plugins/$plugin/testimonials.json?is_featured=true" );

		return $result;
	}

	/**
	 * FS_Testimonials constructor.
	 */
	public function __construct() {
		add_shortcode( 'freemius-testimonials', [ $this, 'testimonials' ] );

		add_action( 'admin_init', [ $this, 'admin' ] );
	}

	public function admin() {

		register_setting( 'general', 'fstm_credentials' );

		add_settings_section(
			'fstm_general_section',
			__( 'Freemius testimonials', 'fs-testimonial' ),
			[ $this, 'section_render' ],
			'general'
		);

	}

	public function section_render() {
		$settings = get_option( 'fstm_credentials', [] );

		$settings = wp_parse_args( $settings, [
			'dev_id'     => '',
			'dev_public' => '',
			'dev_secret' => '',
		] );
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Developer ID', 'fs-testimonial' ) ?></th>
				<td>
					<input class="regular-text" type="text" name='fstm_credentials[dev_id]' value="<?php echo $settings['dev_id'] ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Developer Public Key', 'fs-testimonial' ) ?></th>
				<td>
					<input class="regular-text" type="text" name='fstm_credentials[dev_public]' value="<?php echo $settings['dev_public'] ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Developer Secret Key', 'fs-testimonial' ) ?></th>
				<td>
					<input class="regular-text" type="text" name='fstm_credentials[dev_secret]' value="<?php echo $settings['dev_secret'] ?>">
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders testimonials
	 * @param array $params
	 * @return string
	 */
	function testimonials( $params = [] ) {

		$params = $params ? $params : [];

		$compress = '';

		if ( empty( $params['plugin'] ) ) {
			return "<p><b>Need plugin id.</b></p>";
		}

		if ( isset( $params['compress'] ) || in_array( 'compress', $params ) ) {
			$compress = 'compress';
		}

		$testimonials = get_transient( "fsrevs_testimonials_$params[plugin]" );
		if ( empty( $testimonials ) ) {
			$testimonials = self::get_testimonials( $params['plugin'] );
			if ( $testimonials ) {
				set_transient( "fsrevs_testimonials_$params[plugin]", $testimonials, DAY_IN_SECONDS * 7 );
			}
		}

		ob_start();

		if ( ! empty( $testimonials->error ) ) {

			echo "<p><b>Could not get the testimonials.</b></p>";

			if ( WP_DEBUG ) {
				echo "<p><b>{$testimonials->error->message}</b></p>";
			}

		} else {

			if ( $testimonials && $testimonials->testimonials ) {
				$this->render_testimonials( $testimonials->testimonials, $compress );
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
		<div class="testimonial" data-index="6" data-id="145" aria-hidden="true">
			<div class="quote-container">
				<ul class="rate">
					<?php
					for ( $i = 1; $i < $r->rate + 1; $i += 20 ) {
						echo '<li><i class="fa fa-star"></i></li>';
					}
					?>
				</ul>
				<h4 title="Just perfect!"><?php echo $r->title ?></h4>
				<blockquote><p><?php echo $r->text ?></p></blockquote>
				<img class="profile-pic" src="<?php echo $r->picture ?>">
			</div>
			<strong class="name"><?php echo $r->name ?></strong>
		</div>
		<?php
	}
}

FS_Testimonials::instance();