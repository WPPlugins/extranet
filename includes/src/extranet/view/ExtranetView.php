<?php
// front-end
namespace Extranet\View;

use Joomla\View\AbstractHtmlView;
use Joomla\Filesystem\Path;
use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetUser;
use Extranet\Helper\ExtranetMenu;


class ExtranetView extends AbstractHtmlView 
{
	protected $spath;
	protected $url; 
	protected $app;
	protected $user;

	public function __construct() 
	{
		$this->spath 	= WP_PLUGIN_DIR . '/extranet/public/partials/';
		$this->url 		= get_permalink();
		$this->app 		= ExtranetApp::getInstance();
		$this->user 	= ExtranetUser::getInstance();

		$this->menu   	= new ExtranetMenu($this->app->input->get('view','','cmd'));
	}

	public function render()
	{	
		// Get the layout path.
		$path = $this->spath . $this->getLayout() . '.php';
	
		// Check if the layout path was found.
		if (!$path)
		{
			throw new \RuntimeException('Layout Path Not Found');
		}

		$path = Path::clean($path);

		// Start an output buffer.
		ob_start();

		// Load the layout.
		include $path;

		// Get the layout contents.
		$output = ob_get_clean();

		return $output;
	}
}