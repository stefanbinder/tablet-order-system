<?php
require_once("functions.php");

if(isset($_POST['kategorie']))
{	
	include("../class.Frontend.php");
	session_start();
	$frontend = $_SESSION['frontend'];
	$frontend->loadLanguageConstants();

	$kat 	= (int)$_POST['kategorie'];
	$_SESSION['actual_sub_categorie'] = $kat;
} else {
	$kat 	= $_SESSION['actual_sub_categorie'];
}

$lang	= $frontend->language;

echo "<ul>";

$speisen = selectDB("id, name, action", "categories", "languages_id = '$lang' AND parent = '$kat' AND published = '1' AND (date_format(NOW(),'%H:%i:%s') BETWEEN start_time AND end_time OR (NOT date_format(NOW(),'%H:%i:%s') BETWEEN end_time AND start_time AND start_time > end_time))", "ordering ASC");
while($speise = mysql_fetch_object($speisen))
{
	$id 	= utf8_encode($speise->id);
	$name	= utf8_encode($speise->name);
	
	/*if(isset($action) && $action != '') echo "<li class='foodmenu_sub_button_style' id='".$id."'><a href='karte.php?action=".$action."'>".$name."</a></li>\n"; */
	(isset($_SESSION['actual_products']) && $_SESSION['actual_products'] == $id) ? $active = ' active' : $active = '';
	echo "<li class='foodmenu_sub_button_style foodmenu_sub_button $active' id='".$id."'>".$name."</li>\n";
}
echo "</ul>";
?>