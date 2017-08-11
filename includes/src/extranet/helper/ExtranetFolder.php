<?php
namespace Extranet\Helper;

use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetFolderPermissions;

class ExtranetFolder extends \SplFileInfo 
{
	protected $storage;

	public function __construct($path)
	{
		$this->storage = ExtranetApp::getInstance()->getStorage();

		if (!$this->allowed($path))
		{
			throw new \Exception("Invalid path" . $path, 1);
		}
		parent::__construct($path);
	}

	// get the path relative to default storage path, useful when browsing 
	public function getRelativePath()
	{
		return substr($this->getRealPath(), strlen($this->storage));
	}


	public function SHA1()
	{
		return sha1($this->getRealPath());
	}


	public function permissions()
	{
		return new ExtranetFolderPermissions($this);
	}


	public function getParent()
	{
		return new ExtranetFolder($this->getPath());
	}


	public function isRoot()
	{
		return ($this->getRelativePath() == '');
	}


	// returns a list of parent folders
	// if current folder path is /foo/bar/doc -> [ExtranetFolder(/foo), ExtranetFolder(/foo/bar)]
	public function getParents()
	{
		static $parents = array();

		$parent = $this->getParent();

		if ($parent->isRoot())
		{
			return array_reverse($parents);
		}
		else
		{
			$parents[] = $parent;
		}

		return $parent->getParents();
	}


	protected function allowed($path)
	{
		$path 	= realpath($path);

		if(strpos($path, $this->storage) === false) 
		{
			return false;
		}

		return true;
	}
}