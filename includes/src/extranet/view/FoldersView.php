<?php
// back-end
namespace Extranet\View;

use Joomla\View\AbstractHtmlView;
use Joomla\Filesystem\Path;

class FoldersView extends AbstractHtmlView 
{
	protected $spath;
	protected $url; 

	public function __construct() 
	{
		$this->spath 	= WP_PLUGIN_DIR . '/extranet/admin/partials/folders/';
		$this->url 		= get_site_url() . '/wp-admin/admin.php?page=extranet/admin/partials/folders.php';
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