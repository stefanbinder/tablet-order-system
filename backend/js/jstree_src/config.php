<?php
require_once("../../../includes/conn_info.php");

// Database config & class
$db_config = array(
	"servername"=> $host,
	"username"	=> $user,
	"password"	=> $pw,
	"database"	=> $database
);
if(extension_loaded("mysqli")) require_once("_inc/class._database_i.php"); 
else require_once("_inc/class._database.php");

// Tree class
require_once("_inc/class.tree.php"); 
?>