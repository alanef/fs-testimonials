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

	private static $_instance;

	public static function instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
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
		echo 'Shortcode works!';
	}

}

FS_Testimonials::instance();