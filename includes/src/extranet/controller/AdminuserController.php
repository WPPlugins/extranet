<?php
// admin controller for user
namespace Extranet\Controller;

use Joomla\Controller\AbstractController;
use Extranet\ExtranetApp;
use Extranet\Model\UserModel;

class AdminuserController extends AbstractController 
{
	protected $model;
	protected $app;
	protected $nonce;

	public function __construct()
	{
		$this->app = ExtranetApp::getInstance();
		$this->model = new UserModel();
		$this->nonce = $this->app->input->get('nonce','','cmd');

		parent::__construct(null, $this->app);
	}


	public function execute() 
	{
		if (!wp_verify_nonce($this->nonce,'adminuser'))
		{
		    die('Security check'); 
		} 

		if(!is_admin()) 
		{
			return;
		}

		$task = $this->app->input->get('task','','cmd');
		
		if (preg_match('/adminuser.([a-zA-Z]*)/', $task, $matches)) 
		{
			if (method_exists($this, $matches[1]))
			{
				call_user_func(array($this, $matches[1]));
			}
		}
		exit();
	}


	public function save() 
	{
		$data = $this->app->input->get('user',array(), 'array');

		if ($data['id'])
		{
			$this->model->save($data);
			$this->app->redirect(site_url() . '/wp-admin/admin.php?page=extranet/admin/partials/users.php&layout=user&id=' . $data['id']);
		}		

		$this->app->redirect(site_url() . '/wp-admin/admin.php?page=extranet/admin/partials/users.php');
	}


	public function folders()
	{
		$id = $this->app->input->get('id', 0, 'int');
		$path = $this->app->input->get('path','', 'base64');

		$response = new \stdclass;
		$response->folders = $this->model->getFolders($path, $id);
		$response->previous = base64_encode($this->model->getPrevious($path));

		echo json_encode($response);
		exit();
	}
}