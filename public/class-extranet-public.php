<?php
use Extranet\View\ExtranetView;
use Extranet\Helper\ExtranetUser;
use Joomla\Router\Router;
use Extranet\ExtranetApp;

class Extranet_Public {

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


	public function __construct( $plugin_name, $version ) 
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}


	public function enqueue_styles() 
	{
		global $post;
		$user 	= ExtranetUser::getInstance();
		$app 	= ExtranetApp::getInstance();

		if ($app->id == $post->ID)
		{
			wp_enqueue_style( 'pure-base', site_url() . '/wp-content/plugins/extranet/assets/css/base-min.css', array(), null, 'all' );
			wp_enqueue_style( 'pure-buttons', site_url() . '/wp-content/plugins/extranet/assets/css/buttons-min.css', array(), null, 'all' );
			wp_enqueue_style( 'pure-forms', site_url() . '/wp-content/plugins/extranet/assets/css/forms-min.css', array(), null, 'all' );
			wp_enqueue_style( 'pure-grids', site_url() . '/wp-content/plugins/extranet/assets/css/grids-min.css', array(), null, 'all' );
			wp_enqueue_style( 'pure-responsive', site_url() . '/wp-content/plugins/extranet/assets/css/grids-responsive-min.css', array(), null, 'all' );
			wp_enqueue_style( 'pure-menus', site_url() . '/wp-content/plugins/extranet/assets/css/menus.css', array(), null, 'all' );
			wp_enqueue_style( 'dashicons', site_url() . '/wp-includes/css/dashicons.min.css', array(), null, 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/extranet-public.css', array(), $this->version, 'all' );
		}	
	}


	public function enqueue_scripts() 
	{
		global $post;
		$user 	= ExtranetUser::getInstance();
		$app 	= ExtranetApp::getInstance();

		if ($app->id == $post->ID)
		{
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/extranet-public.js', array(), $this->version, false );
		}
	}


	public function extranet_content($c) 
	{
		global $post;
		$user 	= ExtranetUser::getInstance();
		$app 	= ExtranetApp::getInstance();
		$layout = $app->input->get('view','default','alnum');

		if ($app->id != $post->ID)
		{
			return $c;
		}

		// allow only authenticated users
		// or non authenticated users with view = default (login)
		if (($user->exists() && $user->enabled()) || (!$user->exits() && $layout == 'default'))
		{
			$view = new ExtranetView();
			$view->setLayout($layout);
			$content = $view->render();
		}

		return $content;
	}


	public function route() 
	{
		$user 	= ExtranetUser::getInstance();
		$app 	= ExtranetApp::getInstance();
		$task 	= $app->input->get('task','', 'cmd');
		$router = new Router;
			
		$router ->addMap('extranet.file.download','Extranet\Controller\ExtranetFileController')
				->addMap('extranet.file.sharedownload','Extranet\Controller\ExtranetFileController')
				->addMap('extranet.file.upload','Extranet\Controller\ExtranetFileController')
				->addMap('extranet.file.delete','Extranet\Controller\ExtranetFileController')
				->addMap('extranet.file.favorite','Extranet\Controller\ExtranetFileController')
				->addMap('extranet.file.unfavorite','Extranet\Controller\ExtranetFileController')
				->addMap('extranet.folders.newf','Extranet\Controller\ExtranetFoldersController')
				->addMap('extranet.folders.delete','Extranet\Controller\ExtranetFoldersController')
				->addMap('extranet.login','Extranet\Controller\ExtranetLoginController')
				->addMap('extranet.logout','Extranet\Controller\ExtranetLoginController')
				;

 		// if the user is authenticated OR if the user is not authenticated but task is to login
		if (($user->exists() && $user->enabled()) || (!$user->exists() && $task == 'extranet.login'))
		{	
			// if there is a task try to execute it
			if ($task && preg_match('/^extranet\..*$/', $task))
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
