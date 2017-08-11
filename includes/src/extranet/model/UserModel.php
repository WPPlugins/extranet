<?php
// back-end
namespace Extranet\Model;

use Joomla\Model\AbstractModel;
use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetUser;
use Joomla\Form\Form;
use Joomla\Filesystem\Folder;
use Extranet\Helper\ExtranetFolder;

class UserModel extends AbstractModel 
{
	public $app;
	public $form;
	public $user;

	public function __construct($state = null)
	{
		$this->app = ExtranetApp::getInstance();
		$this->form = new Form('user');
		$this->form->loadFile(ABSPATH . 'wp-content/plugins/extranet/includes/src/extranet/model/forms/user.xml');
		$this->loadData();

		parent::__construct($state);
	}


	public function getFolders($location, $userID)
	{	
		$fds = array();
		$folders = Folder::folders($this->getPath($location), '.', false, true);

		if (!empty($folders))
		{
			foreach ($folders as $path)
			{
				$f 	= new \stdclass;
				$fd = new ExtranetFolder($path);
				$permissions = $fd->permissions();
				$f->path = urlencode(base64_encode($fd->getRelativePath()));
				$f->name = htmlspecialchars($fd->getFileName(), ENT_QUOTES, 'UTF-8');
				$f->individual = $permissions->extract($userID)->value(); 
				$f->aggregate = $permissions->extractAggregate($userID)->value();
				$fds[] = $f;
			}
		}

		$fds = $this->sort($fds);
		return $fds;
	}


	public function save($data) 
	{
		if ($data['id'])
		{
			$user = ExtranetUser::getInstance($data['id']);
			foreach ($data as $key => $value)
			{
				if ($key != 'id')
				{
					$user->setMeta($key, $value);
				}
			}
			return true;
		}

		return false;
	}


	protected function loadData()
	{
		$id = $this->app->input->get('id', 0, 'int');
		$this->user = ExtranetUser::getInstance($id);

		$this->form->bind(array(
			'user[_extranet_enabled]' => (int) $this->user->enabled(),
			'user[_extranet_user_homepage]' =>  $this->user->homepage(),
		));
	}


	protected function sort($items)
	{
		usort($items, function($a,$b){$x = sanitize_title($a->name); $y = sanitize_title($b->name); return strcasecmp($x, $y);});
		return $items;
	}


	public function getPrevious($location)
	{
		$extra 	= base64_decode($location);
		
		if (!$extra || strpos($extra,'/') === false)
		{
			return '';
		}

		$parts = explode('/', $extra);
		array_pop($parts);
		return implode('/', $parts);
	}


	protected function getPath($extra)
	{
		$path 	= $this->app->getStorage();

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
