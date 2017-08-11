<?php
// admin 
use Joomla\Input\Input;
use Extranet\View\SettingsView;

$input 	= new Input();
$view 	= new SettingsView();

$view->setLayout($input->get('layout','default','cmd'));
echo $view->render();
?>
