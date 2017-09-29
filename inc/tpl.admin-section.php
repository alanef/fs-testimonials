<?php
/**
 * Admin settings section
 */

$settings = get_option( 'fstm_credentials', [] );

$settings = wp_parse_args( $settings, [
'dev_id'     => '',
'dev_public' => '',
'dev_secret' => '',
] );
?>
<div id="freemius-testimonials"></div>

<table class="form-table">
	<tr><th colspan="2" style="text-align:left;">
			<h3><?php _e( 'Freemius testimonials', 'fs-testimonial' ) ?> &ndash; <?php _e( 'Shortcodes', 'fs-testimonial' ) ?></h3>
			<p><?php _e( "Don't forget to replace <code>999</code> with your plugin ID in shortcodes below.", 'fs-testimonial' ) ?></p>
	</th></tr>
	<tr>
		<th scope="row">
			<?php _e( 'To show all testimonials', 'fs-testimonial' ) ?>
		</th>
		<td>
			<code>[freemius-testimonials plugin=999]</code>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php _e( 'To show expandable drawer', 'fs-testimonial' ) ?>
		</th>
		<td>
			<code>[freemius-testimonials compress plugin=999]</code>
		</td>
	</tr>

	<tr><th colspan="2" style="text-align:left;">
			<h3><?php _e( 'Freemius testimonials', 'fs-testimonial' ) ?> &ndash; <?php _e( 'API credentials', 'fs-testimonial' ) ?></h3>
			<p><?php printf( __( "Get your developer credentials from %s Freemius Dashboard > My Profile %s", 'fs-testimonial' ), "<a href='https://dashboard.freemius.com/#/profile/'>", '</a>' ) ?></p>
			<?php
			$fs_testimonial_clear_cache_url = admin_url( 'options-general.php?fs-testimonial-clear-cache=' . wp_create_nonce( 'fs-testimonial-clear-cache' ) );

			if ( ! empty( $_GET['fs-testimonial-clear-cache'] ) && wp_verify_nonce( $_GET['fs-testimonial-clear-cache'], 'fs-testimonial-clear-cache' ) ) {
				global $wpdb;
				$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_fs_testimonials_%')" );
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php _e( 'Freemius Testimonials cache cleared successfully.' ); ?></p>
				</div>
				<?php
			} else {
				?>
				<a href="<?php echo $fs_testimonial_clear_cache_url ?>"
					 class="button"><?php _e( 'Clear testimonials cache', 'fs-testimonial' ) ?></a>
				<?php
			}
			?>
		</th></tr>

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