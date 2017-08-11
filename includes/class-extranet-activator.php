<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Extranet
 * @subpackage Extranet/includes
 * @author     Ionut Lupu <contact@ionutlupu.me>
 */
class Extranet_Activator {

	/**
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

	    $post = array(
	          'comment_status' => 'closed',
	          'ping_status' =>  'closed' ,
	          'post_date' => date('Y-m-d H:i:s'),
	          'post_name' => 'extranet',
	          'post_status' => 'publish' ,
	          'post_title' => 'Extranet',
	          'post_type' => 'page',
	    );
	    $newvalue = wp_insert_post( $post, false );
	    update_option('_extranet_page', $newvalue);

	    if(!is_dir(ABSPATH . 'wp-content/uploads'))
	    {
	    	mkdir(ABSPATH . 'wp-content/uploads');
	    }
	    
	    // create default storage folder
	    $path = ABSPATH . 'wp-content/uploads/extranet-plugin';

	    if(!file_exists($path)) 
	    {
	    	mkdir($path);
	    	touch($path . '/index.html');

	    	if (!file_exists($path . '/' . sha1(NONCE_KEY)))
	    	{
	    		mkdir($path . '/' . sha1(NONCE_KEY));
	    	}
	    }
	}
}
