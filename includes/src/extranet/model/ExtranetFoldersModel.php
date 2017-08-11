<?php
// model for front-end folders
namespace Extranet\Model;

use Joomla\Model\AbstractModel;
use Joomla\Filesystem\Folder;
use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetUser;
use Extranet\Helper\ExtranetFile;
use Extranet\Helper\ExtranetFolder;

class ExtranetFoldersModel extends AbstractModel 
{
	public $app;
	protected $user;
	protected $folder;
	protected $rule;
	protected $extensions;

	public function __construct($state = null)
	{
		$this->app 	= ExtranetApp::getInstance();
		$this->user = ExtranetUser::getInstance();
		$this->folder = new ExtranetFolder($this->getPath());
		$this->rule = $this->folder->permissions()->extractAggregate($this->user->ID);

		parent::__construct($state);
	}


	public function getFolders()
	{
		$fd = array();
		$folders = Folder::folders($this->getPath(), '.', false, true);

		if (!$this->rule->is_list())
		{
			return array();
		}

		// generate the list of folders to use in the javascript
		if (!empty($folders))
		{
			foreach ($folders as $path)
			{
				$folder = new ExtranetFolder($path);
				$permis = $folder->permissions()->extractAggregate($this->user->ID);

				if ($permis->is_list())
				{
					$f 			= new \stdclass;
					$f->name 	= htmlspecialchars($folder->getFileName(), ENT_QUOTES, 'UTF-8');
					$f->path 	= urlencode(base64_encode($folder->getRelativePath()));
					$f->type 	= 'folder';
					$f->url 	= $this->app->url(array('view'=>'folders', 'path' => $f->path));
					$f->creation = $folder->getCTime();
					$f->size 	= $folder->getSize();
					array_push($fd, $f);
				} 
			}
		}

		$fd = $this->sort($fd, 'folders');
		return $fd;
	}


	public function getFiles()
	{
		$fd = array();

		if ($this->folder->isRoot())
		{
			return array();
		}

		if ($this->rule->is_list())
		{
			$files = Folder::files($this->getPath(), '.', false, true);
			$fd = array_map(
				function($path){
					$f = new \stdclass;
					$fd = new ExtranetFile($path);
					$f->name = htmlspecialchars($fd->getFileName(), ENT_QUOTES, 'UTF-8');
					$f->path = urlencode(base64_encode($fd->getRelativePath()));
					$f->type = 'file';
					$f->ext = strtolower($fd->getExtension());
					$f->creation = $fd->getCTime();
					$f->size 	= $fd->getSize();
					$time = time();
					if ($this->app->get('allow_user_sharing')) 
					{
						$f->share  	= $this->app->url(array('task'=>'extranet.file.sharedownload', 'path' => $f->path, 'time'=>$time, 'id'=>sha1($f->path . $time . NONCE_SALT), 'nonce'=>wp_create_nonce('extranet.file')));
					}
					return $f;
				}, 
				$files
			);
			
			$fd = $this->sort($fd, 'files');
		}

		return $fd;
	}


	public function getRule()
	{
		return $this->rule;
	}


	public function getFolder()
	{
		return $this->folder;
	}


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


	public function getBreadcrumb()
	{
		$path =  $this->requestedPath();
		$chain = array();

		if ($path)
		{
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


	// getExtensions :: String
	// a list of extensions for which we can generate the view link 
	public function getExtensions()
	{
		$ext = $this->app->get('view_file_types');

		if (empty($ext))
		{
			return '[]';
		}

		$ext = strtolower($ext);

		if (stripos($ext, ',') !== false)
		{
			return json_encode(explode(',', $ext));
		}
		else
		{
			return '["' . $ext . '"]';
		}
	}


	protected function sort($items, $type)
	{
		$orderby = $type == 'files' ? $this->app->get('file_order') . $this->app->get('file_order_dir') : $this->app->get('folder_order') . $this->app->get('folder_order_dir');

		switch ($orderby) 
		{
			case 'nameASC':
			default:
				usort($items, function($a,$b){
					$x = sanitize_title($a->name); 
					$y = sanitize_title($b->name); 
					$r = strcasecmp($x, $y);
					return $r;
				});
			break;

			case 'nameDESC':
				usort($items, function($a,$b){
					$x = sanitize_title($a->name); 
					$y = sanitize_title($b->name); 
					$r = strcasecmp($x, $y);
					return (-1) * $r;
				});
			break;

			case 'creationASC':
				usort($items, function($a,$b){
					return $a->creation > $b->creation;
				});
			break;

			case 'creationDESC':
				usort($items, function($a,$b){
					return $a->creation < $b->creation;
				});
			break;

			case 'sizeASC':
				usort($items, function($a,$b){
					return $a->size > $b->size;
				});
			break;

			case 'sizeDESC':
				usort($items, function($a,$b){
					return $a->size < $b->size;
				});
			break;
		}

		return $items;
	}


	protected function getPath()
	{
		$path 	= $this->app->getStorage();
		$extra 	= $this->requestedPath();

		if(!empty($extra))
		{
			$full 	= realpath($this->app->getStorage() . '/' . $extra);
			
			if (stripos($full, $this->app->getStorage()) !== false)
			{
				$path = $full;
			}
		}

		return $path;
	}


	protected function requestedPath()
	{
		$path =  $this->app->input->get('path', '', 'base64');
		return $path ? base64_decode($path) : $path;
	}
}