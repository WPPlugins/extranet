<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Extranet
 * @subpackage Extranet/admin
 * @author     Ionut Lupu <contact@ionutlupu.me>
 */

use Joomla\Router\Router;
use Joomla\Input\Input;

class Extranet_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) 
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}


	public function enqueue_styles() 
	{
		$current = get_current_screen();

		if (stripos($current->base, 'extranet/admin') !== false)
		{
			wp_enqueue_style( 'pure-base', site_url() . '/wp-content/plugins/extranet/assets/css/base-min.css', array(), null, 'all' );
			wp_enqueue_style( 'pure-buttons', site_url() . '/wp-content/plugins/extranet/assets/css/buttons-min.css', array(), null, 'all' );
			wp_enqueue_style( 'pure-forms', site_url() . '/wp-content/plugins/extranet/assets/css/forms-min.css', array(), null, 'all' );
			wp_enqueue_style( 'pure-grids', site_url() . '/wp-content/plugins/extranet/assets/css/grids-min.css', array(), null, 'all' );
			wp_enqueue_style( 'pure-responsive', site_url() . '/wp-content/plugins/extranet/assets/css/grids-responsive-min.css', array(), null, 'all' );
			wp_enqueue_style( 'pure-menus', site_url() . '/wp-content/plugins/extranet/assets/css/menus.css', array(), null, 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/extranet-admin.css', array(), $this->version, 'all' );
		}
	}


	public function enqueue_scripts() 
	{
		wp_enqueue_script( 'polyfills', site_url() . '/wp-content/plugins/extranet/assets/js/polyfills.js', array(), $this->version, false );
		wp_enqueue_script( 'hejs', site_url() . '/wp-content/plugins/extranet/assets/js/he.js', array(), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/extranet-admin.js', array(), $this->version, false );
	}


	public function register_menu() {
		add_menu_page(
	        __( 'Extranet', 'extranet' ),
	       	__( 'Extranet', 'extranet' ),
	        'manage_options',
	        'extranet',
	        '',
	        ''
	    );

	    add_submenu_page('extranet',  __( 'Folders', 'extranet' ),  __( 'Folders', 'extranet' ), 'manage_options', 'extranet/admin/partials/folders.php');
	    add_submenu_page('extranet',  __( 'Users', 'extranet' ),  __( 'Users', 'extranet' ), 'manage_options', 'extranet/admin/partials/users.php');
	    add_submenu_page('extranet',  __( 'Settings', 'extranet' ),  __( 'Settings', 'extranet' ), 'manage_options', 'extranet/admin/partials/settings.php');
	    remove_submenu_page ('extranet','extranet');
	}


	public function route() 
	{

		$router = new Router;
		$input  = new Input;
		$page  	= $input->get('page', '', 'string');
		$task 	= $input->get('task','', 'cmd');

		if (stripos($page, 'extranet') !== false) 
		{
			$router ->addMap('adminfolder.newf','Extranet\Controller\AdminfolderController')
					->addMap('adminfolder.rename','Extranet\Controller\AdminfolderController')
					->addMap('adminfolder.delete','Extranet\Controller\AdminfolderController')
					->addMap('adminfolder.upload','Extranet\Controller\AdminfolderController')
					->addMap('adminfolder.setPermissions','Extranet\Controller\AdminfolderController')
					->addMap('adminfile.rename','Extranet\Controller\AdminfileController')
					->addMap('adminfile.delete','Extranet\Controller\AdminfileController')
					->addMap('adminuser.save','Extranet\Controller\AdminuserController')
					->addMap('adminuser.folders','Extranet\Controller\AdminuserController')
					->addMap('adminsettings.save','Extranet\Controller\AdminsettingsController')
					;
			
			if ($task)
			{			
				try 
				{
					$controller = $router->getController($task);
					$controller->execute();
					exit();
				}
				catch (\RuntimeException $e) 
				{
					echo $e->getMessage();
				}
			}		
		}
	}
}
