<?php
// class for Extranet user 
namespace Extranet\Helper;

use Extranet\Helper\ExtranetUserFavorites;

class ExtranetUser extends \WP_User
{

  public static $instance;

	public function setMeta($key, $value)
	{
		update_user_meta($this->ID, $key, $value);
	}

  public function getMeta($key, $single)
  {
    return get_user_meta($this->ID, $key, $single);
  }

  public static function getInstance($id = 0)
  {
    $wp = wp_get_current_user();

    if ($wp->id && $id == 0)
    {
      $id = $wp->id;
    }

    if(!isset($instance[$id]))
    {
      $instance[$id] = new self($id);
    }
    return $instance[$id];
  }

  public function enabled()
  {
    return (bool) get_user_meta($this->ID, '_extranet_enabled', true);
  }

  public function homepage()
  {
    return esc_html(get_user_meta($this->ID, '_extranet_user_homepage', true));
  }

  public function favorites()
  {
    $meta = $this->getMeta('_extranet_user_favorites', true);
    return new ExtranetUserFavorites($this, $meta);
  }
}