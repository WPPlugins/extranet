<?php
// admin controller for settings
namespace Extranet\Controller;

use Joomla\Controller\AbstractController;
use Extranet\ExtranetApp;
use Extranet\Model\SettingsModel;

class AdminsettingsController extends AbstractController 
{
	protected $model;
	protected $app;
	protected $nonce;

	public function __construct()
	{
		$this->app = ExtranetApp::getInstance();
		$this->model = new SettingsModel();
		$this->nonce = $this->app->input->get('nonce','','cmd');

		parent::__construct(null, $this->app);
	}


	public function execute() 
	{
		if (!wp_verify_nonce($this->nonce,'adminsettings'))
		{
		    die('Security check'); 
		} 

		if(!is_admin()) 
		{
			return;
		}

		$task = $this->app->input->get('task','','cmd');
		
		if (preg_match('/adminsettings.([a-zA-Z]*)/', $task, $matches)) 
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
		$data = $this->app->input->get('settings',array(), 'array');
		
		if ($data)
		{
			$this->model->save($data);
		}		

		$this->app->redirect(site_url() . '/wp-admin/admin.php?page=extranet/admin/partials/settings.php');
	}
}