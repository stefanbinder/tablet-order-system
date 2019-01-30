<?php
require_once("../class.Frontend.php");
require_once("functions.php");

session_start();

$frontend = $_SESSION['frontend'];
$frontend->loadLanguageConstants();

if(isset($_POST['submit']))
{
	$frontend->order->foodfilter = array();
	
	if(isset($_POST['ingredients']))
	{
		foreach($_POST['ingredients'] as $ingredient)
		{
			$frontend->order->foodfilter[] = $ingredient;
		}
	}
	header("location: ../karte.php");
	
} else {

	echo "<div style='color:#FFF;'>";
	echo "<h1>Foodfilter</h1>";
	echo "<p class='info'>"._FOODFILTER_INFO."</p>";
	echo "<form action='".$_SERVER['PHP_SELF']."' method='POST'>";
	echo "<fieldset class='bottom_border'><legend>"._FOODFILTER_CHOOSE."</legend>";
	$ingredients = mysql_query("SELECT * FROM ingredients WHERE languages_id = '$frontend->language' AND foodfilter = '1'");
	//echo "<table>";
	while($i = mysql_fetch_object($ingredients))
	{
		echo "<label class='checkbox_big'>";
		
		if($frontend->order->isIngredientInFoodfilter($i->id))
		{
			echo "<input id='i".$i->id."' type='checkbox' name='ingredients[]' value='$i->id' checked /></label><label for='i".$i->id."'>".utf8_encode($i->name)."</label> <br />";
		} else {
			echo "<input id='i".$i->id."' type='checkbox' name='ingredients[]' value='$i->id' /></label><label for='i".$i->id."'>".utf8_encode($i->name)."</label> <br />";
		}
		
		echo "";
	}
	//echo "</table>";
	echo "</fieldset>";
	echo "<input class='button' type='submit' name='submit' value='Speisen markieren' />";
	echo "</form>";
	echo "<p class='liability'>"._FOODFILTER_LIABILITY."</p>";
	
	echo "</div>";

}
?>