<?php
// admin controller for folders
namespace Extranet\Controller;

use Joomla\Controller\AbstractController;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\Input\Files;
use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetFolder;
use Extranet\Helper\ExtranetFile;
use Extranet\Helper\ExtranetFilter;
use Extranet\Helper\ExtranetEvent;
use Extranet\Helper\SendNotificationsObserver;

class AdminfolderController extends AbstractController 
{
	protected $app;
	protected $nonce;

	public function __construct()
	{
		$this->app = ExtranetApp::getInstance();
		$this->app->attach(new SendNotificationsObserver());
		$this->nonce = $this->app->input->get('nonce','','cmd');

		parent::__construct(null, $this->app);
	}


	public function execute() 
	{
		if (!wp_verify_nonce($this->nonce,'adminfolder'))
		{
		    die('Security check'); 
		} 

		if(!is_admin()) 
		{
			return;
		}

		$task = $this->app->input->get('task','','cmd');
		
		if (preg_match('/adminfolder.([a-zA-Z]*)/', $task, $matches)) 
		{
			if (method_exists($this, $matches[1]))
			{
				call_user_func(array($this, $matches[1]));
			}
		}
		exit();
	}


	protected function rename()
	{	
		$path 		= $this->app->input->get('path','', 'base64');
		$renameto 	= ExtranetFilter::cleanFileName(trim(stripcslashes($this->app->input->get('new','', 'raw'))));

		if ($path)
		{	
			$path = $this->app->getStorage() . base64_decode($path);	
			$folder = new ExtranetFolder($path);

			// if folder we must rename doesn't exist on server
			if(!file_exists($folder->getRealPath()))
			{
				$this->json_output(array('error'=>__('Invalid folder path.','extranet')));
			}

			// if a folder with the same name aready exists
			if(file_exists($folder->getPath() . '/' . $renameto))
			{
				$this->json_output(array('error'=>__('A folder with this name already exists.','extranet')));
			}

			// we must delete also the files containing the permissions associated with folder/subfolders
			// so before deleting the folder, get the locations of the rules
			$subfolders = Folder::folders($folder->getRealPath(), '.', true, true);
			$rules 		= $this->getRules($subfolders);
			array_push($rules, $folder->permissions()->rulefile());

			// if something wrong goes when we try to rename it (move it)
			if(!Folder::move($folder->getRealPath(), $folder->getPath() . '/' . $renameto))
			{
				$this->json_output(array('error'=>__('Failed to rename folder.','extranet')));
			}

			// everything went fine so we can now delete also the files containing the permissions
			if (!empty($rules))
			{
				foreach ($rules as $rule)
				{
					unlink($rule);
				}
			}

			// load new folder
			$newfolder = new ExtranetFolder($folder->getPath() . '/' . $renameto);

			// return success (non error), with the new path and new url
			$this->json_output(
				array(
					'error'=>'', 
					'renameto'=>$renameto,
					'value'=>base64_encode($newfolder->getRelativePath()), 
					'href'=>base64_encode(site_url() . '/wp-admin/admin.php?page=extranet/admin/partials/folders.php&path=' . base64_encode($newfolder->getRelativePath())),
					)
				);
		}
	}


	protected function newf() 
	{

		$path 		= $this->app->input->get('path','', 'base64');
		$createas 	= ExtranetFilter::cleanFileName(trim(stripcslashes($this->app->input->get('new','', 'raw'))));

		$path 		= $path ? base64_decode($path) : '';
		$newpath 	= $this->app->getStorage() . $path . '/' . $createas;

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

		$new = new \stdclass;
		$new->path = urlencode(base64_encode($path . '/' . $createas));
		$new->name = htmlspecialchars($createas, ENT_QUOTES, 'UTF-8');

		// return success with new folder name
		$this->json_output(array(
				'error' => '',
				'new' => $new,	
			)
		);
	}


