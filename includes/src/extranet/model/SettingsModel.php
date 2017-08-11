<?php
namespace Extranet\Model;

use Joomla\Model\AbstractModel;
use Joomla\Form\Form;
use Extranet\ExtranetApp;


class SettingsModel extends AbstractModel 
{
	public $app;
	public $form;


	public function __construct($state = null)
	{
		$this->app = ExtranetApp::getInstance();
		$this->form = new Form('settings');
		$this->form->loadFile($this->app->xml);
		$this->loadData();

		parent::__construct($state);
	}


	protected function loadData()
	{
		$meta = get_post_meta($this->app->id, '_extranet_config', true);
		
		if ($meta)
		{
			$values = json_decode($meta, true);
			$settings = array();

			foreach ($values as $key => $value)
			{
				$settings['settings['.$key.']'] = $value;
			}
		}

		if (empty($settings['settings[default_storage]']))
		{
			$settings['settings[default_storage]'] = $this->app->getStorage();
		}
		
		$this->form->bind($settings);
	}


	public function save($data)
	{
		update_post_meta($this->app->id, '_extranet_config', json_encode($data));
	}
}