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
	 * Returns plugin reviews
	 * @param int $plugin Plugin id
	 * @return array|mixed|null|object|object[]|string
	 */
	static function get_reviews( $plugin ) {

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
		$result = $api->Api( "/plugins/$plugin/reviews.json?is_featured=true" );

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
		ob_start();

		$reviews = get_transient( "fsrevs_reviews_$params[plugin]" );

		var_dump( $reviews );


		return ob_get_clean();
	}

}

FS_Testimonials::instance();