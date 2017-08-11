<?php
// front-end controller for files

namespace Extranet\Controller;

use Joomla\Controller\AbstractController;
use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetUser;


class ExtranetLoginController extends AbstractController 
{
	protected $user;
	protected $nonce;
	protected $app;

	public function __construct()
	{
		$this->app = ExtranetApp::getInstance();
		$this->user = ExtranetUser::getInstance();
		$this->nonce = $this->app->input->get('nonce','','cmd');

		parent::__construct(null, $this->app);
	}


	public function execute() 
	{	
		$task = $this->app->input->get('task','','cmd');
		
		if (!wp_verify_nonce($this->nonce,'extranet.login'))
		{
		    die('Security check'); 
		} 

		if (preg_match('/^extranet.(login|logout)$/', $task, $matches)) 
		{
			if (method_exists($this, $matches[1]))
			{
				call_user_func(array($this, $matches[1]));
			}
		}
		exit();
	}


	public function login() 
	{
		$username = sanitize_user($this->app->input->get('user_login','','raw')); 
		$password = $this->app->input->get('user_password', '', 'raw');

		if ($this->user->exists()) 
		{
			$this->app->redirect($this->app->url(array('view' => 'dashboard')));
		}

		if (empty($username) || empty($password))
		{
			$this->app->redirect($this->app->url(array('view' => 'default')));
		}

		$login = wp_signon(
					array(
						'user_login' => $username,
						'user_password' => $password,
					)
				);

		if ($login instanceof \WP_User)
		{
			$this->app->redirect($this->app->url(array('view' => 'dashboard')));
		}

		$this->app->redirect($this->app->url(array('view' => 'default')));
	}
	

	public function logout()
	{
		 wp_logout();
		 $this->app->redirect($this->app->url(array('view' => 'default')));
	}
}