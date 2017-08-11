<?php
namespace Extranet\Helper;

use Extranet\ExtranetApp;


class ExtranetFile extends \SplFileInfo 
{
	protected $storage;

	public function __construct($path)
	{
		$this->storage = ExtranetApp::getInstance()->getStorage();

		if (!$this->allowed($path))
		{
			throw new \Exception("Invalid path", 1);
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
		return sha1_file($this->getRealPath());
	}
	

	public function mime() 
	{
		if (class_exists('finfo')) 
		{
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			return finfo_file($finfo, $this->getRealPath());
		} 
		elseif (function_exists('mime_content_type')) 
		{
			return mime_content_type($this->getRealPath());
		} 
		else 
		{
			return 'application/octet-stream';
		}			
	}


	protected function allowed($path)
	{
		$path 		= realpath($path);

		if(strpos($path, $this->storage) === false) 
		{
			return false;
		}

		return true;
	}
}