	protected function delete() 
	{
		$path 	= $this->app->input->get('path','', 'base64');
		$path 	= $path ? base64_decode($path) : '';
		$full 	= $this->app->getStorage() . $path;

		// if no relative path was provided or no folder exists at that location
		if (!$path || !file_exists($full)) 
		{
			$this->json_output(array('error'=>__('Failed to delete folder.','extranet')));
		}

		// load the folder object, it will automatically restrict to allowed storage path
		$folder = new ExtranetFolder($full);
		
		// we must delete also the files containing the permissions associated with folder/subfolders
		// so before deleting the folder, get the locations of the rules
		$subfolders = Folder::folders($folder->getRealPath(), '.', true, true);
		$rules 		= $this->getRules($subfolders);
		array_push($rules, $folder->permissions()->rulefile());

		// if something goes wrong trying to delete
		if (!Folder::delete($folder->getRealPath())) 
		{
			$this->json_output(array('error'=>__('Something went wrong. Failed to delete folder.','extranet')));
		}

		// everything went fine so we can now delete also the files containing the permissions
		if (!empty($rules))
		{
			foreach ($rules as $rule)
			{
				unlink($rule);
			}
		}
		
		// everything ok, return empty error message
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
					$rulefile = $folder->permissions()->rulefile();

					if (file_exists($rulefile))
					{
						$rules[] = $rulefile;
					}
				} 
				catch (\Exception $e) {echo $e->getMessage();}	
			}
		}

		return $rules;
	}


	protected function upload() 
	{
		$files = new Files();
		$todo = $files->get('mf_file_upload', array(), 'array');
		$path = $this->app->input->get('path', '', 'base64');
		$path = $path ? base64_decode($path) : '';
		$full = $this->app->getStorage() . $path;
		$location = new ExtranetFolder($full);
		$success = array();
		$failure = '';

		if (!is_dir($location->getRealPath()))
		{
			$this->json_output(array('error'=>__('Wrong location.','extranet')));
		}

		if (!empty($todo))
		{
			foreach ($todo as $file)
			{	
				$file['name'] = ExtranetFilter::cleanFileName($file['name']);
				$new = $location->getRealPath() . '/' . $file['name'];

				if (!file_exists($new))
				{
					if (File::upload($file['tmp_name'], $new))
					{
						$newf = new ExtranetFile($new);
						$f = new \stdclass;
						$f->name = htmlspecialchars($newf->getFilename(), ENT_QUOTES, 'UTF-8');
						$f->path = urlencode(base64_encode($newf->getRelativePath()));
						$success[] = $f;
					}
				}
				else 
				{
					$failure = __('Some files could not be uploaded.', 'extranet');
				}
			}
		}

		$event = new ExtranetEvent('adminuploadfiles', array('location'=>$location));
		$this->app->trigger($event);

		$this->json_output(
				array(
					'error'=> $failure, 
					'new' => (array) $success,
					)
				);
	}


	public function setPermissions()
	{
		$path = $this->app->input->get('folder', '', 'base64');
		$user = $this->app->input->get('user', '', 'int');
		$perm = $this->app->input->get('permissions', '', 'cmd');
		$path = $path ? base64_decode($path) : '';
		$full = $this->app->getStorage() . $path;

		$folder = new ExtranetFolder($full);
		$permissions = $folder->permissions();
	
		if (!$permissions->existRuleFile())
		{
			$p = new \stdclass;
			$p->permission = $perm;
			$p->users = array($user);

			$permissions->set('resource', base64_encode($path));
			$permissions->set('rules', array($p));
			$permissions->save();
		}
		else
		{
			$reg = $permissions->registry();

			if ($reg->exists('rules')) 
			{
				$rules = $reg->get('rules');
				$found = false;

				foreach ($rules as $rule)
				{
					if ($rule->permission == $perm)
					{
						$found = true;

						if (in_array($user, $rule->users))
						{
							$this->json_output(array('error'=>''));
						}

						array_push($rule->users, $user);
					}
					else 
					{
						if (in_array($user, $rule->users))
						{
							$rule->users = array_diff($rule->users, array($user));
						}
					}
				}

				if (!$found)
				{
					$p = new \stdclass;
					$p->permission = $perm;
					$p->users = array($user);
					$rules[] = $p;
				}

				$reg->set('rules', $rules);
				$permissions->save();
			}
		}

		$this->json_output(array('error'=>''));
	}


	protected function json_output($text)
	{
		echo json_encode($text);
		exit();
	}	
}