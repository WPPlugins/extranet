<?php
// front-end controller for files

namespace Extranet\Controller;

use Joomla\Controller\AbstractController;
use Joomla\Filesystem\File;
use Joomla\Input\Files;
use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetFile;
use Extranet\Helper\ExtranetFolder;
use Extranet\Helper\ExtranetUser;
use Extranet\Helper\ExtranetObserverEmail;
use Extranet\Helper\ExtranetFilter;
use Extranet\Helper\ExtranetEvent;

class ExtranetFileController extends AbstractController 
{

	protected $user;
	protected $nonce;
	protected $app;
	protected $preview;

	public function __construct()
	{
		$this->app = ExtranetApp::getInstance();
		$this->app->attach(new ExtranetObserverEmail());
		$this->user = ExtranetUser::getInstance();
		$this->nonce = $this->app->input->get('nonce','','cmd');
		$this->preview = $this->app->input->get('preview', 0, 'int');

		parent::__construct(null, $this->app);
	}


	public function execute() 
	{
		$task = $this->app->input->get('task','','cmd');
		
		if (!$this->user->exists() && ($task != 'extranet.file.sharedownload'))
		{
			die('Unexisting user'); 
		}
 
		if (!wp_verify_nonce($this->nonce,'extranet.file'))
		{
		    die('Security check'); 
		} 

		if (preg_match('/extranet.file.([a-zA-Z]*)/', $task, $matches)) 
		{
			if (method_exists($this, $matches[1]))
			{
				call_user_func(array($this, $matches[1]));
			}
		}
		exit();
	}


