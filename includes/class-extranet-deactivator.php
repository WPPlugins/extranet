<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Extranet
 * @subpackage Extranet/includes
 * @author     Ionut Lupu <contact@ionutlupu.me>
 */
class Extranet_Deactivator {

	/**
	 * @since    1.0.0
	 */
	public static function deactivate() {
		global $wpdb;

		$id = get_option('_extranet_page', 0);
		$wpdb->query( 
			$wpdb->prepare("DELETE FROM $wpdb->posts WHERE ID = %d", $id)
		);

		$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key IN ('_extranet_enabled', '_extranet_user_homepage', '_extranet_user_favorites')" );
		$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_extranet_config'" );

		delete_option('_extranet_page');
	}
}
