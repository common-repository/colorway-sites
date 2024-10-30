<?php
/**
 * White Label Form
 *
 * @package Colorway Sites
 */

?>
<li>
	<div class="branding-form postbox">
		<button type="button" class="handlediv button-link" aria-expanded="true">
			<span class="screen-reader-text"><?php echo esc_html( $plugin_name ); ?></span>
			<span class="toggle-indicator" aria-hidden="true"></span>
		</button>

		<h2 class="hndle ui-sortable-handle">
			<span><?php echo esc_html( $plugin_name ); ?></span>
		</h2>

		<div class="inside">
			<div class="form-wrap">
				<div class="form-field">
					<label><?php _e( 'Plugin Name:', 'colorway-sites' ); ?>
						<input type="text" name="ast_white_label[colorway-sites][name]" class="placeholder placeholder-active" value="<?php echo esc_attr( $settings['colorway-sites']['name'] ); ?>">
					</label>
				</div>
				<div class="form-field">
					<label><?php _e( 'Plugin Description:', 'colorway-sites' ); ?>
						<textarea name="ast_white_label[colorway-sites][description]" class="placeholder placeholder-active" rows="2"><?php echo esc_attr( $settings['colorway-sites']['description'] ); ?></textarea>
					</label>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</li>
