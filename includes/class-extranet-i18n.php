<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Extranet
 * @subpackage Extranet/includes
 * @author     Ionut Lupu <contact@ionutlupu.me>
 */
class Extranet_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'extranet',
			false,
			ABSPATH . 'wp-content/plugins/extranet/languages/'
		);
	}
}