	public function upload()
	{
		$success = array();
		$failure = '';
		$files = new Files();
		$todo = $files->get('mf_file_upload', array(), 'array');
		$path = $this->app->input->get('path', '', 'base64');
		$full = $this->app->getStorage() . ($path ? base64_decode($path) : '');
		
		try 
		{
			$location = new ExtranetFolder($full);
			$permissions = $location->permissions()->extractAggregate($this->user->ID);
		} 
		catch (\Exception $e) 
		{
			$this->json_output(array('error'=>$e->getMessage()));
		}

		if (!$permissions->is_upload())
		{
			$this->json_output(array('error'=>__('Sorry. You do not have permission to do this operation.','extranet')));
		}

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
					if (!$this->allowedExtension($file['name']))
					{
						$failure .= __('Some files could not be uploaded. Possible reason: invalid file extension.', 'extranet');
						continue;
					}

					if (File::upload($file['tmp_name'], $new))
					{
						$newf = new ExtranetFile($new);
						$f = new \stdclass;
						$f->path = urlencode(base64_encode($newf->getRelativePath()));
						$f->name = htmlspecialchars($newf->getFilename(), ENT_QUOTES, 'UTF-8');
						$f->type = 'file';
						$success[] = $f;
					}
				}
				else 
				{
					$failure .= __('Some files could not be uploaded. Possible reason: file already exists.', 'extranet');
				}
			}
		}

		$event = new ExtranetEvent('upload');
		$this->app->trigger($event);

		$this->json_output(
				array(
					'error'=> $failure, 
					'new' => (array) $success,
					)
				);
	}


	protected function allowedExtension($filename)
	{
		$restricted = $this->app->get('restrict_upload');
		$allowed = $this->app->get('allow_upload');
		preg_match('#\.([\w]*)$#', $filename, $matches);
		$extension = isset($matches[1]) ? $matches[1] : '';

		if(!$extension)
		{
			return true;
		}

		if (!empty($allowed))
		{
			if (preg_match('#(^'.$extension.'|'.$extension.',|'.$extension.'$)#', $allowed))
			{	
				return true;
			}
		}
		else
		{	
			if (empty($restricted))
			{
				return true;
			}

			if (preg_match('#(^'.$extension.'|'.$extension.',|'.$extension.'$)#', $restricted))
			{
				return false;
			}
		}

		return true;
	}


	protected function delete() 
	{
		$parent_path 	= $this->app->input->get('parent','', 'base64');
		$file_path 		= $this->app->input->get('item','', 'base64');
		$parent_path 	= $this->app->getStorage() . ($parent_path ? base64_decode($parent_path) : '');
		$file_path 		= $this->app->getStorage() . ($file_path ? base64_decode($file_path) : '');
		
		try 
		{
			$parent = new ExtranetFolder($parent_path);
			$file 	= new ExtranetFile($file_path);
			$permissions = $parent->permissions()->extractAggregate($this->user->ID);
		} 
		catch (Exception $e) 
		{
			$this->json_output(array('error'=>$e->getMessage()));
		}

		// check if user has permission to delete files in this directory
		if (!$permissions->is_unlink())
		{
			$this->json_output(array('error'=>__('Sorry. You do not have permission to do this operation.','extranet')));
		}

		// if provided directory is not the actual parent for the file
		if ($file->getPath() != $parent->getRealPath())
		{
			$this->json_output(array('error'=>__('Wrong file or parent location.','extranet')));
		}

		// if file doesn't exist
		if (!$file->isFile())
		{
			$this->json_output(array('error'=>__('File does not exist.','extranet')));
		}

		// if folder doesn't exist
		if (!$parent->isDir())
		{
			$this->json_output(array('error'=>__('Directory parent does not exist.','extranet')));
		}

		// try to delete the file
		if (!File::delete($file->getRealPath())) 
		{
			$this->json_output(array('error'=>__('Something went wrong. Failed to delete file.','extranet')));
		}

		$event = new ExtranetEvent('unlink');
		$this->app->trigger($event);

		$this->json_output(array('error'=>''));
	}


	public function favorite() 
	{
		$parent_path 	= $this->app->input->get('parent','', 'base64');
		$parent_path 	= $this->app->getStorage() . ($parent_path ? base64_decode($parent_path) : '');
		$item 			= $this->app->input->get('item','', 'base64');

		// decode url since the link to the item is url encoded
		$obj 			= $item ? json_decode(urldecode(base64_decode($item))) : new \stdclass;

		if (!isset($obj->name))
		{
			$this->json_output(array('error'=>__('Unable to mark as favorite.','extranet')));
		}

		try 
		{
			$file = new ExtranetFile($this->app->getStorage() . base64_decode($obj->path));
			$parent = new ExtranetFolder($parent_path);
			$permissions = $parent->permissions()->extractAggregate($this->user->ID);
		}
		catch (\Exception $e)
		{
			$this->json_output(array('error'=>$e->getMessage()));
		}

		/* 	check if user has permission to download
			since in the favorites view we also check for download rights, it's ok to allow this action.

		if (!$permissions->is_download())
		{
			$this->json_output(array('error'=>__('Sorry. You do not have permission to do this operation.','extranet')));
		}
		*/

		// if provided directory is not the actual parent for the file
		if ($file->getPath() != $parent->getRealPath())
		{
			$this->json_output(array('error'=>__('Wrong file or parent location.','extranet')));
		}

		$this->user->favorites()->update($obj);
		$this->json_output(array('error'=>__('File added to your list of favorites.','extranet')));
	}


	public function unfavorite() 
	{
		$path = $this->app->input->get('path','', 'base64');

		// create the object to be searched in the meta
		$item = new \stdclass;
		$item->path = $path;

		// remove the object from favorites
		$this->user->favorites()->remove($item);

		// redirect to favorites view
		$this->app->redirect($this->app->url(array('view'=>'favorites')));
	}


	public function download()
	{
		$parent_path 	= $this->app->input->get('parent','', 'base64');
		$file_path 		= $this->app->input->get('item','', 'base64');
		$parent_path 	= $this->app->getStorage() . ($parent_path ? base64_decode($parent_path) : '');
		$file_path 		= $this->app->getStorage() . ($file_path ? base64_decode($file_path) : '');
		
		try 
		{
			$parent = new ExtranetFolder($parent_path);
			$file 	= new ExtranetFile($file_path);
			$permissions = $parent->permissions()->extractAggregate($this->user->ID);
		} 
		catch (Exception $e) 
		{
			$this->json_output(array('error'=>$e->getMessage()));
		}

		// check if user has permission to download or if he must view it online if he has permission to preview
		if (!$permissions->is_download() || (!$permissions->is_preview() && $this->preview) || ($permissions->is_preview() && $this->preview && !$this->is_previewable($file)))
		{
			$this->json_output(array('error'=>__('Sorry. You do not have permission to do this operation.','extranet')));
		}

		// if provided directory is not the actual parent for the file
		if ($file->getPath() != $parent->getRealPath())
		{
			$this->json_output(array('error'=>__('Wrong file or parent location.','extranet')));
		}

		$event = new ExtranetEvent('download');
		$this->app->trigger($event);

		$this->sendHeaders($file);
		$this->readFile($file);
	}


	public function sharedownload()
	{
		$path = $this->app->input->get('path','', 'string');
		$time = $this->app->input->get('time', 0, 'int');
		$id = $this->app->input->get('id','', 'string');
		
		if ($this->app->get('allow_user_sharing') == 0)
		{
			die('shared not allowed');
		}

		if (sha1(urlencode($path) . $time . NONCE_SALT) != $id)
		{
			die('invalid id');
		}

		if ($availability = $this->app->get('sharing_timeframe'))
		{
			if (($time + $availability*24*3600) < time())
			{
				die('link not available anymore');
			}	
		}

		$path = base64_decode($path);
		
		try 
		{
			$file 	= new ExtranetFile($this->app->getStorage() . $path);
		} 
		catch (Exception $e) 
		{
			die('invalid link');
		}

		$this->sendHeaders($file);
		$this->readFile($file);
		exit();
	}


	protected function sendHeaders($file)
	{
		ob_clean();
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: " . $file->mime());
		header("Content-Length: " .(string)($file->getSize()));
		if (!$this->preview)
		{
			header('Content-Disposition: attachment; filename="' . $file->getFilename() . '"');
			header("Content-Transfer-Encoding: binary\n");
		}
	}


	protected function readFile($file) 
	{
		$fh = fopen($file->getRealPath(), 'rb' );
		while (!feof($fh)) {
			$data = fread($fh, '8192');
			echo $data;
			ob_flush();
			flush();
		}
		fclose($fh);
		exit(0);
	}


	protected function is_previewable($file)
	{
		$allowed = $this->app->get('view_file_types');
		$extension = strtolower($file->getExtension());

		if (empty($allowed))
		{
			return false;
		}

		if (preg_match('#(^'.$extension.'|'.$extension.',|'.$extension.'$)#', $allowed))
		{
			return true;
		}

		return false;
	}


	protected function json_output($text)
	{
		echo json_encode($text);
		exit();
	}	
}