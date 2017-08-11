<?php
// generate front-end menu
namespace Extranet\Helper;

use Joomla\Uri\Uri;
use Extranet\ExtranetApp;

class ExtranetMenu {

	protected $selected;
	protected $app;

	public function __construct($selected)
	{
		$this->selected = $selected;
		$this->app = ExtranetApp::getInstance();
	}

	public function render()
	{
		$html = '<div class="pure-menu pure-menu-horizontal extranetmenu">';
		$html .= '<ul class="pure-menu-list">';
		$html .= '<li class="pure-menu-item '.($this->selected == 'dashboard' ? 'pure-menu-selected' : '').'"><a href="'.$this->buildURL('dashboard').'" class="pure-menu-link"> '. __('Home', 'extranet') .'</a></li>';
		$html .= '<li class="pure-menu-item '.($this->selected == 'folders' ? 'pure-menu-selected' : '').'"><a href="'.$this->buildURL('folders').'" class="pure-menu-link">'. __('Folders', 'extranet') .'</a></li>';
		$html .= '<li class="pure-menu-item '.($this->selected == 'favorites' ? 'pure-menu-selected' : '').'"><a href="'.$this->buildURL('favorites').'" class="pure-menu-link">'. __('Favorites', 'extranet') .'</a></li>';
		$html .= '<li class="pure-menu-item"><a href="'.$this->app->url(array('task' => 'extranet.logout', 'nonce'=>wp_create_nonce('extranet.login'))).'" class="pure-menu-link">'. __('Logout', 'extranet') .'</a></li>';
		$html .= '</ul>';
		$html .= '</div>';

		return $html;
	}


	protected function buildURL($view)
	{
		$url = new Uri(get_permalink());
		
		if ($view)
		{
			$url->setVar('view', $view);
		}
		return $url->toString();
	}
}