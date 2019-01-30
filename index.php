<?php
header("Content-Type: text/html; charset=utf-8");

require_once("class.Frontend.php");
session_start();

if(isset($_SESSION['frontend']))
{
	$frontend = $_SESSION['frontend'];
} else {
	$frontend = new Frontend();
}

if(isset($_GET['action'])) 
{
	$action = $_GET['action'];
} else {
	if(isset($_SESSION['frontend']) && $_SESSION['frontend']->isLoggedIn())
	{
		$action = 'karte';
	} else {
		$action = 'login';
	}
}

switch ($action) 
{
	case 'logout':
		$frontend = NULL;
		unset($_GET);
		unset($_POST);
		session_destroy();
		header("location: login.php");
		break;
		
	case 'language':
		// login.php sends the language request - init the table, place and mastercode
		if(isset($_POST['submit'])) 
		{
			(isset($_POST['relogin'])) ? $relogin = 1 : $relogin = 0;
			
			$frontend->setOrder($_POST['tisch_nr'], $_POST['platz_nr'], md5($_POST['mastercode']), $relogin);
			
			$_SESSION['frontend'] = $frontend;
		}
		
		break;
		
	case 'karte':
		if(isset($_GET['lang']) && $_GET['lang'] == 'en') $frontend->language = 2;   // The default value of language is de -> 1
		$_SESSION['frontend'] = $frontend;
		header("location: karte.php");
		break;
		
	case 'error':
		header("location: error.php?error=".$_REQUEST['message']);
		break;
		
	default:		// default zugleich mit action login
		$_SESSION['frontend'] = $frontend;
		header("location: login.php");
		break;
	
}
?>