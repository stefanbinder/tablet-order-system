<?php
require_once("conn_info.php");
mysql_connect($host, $user, $pw) or die("MySQL-Verbindung konnte nicht hergestellt werden.");
mysql_select_db($database) or die("Fehler bei Datenbankverbindung");
?>