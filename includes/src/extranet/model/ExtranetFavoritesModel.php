<?php
// model for front-end favorites
namespace Extranet\Model;

use Joomla\Model\AbstractModel;
use Joomla\Filesystem\Folder;
use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetUser;
use Extranet\Helper\ExtranetFile;
use Extranet\Helper\ExtranetFolder;

class ExtranetFavoritesModel extends AbstractModel 
{
	public $app;
	protected $user;


	public function __construct($state = null)
	{
		$this->app 	= ExtranetApp::getInstance();
		$this->user = ExtranetUser::getInstance();

		parent::__construct($state);
	}
	

	public function getFavorites()
	{
		$favorites = array();
		$stored = $this->user->favorites()->toArray();

		if (!empty($stored))
		{
			foreach ($stored as $fav)
			{	
				try
				{
					$file = new ExtranetFile($this->app->getStorage() . base64_decode($fav->path));
					$parent = new ExtranetFolder($file->getPath());
					$f = new \stdclass;
					$f->name = $fav->name;
					$f->path = $fav->path;
					$f->rule = $parent->permissions()->extractAggregate($this->user->ID);
					$f->parent = base64_encode($parent->getRelativePath());
					array_push($favorites, $f);
				}
				catch (\Exception $e)
				{

				}
			}
		}
		return $favorites; 
	}


	protected function sort($items)
	{
		usort($items, function($a,$b){$x = sanitize_title($a->name); $y = sanitize_title($b->name); return strcasecmp($x, $y);});
		return $items;
	}
}