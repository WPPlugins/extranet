<?php
// admin 
use Joomla\Input\Input;
use Extranet\View\UsersView;

$input 	= new Input();
$view 	= new UsersView();

$view->setLayout($input->get('layout','default','cmd'));
echo $view->render();
?>
