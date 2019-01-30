<?php
require_once("functions.php");

if(isset($_POST['kategorie']))
{
	include("../class.Frontend.php");
	session_start();
	$frontend = $_SESSION['frontend'];
	$frontend->loadLanguageConstants();

	$kat 	= (int)$_POST['kategorie'];
	$_SESSION['actual_products'] = $kat;
} else {
	$kat 	= $_SESSION['actual_products'];
	$frontend = $_SESSION['frontend'];
}

$lang	= $frontend->language;

$products = mysql_query("SELECT * FROM products WHERE categories_id = '$kat' AND languages_id = '$lang' AND published = '1' AND (date_format(NOW(),'%H:%i:%s') BETWEEN start_time AND end_time OR (NOT date_format(NOW(),'%H:%i:%s') BETWEEN end_time AND start_time AND start_time > end_time)) ORDER BY ordering ASC");


if(mysql_num_rows($products) != 0)	// Means, that there are no Separators between the products
{
	while($product = mysql_fetch_object($products))
	{	
		showProduct($product, $frontend);
	}
	
} else {
	$sub_categories = mysql_query("SELECT * FROM categories WHERE parent = '$kat' ORDER BY ordering ASC");
	while($sub_cat = mysql_fetch_object($sub_categories))
	{
		$products = mysql_query("SELECT * FROM products WHERE categories_id = '$sub_cat->id' AND published = '1' AND (date_format(NOW(),'%H:%i:%s') BETWEEN start_time AND end_time OR (NOT date_format(NOW(),'%H:%i:%s') BETWEEN end_time AND start_time AND start_time > end_time)) ORDER BY ordering ASC");
		
		echo "<fieldset class='products_separator'><legend><img src='kategorien/icon/".$sub_cat->name.".png' alt='kategorien/icon/".$sub_cat->name.".png fehlt!' />".$sub_cat->name."</legend>";
		while($product = mysql_fetch_object($products))
		{
			showProduct($product, $frontend);
		}
		echo "</fieldset>";
		
	}
}

function showProduct($product, $frontend)
{
	$scharf = "";
	$vegetarisch = "";
	$achtung = "";
	
	if($product->spicy == "1") $scharf = "<img src='images/scharf.png' />";
	if($product->vegetarian == "1") $vegetarisch = "<img src='images/vegetarisch.png' />";
	
	
	$ingredients = mysql_query("SELECT ingredients_id FROM products_has_ingredients WHERE products_id = '$product->id'");
	while($i = mysql_fetch_object($ingredients))
	{
		if($frontend->order->isIngredientInFoodfilter($i->ingredients_id)) $achtung = "<img src='images/warning.png' />";
	}
	
	echo "<div class='speise_style speise' id='".$product->id."'>\n";
	echo "<h3>".utf8_encode($product->name)."&nbsp;&nbsp;".$vegetarisch.$scharf.$achtung."<div class='speise_preis'>&euro; ".number_format(calcBruttoPreis($product->price, $product->tax), 2)."</div><div class='clear'></div></h3>\n";
	echo utf8_encode($product->subname)."\n";
	echo "</div>";
		
}

?>