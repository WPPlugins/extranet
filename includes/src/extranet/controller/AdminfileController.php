<?php
// admin controller for files
namespace Extranet\Controller;

use Joomla\Controller\AbstractController;
use Joomla\Filesystem\File;
use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetFile;
use Extranet\Helper\ExtranetFilter;

class AdminfileController extends AbstractController 
{
	protected $app;
	protected $nonce;


	public function __construct()
	{
		$this->app = ExtranetApp::getInstance();
		$this->nonce = $this->app->input->get('nonce','','cmd');
		parent::__construct(null, $this->app);
	}


	public function execute() 
	{
		if (!wp_verify_nonce($this->nonce,'adminfile'))
		{
		    die('Security check'); 
		} 

		if(!is_admin()) 
		{
			return;
		}

		$task = $this->app->input->get('task','','cmd');
		
		if (preg_match('/adminfile.([a-zA-Z]*)/', $task, $matches)) 
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
		$renameto 	= ExtranetFilter::cleanFileName(stripcslashes($this->app->input->get('new','', 'raw')));

		if ($path)
		{	
			$path = $this->app->getStorage() . base64_decode($path);	
			$file = new ExtranetFile($path);

			if(!file_exists($file->getRealPath()))
			{
				$this->json_output(array('error'=>__('Invalid file path','extranet')));
			}

			if(!File::move($file->getRealPath(), $file->getPath() . '/' . $renameto))
			{
				$this->json_output(array('error'=>__('Failed to rename file.','extranet')));
			}

			$newfile = new ExtranetFile($file->getPath() . '/' . $renameto);

			$this->json_output(array('error'=>'', 'renameto'=>$renameto,'value'=>base64_encode($newfile->getRelativePath())));
		}
	}


	protected function delete() 
	{

		$path 	= $this->app->input->get('path','', 'base64');
		$path 	= $path ? base64_decode($path) : '';
		$full 	= $this->app->getStorage() . $path;

		if (!$path || !file_exists($full)) 
		{
			$this->json_output(array('error'=>__('Failed to delete file.','extranet')));
		}

		$file = new ExtranetFile($full);

		if (!File::delete($file->getRealPath())) 
		{
			$this->json_output(array('error'=>__('Something went wrong. Failed to delete file.','extranet')));
		}

		$this->json_output(array('error'=>''));
	}


	protected function json_output($text)
	{
		echo json_encode($text);
		exit();
	}	
}