<?php
// front-end controller for folders

namespace Extranet\Controller;

use Joomla\Controller\AbstractController;
use Joomla\Filesystem\Folder;
use Extranet\Model\FoldersModel;
use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetFile;
use Extranet\Helper\ExtranetFolder;
use Extranet\Helper\ExtranetUser;
use Extranet\Helper\ExtranetObserverEmail;
use Extranet\Helper\ExtranetFilter;
use Extranet\Helper\ExtranetEvent;

class ExtranetFoldersController extends AbstractController 
{
	protected $user;
	protected $nonce;
	protected $model;
	protected $app;

	public function __construct()
	{
		$this->app = ExtranetApp::getInstance();
		$this->app->attach(new ExtranetObserverEmail());
		$this->user = ExtranetUser::getInstance();
		$this->nonce = $this->app->input->get('nonce','','cmd');

		parent::__construct(null, $this->app);
	}


	public function execute() 
	{
		if (!$this->user->exists())
		{
			die('Unexisting user'); 
		}
 
		if (!wp_verify_nonce($this->nonce,'extranet.folders'))
		{
		    die('Security check'); 
		} 

		$task = $this->getApplication()->input->get('task','','cmd');
		
		if (preg_match('/extranet.folders.([a-zA-Z]*)/', $task, $matches)) 
		{
			if (method_exists($this, $matches[1]))
			{
				call_user_func(array($this, $matches[1]));
			}
		}
		exit();
	}


	public function newf()
	{
		$path 		= $this->getApplication()->input->get('path','', 'base64');
		$createas 	= ExtranetFilter::cleanFileName(trim(stripcslashes($this->getApplication()->input->get('new','', 'raw'))));
		$path 		= $path ? base64_decode($path) : '';
		$fullpath	= $this->getApplication()->getStorage() . $path;
		$newpath	= $fullpath . '/' . $createas;

		try 
		{
			$location 		= new ExtranetFolder($fullpath);
			$permissions 	= $location->permissions()->extractAggregate($this->user->ID);
		} 
		catch (\Exception $e) 
		{
			$this->json_output(array('error'=>$e->getMessage()));
		}

		if (!$permissions->is_mkdir())
		{
			$this->json_output(array('error'=>__('Sorry. You do not have permission to do this operation.','extranet')));
		}

		// check if a folder with the same name exist already on server
		if (is_dir($newpath))
		{
			$this->json_output(array('error'=>__('This folder already exists. Please use a different name.','extranet')));
		}

		// if we cannot create the folder
		if (!mkdir($newpath))
		{
			$this->json_output(array('error'=>__('Unable to create this folder.','extranet')));
		}

		try 
		{
			$newlocation = new ExtranetFolder($newpath);
		} 
		catch (\Exception $e) 
		{
			$this->json_output(array('error'=>$e->getMessage()));
		}

		$new = new \stdclass;
		$new->name = htmlspecialchars($createas, ENT_QUOTES, 'UTF-8');
		$new->path = urlencode(base64_encode($newlocation->getRelativePath()));
		$new->type = 'folder';
		$new->url  = $this->getApplication()->url(array('view'=>'folders', 'path' => $new->path));

		$event = new ExtranetEvent('mkdir');
		$this->app->trigger($event);

		// return success with new folder name
		$this->json_output(array(
				'error' => '',
				'new' => $new,	
			)
		);
	}


	protected function delete() 
	{
		$parent_path 	= $this->getApplication()->input->get('parent','', 'base64');
		$folder_path 	= $this->getApplication()->input->get('item','', 'base64');
		$parent_path 	= $this->getApplication()->getStorage() . ($parent_path ? base64_decode($parent_path) : '');
		$folder_path 	= $this->getApplication()->getStorage() . ($folder_path ? base64_decode($folder_path) : '');
		
		try 
		{
			$parent = new ExtranetFolder($parent_path);
			$folder = new ExtranetFolder($folder_path);
			$permissions = $parent->permissions()->extractAggregate($this->user->ID);
		} 
		catch (Exception $e) 
		{
			$this->json_output(array('error'=>$e->getMessage()));
		}

		// check if user has permission to delete files in this directory
		if (!$permissions->is_rmdir())
		{
			$this->json_output(array('error'=>__('Sorry. You do not have permission to do this operation.','extranet')));
		}

		// if provided directory is not the actual parent
		if ($parent->getRealPath() != $folder->getPath())
		{
			$this->json_output(array('error'=>__('Wrong file or parent location.','extranet')));
		}

		// if folder or parent doesn't exist
		if (!$parent->isDir() || !$folder->isDir())
		{
			$this->json_output(array('error'=>__('Selected folder or parent directory does not exist.','extranet')));
		}

		$subfolders = Folder::folders($folder->getRealPath(), '.', true, true);
		$rules 		= $this->getRules($subfolders);
		array_push($rules, $folder->permissions()->rulefile());

		// try to delete the file
		if (!Folder::delete($folder->getRealPath())) 
		{
			$this->json_output(array('error'=>__('Something went wrong. Failed to delete file.','extranet')));
		}

		// everything went fine so we can now delete also the files containing the permissions
		if (!empty($rules))
		{
			foreach ($rules as $rule)
			{
				if (file_exists($rule)) 
				{
					unlink($rule);
				}
			}
		}

		$event = new ExtranetEvent('rmdir');
		$this->app->trigger($event);

		$this->json_output(array('error'=>''));
	}


	protected function getRules($subfolders) 
	{
		$rules = array();

		if (!empty($subfolders))
		{
			foreach ($subfolders as $path)
			{
				try 
				{
					$folder = new ExtranetFolder($path);
					$rules[] = $folder->permissions()->rulefile();
				} 
				catch (\Exception $e) {echo $e->getMessage();}	
			}
		}

		return $rules;
	}


	protected function json_output($text)
	{
		echo json_encode($text);
		exit();
	}	
}