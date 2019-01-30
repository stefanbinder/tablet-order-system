<?php

/**
  *		HC-Media
  *		Copyright 2012
  *		@package ordersystem
  */


/*
* Starting including class.*.php files and authorize the user
*/ 


require_once('authorize.inc.php');


//echo $_SESSION[$_sessionKey]['be'] ;
//echo $screen->createMenu();

if(empty($_GET['action'])) {
	$action = 'login';
} else {
	$action = $_GET['action'];
	unset($_GET['action']);
}

echo $backend->handleAction($action, $_GET);

?>