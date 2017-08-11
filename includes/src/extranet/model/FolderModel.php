<?php
// back-end
namespace Extranet\Model;

use Joomla\Model\AbstractModel;
use Joomla\Filesystem\Folder;
use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetFile;
use Extranet\Helper\ExtranetFolder;

class FolderModel extends AbstractModel 
{
	public $app;
	public $folder;


	public function __construct($state = null)
	{
		$this->app 		= ExtranetApp::getInstance();
		$this->folder 	= new ExtranetFolder($this->getPath());
		parent::__construct($state);
	}


	public function getPermissionsList()
	{	
		$users = $this->app->getUsers();
		$rules = $this->folder->permissions();

		$permissions = array();

		if (!empty($users))
		{
			foreach ($users as $user)
			{
				$line = new \stdclass;
				$line->username = esc_html($user->first_name . ' ' . $user->last_name);
				$line->nick = esc_html($user->user_login);
				$line->id = $user->ID;
				$line->aggregate = $rules->extractAggregate($user->ID)->value();
				$line->individual = $rules->extract($user->ID)->value();
				$line->allowed = (int) get_user_meta($user->ID, '_extranet_enabled', true);
				$permissions[] = $line;
			}
		}

		return json_encode($permissions);
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