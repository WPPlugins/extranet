<?php
namespace Extranet;

use Joomla\Application\AbstractApplication;
use Joomla\Registry\Registry;
use Joomla\Form\Form;
use Joomla\Uri\Uri;
use Extranet\Helper\ExtranetEvent;


class ExtranetApp extends AbstractApplication 
{
	protected static $instance;
	protected $observers;
	public $event;
	public $id;
	public $xml;


	public function __construct(Input $input = null, Registry $config = null)
	{
		$this->xml		= ABSPATH . 'wp-content/plugins/extranet/config.xml';
		$this->id 		= $this->getExtranetId();

		parent::__construct($input, $this->loadConfig());
	}


	public static function getInstance(Input $input = null, Registry $config = null)
	{
		if(!isset(self::$instance))
		{
			self::$instance = new ExtranetApp($input, $config);
		}

		return self::$instance;
	}


	public function loadConfig()
	{
		if(!file_exists($this->xml))
		{
			throw new \Exception("Could not find configuration file", 1);
		}

		$config = new Registry();
		$stored = get_post_meta($this->id, '_extranet_config', true);
		
		if (!empty($stored))
		{
			$config->loadString($stored);
		}

		$form = new Form('app');
		$form->loadFile($this->xml);

		foreach ($form->getFieldsets() as $field)
		{
			foreach ($form->getFieldset($field->name) as $f)
			{
				if (!$config->exists($f->id))
				{
					$default = $form->getFieldAttribute($f->name, 'default');
					$config->set($f->id, $default);
				}
			}
		}
		
		return $config;
	}


	// return the full storage path
	public function getStorage() 
	{
		$option = $this->get('default_storage');

		if (empty($option))
		{
			return ABSPATH . 'wp-content/uploads/extranet-plugin/' . sha1(NONCE_KEY); 
		}
		return $option;
	}
	

	public function getUsers($options = array())
	{
		if (empty($options))
		{
			$options = array(
				'blog_id' 	=> $GLOBALS['blog_id'],
				'orderby'   => 'ID',
				'order'     => 'ASC',
			);
		}
		return get_users($options);
	}
	

	public function redirect($url, $status = 303)
	{
		wp_redirect($url, $status);
		exit();
	}


	public function url($queries = array(), $url = '')
	{
		if (!$url) 
		{
			$url = new Uri(get_permalink($this->id));
		}
	
		if (!empty($queries))
		{
			foreach ($queries as $key => $value)
			{
				$url->setVar($key, $value);
			}
		}
		return $url->toString();
	}


	public function attach($observer)
	{
		$this->observers[] = $observer;
	}	


	public function dettach($o)
	{
		foreach ($this->observers as $key => $observer)
		{
			if ($observer == $o)
			{
				unset($this->observers[$key]);
			}
		}
	}


	public function trigger(ExtranetEvent $event)
	{
		$this->event = $event;

		foreach ($this->observers as $observer)
		{
			$observer->trigger($this);
		}
	}


	public function getPluginData()
	{
		return get_plugin_data(ABSPATH . 'wp-content/plugins/extranet/extranet.php');
	}

	

	protected function getExtranetId()
	{
		return get_option('_extranet_page', 0);
	}


	protected function doExecute() {}
}