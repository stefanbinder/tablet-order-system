<?php
require_once('class.Backend.php');
session_start();

$_sessionKey = md5(__FILE__);

if(isset($_SESSION[$_sessionKey]["backend"])) $backend = $_SESSION[$_sessionKey]["backend"];
else $backend = new Backend();

/* check for logout attempt */
if (!empty($_REQUEST["logout"]) && $_REQUEST["logout"]) {
        unset($backend, $_REQUEST);
		session_destroy();
		header("location: index.php");
}

if (isset($_REQUEST["username"], $_REQUEST["password"])) {
	
	switch($backend->validLogin($_REQUEST["username"], $_REQUEST["password"]))
	{
        case 'bar':
			$backend = new BarScreen($_REQUEST["username"], $_REQUEST["password"]);
			break;
		case 'kitchen':
			$backend = new KitchenScreen($_REQUEST["username"], $_REQUEST["password"]);
			break;
		case 'waiter':
			$backend = new WaiterScreen($_REQUEST["username"], $_REQUEST["password"]);
			break;
		case 'admin':
			$backend = new AdminScreen($_REQUEST["username"], $_REQUEST["password"]);
			break;
		default:
			$backend->printError("Invalid username or password");
			break;
	}
	$_SESSION[$_sessionKey]["backend"] = $backend;
}

/* check for logged in status */
if (!$backend->isLoggedIn()) {
        /* not logged in */
        $backend->printMessage(Backend::createContent(Backend::createLogin()));
        exit();
}

/* logged in, fall through to rest of site */

?>