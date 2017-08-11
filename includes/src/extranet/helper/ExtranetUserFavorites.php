<?php
namespace Extranet\Helper;

use Extranet\Helper\ExtranetUser;
use Extranet\ExtranetApp;

class ExtranetUserFavorites
{

	protected $user;
	protected $reg;


	public function __construct($user, $meta = '')
	{
		$this->user = $user;
		$this->reg 	= array();

		if (!empty($meta))
		{
			$this->loadString($meta);
		}
	}


	public function update($item)
	{
		if (empty($this->reg))
		{
			array_push($this->reg, $item);
		}
		else
		{
			$found = false;
			foreach ($this->reg as $i)
			{
				if ($i->path == $item->path)
				{
					$found = true;
				}
			}

			if (!$found)
			{
				array_push($this->reg, $item);
			}
		}

		$this->user->setMeta('_extranet_user_favorites', $this->toString());
	}


	public function remove($item)
	{
		$filtered = array();

		if (!empty($this->reg))
		{
			foreach ($this->reg as $fav)
			{
				if ($fav->path != $item->path && $this->exists($fav->path))
				{
					array_push($filtered, $fav);
				}
			}
		}

		$this->reg = $filtered;
		$this->user->setMeta('_extranet_user_favorites', $this->toString());
	}


	public function toArray()
	{
		sort($this->reg);
		return $this->reg;
	}


	protected function toString()
	{
		sort($this->reg);
		return serialize($this->reg);
	}


	protected function loadString($meta)
	{
		$this->reg = unserialize($meta);
	}


	protected function exists($b64path)
	{
		$path = base64_decode($b64path);
		$full = ExtranetApp::getInstance()->getStorage() . $path;

		if (!file_exists($full))
		{
			return false;
		}

		return true;
	}
}