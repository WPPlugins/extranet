<?php
// folder permissions 
namespace Extranet\Helper;

use Extranet\ExtranetApp;
use Extranet\Helper\ExtranetFolder;
use Joomla\Registry\Registry;
use Extranet\Helper\ExtranetPermissionRule;


class ExtranetFolderPermissions
{
	protected $folder;
	protected $app;
	protected $location;
	protected $registry;


	public function __construct($folder)
	{
		$this->app = ExtranetApp::getInstance();
		$this->folder = $folder;
		$this->location = ABSPATH . 'wp-content/plugins/extranet/rules/folder/'.$this->folder->SHA1().'.php';
		$this->registry = new Registry();
		$this->load();
	}


	public function registry()
	{
		return $this->registry;
	}


	// returns final permissions for selected folder
	public function extractAggregate($userID = 0)
	{
		// if current folder is root return listing permission
		if ($this->folder->isRoot())
		{
			return new ExtranetPermissionRule('00000001');
		}
		
		// get current folder permissions
		$current = $this->extract($userID);

		// get previous folders, starting from root
		$parents = $this->folder->getParents();
		
		// if no parents, just return current folder permissions
		if (empty($parents))
		{
			return $current;
		}

		// traverse parents and check permissions
		foreach ($parents as $parent)
		{
			$permission = $parent->permissions()->extract($userID);

			// if permission is recursive, keep it recursive
			if ($permission->is_recursive())
			{
				$current = new ExtranetPermissionRule($current->value() | $permission->value());
			}
		}

		return $current;
	}


	// extract user permissions for current folder only
	// use extractAggregate to view user permissions based on parent folder permissions!
	public function extract($userID = 0)
	{	
		$reg = $this->registry->toObject();
	
		if (isset($reg->resource) && $userID && $reg->resource == base64_encode($this->folder->getRelativePath()))
		{	
			foreach ($reg->rules as $rule)
			{
				if(in_array($userID, $rule->users))
				{

					return new ExtranetPermissionRule($rule->permission);
				}
			}
		}
		return new ExtranetPermissionRule();
	}


	public function get($key, $default)
	{
		return $this->registry->get($key, $default);
	}


	public function set($path, $value, $separator = null) 
	{
		$this->registry->set($path, $value, $separator = null);
	}


	public function save() 
	{
		file_put_contents($this->location, $this->appendPHP($this->registry->toString()));
	}


	public function existRuleFile()
	{
		return is_file($this->location);
	}


	public function rulefile()
	{
		return $this->location;
	}


	protected function load()
	{
		if(is_file($this->location))
		{
			$content = $this->stripTags(file_get_contents($this->location));
			$this->registry->loadString($content);
		}
	}

	protected function stripTags($content)
	{
		return str_replace('<?php exit();?>', '', $content);
	}


	protected function appendPHP($content)
	{
		return '<?php exit();?>' . $content;
	}
}

