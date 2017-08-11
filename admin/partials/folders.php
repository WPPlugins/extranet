<?php
// admin 
use Joomla\Input\Input;
use Extranet\View\FoldersView;

$input 	= new Input();
$view 	= new FoldersView();

$view->setLayout($input->get('layout','default','cmd'));
echo $view->render();
?>
