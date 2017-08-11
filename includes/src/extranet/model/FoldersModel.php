<?php
namespace Extranet\Model;

use Joomla\Model\AbstractModel;
use Joomla\Filesystem\Folder;
use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetFile;
use Extranet\Helper\ExtranetFolder;

class FoldersModel extends AbstractModel 
{
	public $app;

	public function __construct($state = null)
	{
		$this->app = ExtranetApp::getInstance();
		parent::__construct($state);
	}

	public function getFolders()
	{
		$fd = array();

		try 
		{
			$folders = Folder::folders($this->getPath(), '.', false, true);
		} 
		catch (\UnexpectedValueException $e) 
		{
			echo $e->getMessage();
			$folders = array();
		}

		if (!empty($folders))
		{
			$fd = array_map(
					function($path){
						$f 	= new \stdclass;
						$fd = new ExtranetFolder($path);
						$f->path = urlencode(base64_encode($fd->getRelativePath()));
						$f->name = htmlspecialchars($fd->getFileName(), ENT_QUOTES, 'UTF-8');
						return $f;
					}, $folders);
		}

		$fd = $this->sort($fd);
		return $fd;
	}


	public function getFiles()
	{
		$fd = array();

		try
		{
			$files = Folder::files($this->getPath(), '.', false, true);
		}
		catch (\UnexpectedValueException $e) 
		{
			echo $e->getMessage();
			$files = array();
		}

		if (!empty($files))
		{
			$fd = array_map(
					function($path){
						$f 	= new \stdclass;
						$fd = new ExtranetFile($path);
						$f->path = base64_encode($fd->getRelativePath());
						$f->name = htmlspecialchars($fd->getFileName(), ENT_QUOTES, 'UTF-8');
						return $f;
					}, $files);
		}
	
		$fd = $this->sort($fd);
		return $fd;
	}


	// getPrevious :: String
	// return the relative location of the previous view
	// used in the Go Back link in the view
	public function getPrevious()
	{
		$extra 	= $this->app->input->get('path', '', 'base64');
		$extra 	= base64_decode($extra);
		
		if (!$extra || strpos($extra,'/') === false)
		{
			return '';
		}

		$parts = explode('/', $extra);
		array_pop($parts);
		return implode('/', $parts);
	}



	// getBreadcrumb :: [Objects]
	// returns a list of objects / empty list
	// each object represents a node in the breadcrumb 
	public function getBreadcrumb()
	{
		$path =  $this->app->input->get('path', '', 'base64');
		$chain = array();

		if ($path)
		{
			$path = base64_decode($path);
			$parts = explode('/',$path);
			$count = count($parts);
			$path = '';


			for ($i=1;$i<$count;$i++)
			{
				$chain[$i] = new \stdclass;
				$chain[$i]->url = isset($chain[$i-1]) ? $chain[$i-1]->url . '/' . $parts[$i] : '/' . $parts[$i];
				$chain[$i]->name = $parts[$i];
			}
		}

		return $chain;
	}


	protected function sort($items)
	{
		usort($items, function($a,$b){$x = sanitize_title($a->name); $y = sanitize_title($b->name); return strcasecmp($x, $y);});
		return $items;
	}


	// returns the current full path on server used in the view
	// used to retrieve folders/files 
	protected function getPath()
	{
		$path 	= $this->app->getStorage();
		$extra 	= $this->app->input->get('path', '', 'base64');

		if(!empty($extra))
		{
			$extra 	= base64_decode($extra);
			$full 	= realpath($this->app->getStorage() . '/' . $extra);
			
			if (stripos($full, $this->app->getStorage()) !== false)
			{
				$path = $full;
			}
		}
		
		return $path;
	}
}