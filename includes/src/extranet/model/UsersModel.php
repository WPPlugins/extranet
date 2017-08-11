<?php
// back-end
namespace Extranet\Model;

use Joomla\Model\AbstractModel;
use Extranet\ExtranetApp;

class UsersModel extends AbstractModel 
{
	public $app;

	public function __construct($state = null)
	{
		$this->app = ExtranetApp::getInstance();
		parent::__construct($state);
	}


	public function getUsers()
	{
		$wpusers = array();
		$users = $this->app->getUsers();
		foreach ($users as $user)
		{
			$u = new \stdclass;
			$u->id = $user->data->ID;
			$u->username = esc_html($user->user_login);
			$u->name = esc_html($user->first_name . ' ' . $user->last_name);
			$u->email = esc_html($user->user_email);
			$u->allowed = (int) $user->get('_extranet_enabled');
			array_push($wpusers, $u);
		}

		return $wpusers;
	}
}