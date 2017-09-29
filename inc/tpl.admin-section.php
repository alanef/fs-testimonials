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
			<?php _e( 'To show all shortcodes', 'fs-testimonial' ) ?>
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