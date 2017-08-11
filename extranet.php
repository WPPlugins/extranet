<?php
/**
  * Plugin Name: Extranet
  * Plugin URI: https://ionutlupu.me
  * Description: Create a secure download area/wordpress extranet/client area.
  * Version: 2.0.1
  * Author: Ionut Lupu
  * Author URI: https://ionutlupu.me
  * License: GPLv2+
  * Text Domain: extranet
  * Domain Path: /languages
*/

// If this file is called directly, abort.
if (!defined('WPINC')) { die; }

define(JPATH_ROOT, ABSPATH . 'wp-content/plugins/extranet');

require_once ABSPATH . 'wp-content/plugins/extranet/includes/vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-extranet-activator.php
 */
function activate_extranet() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-extranet-activator.php';
	Extranet_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-extranet-deactivator.php
 */
function deactivate_extranet() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-extranet-deactivator.php';
	Extranet_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_extranet' );
register_deactivation_hook( __FILE__, 'deactivate_extranet' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-extranet.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_extranet() {

	$plugin = new Extranet();
	$plugin->run();
}
run_extranet();
