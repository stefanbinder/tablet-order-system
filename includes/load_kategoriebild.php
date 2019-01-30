<?php
require_once("functions.php");

if(isset($_POST['kategorie']))
{	
	include("../class.Frontend.php");
	session_start();
	$frontend = $_SESSION['frontend'];
	$frontend->loadLanguageConstants();

	$kat 	= (int)$_POST['kategorie'];
} else {
	$kat 	= $_SESSION['actual_products'];
}

$lang	= $frontend->language;

$cat = selectDB("pic1, pic2, pic3", "categories", "languages_id = '$lang' AND id = '$kat' AND published = '1'", "ordering ASC");
$cat = mysql_fetch_object($cat);
$photo1 = trim($cat->pic1);
$photo2 = trim($cat->pic2);
$photo3 = trim($cat->pic3);

if($photo1 != "" || $photo2 != "" || $photo3 != "")
{
	//echo "<div id='image_fade'>";
	
	//if($photo1 != "") echo "<div class='slide'><img src='kategorien/".$photo1."' /></div>";
	if($photo1 != "") echo "<img src='kategorien/".$photo1."' />";
	/**if($photo2 != "") echo "<div class='slide'><img src='kategorien/".$photo2."' /></div>";
	if($photo3 != "") echo "<div class='slide'><img src='kategorien/".$photo3."' /></div>";**/
	
	//echo "</div>";
	/*
	echo "<script language='javascript'>$('#image #image_fade').innerfade({ speed: 2000, timeout: 4000, type: 'sequence', containerheight: '528px' });</script>";
	*/
}

?>