<?php

$lang	= $frontend->language;

$kat = 30;



if($frontend->meal_products_id == null || $frontend->meal_products_id == '') 
{
	echo "<script type='text/javascript'>window.location.href='karte.php?message=". _MEAL_NOT_AVAILABLE."'</script>";	
}

$sql = "SELECT * FROM products WHERE id IN (".implode(",",$frontend->meal[$frontend->actualCourse]).")";

$course = mysql_query($sql);

echo '<div id="show_lunch_products">
		<div id="foodwrapper">
			<div id="food"> ';

while($speise = mysql_fetch_object($course))
{
	$scharf = "";
	$vegetarisch = "";
	$achtung = "";
	$inMenu = 0;
	
	if($speise->spicy == "1") $scharf = "<img src='images/scharf.png' />";
	if($speise->vegetarian == "1") $vegetarisch = "<img src='images/vegetarisch.png' />";

	$ingredients = mysql_query("SELECT ingredients_id FROM products_has_ingredients WHERE products_id = '$speise->id'");
	while($i = mysql_fetch_object($ingredients))
	{
		if($frontend->order->isIngredientInFoodfilter($i->ingredients_id)) $achtung = "<img src='images/warning.png' />";
	}
	
	
	if(!empty($frontend->meal_products[$frontend->actualCourse]))
	{
		
		if(!empty($frontend->meal_products[$frontend->actualCourse]) && $speise->id == $frontend->meal_products[$frontend->actualCourse]->getID()) 
		{
			$achtung .= "<img src='images/haken.png' />"; 
			$inMenu = 1;
		}
	}
	
	echo "<div class='speise_style speise_meal' id='".$speise->id."' action='meal' inMenu='".$inMenu."' actualCourse='".$frontend->actualCourse."' >\n";
	echo "<h3>".utf8_encode($speise->name)."&nbsp;&nbsp;".$vegetarisch.$scharf.$achtung."</h3>\n";
	
	// PRICE: <div class='speise_preis'>&euro; ".calcBruttoPreis($speise->price, $speise->tax)."</div>
	
	echo utf8_encode($speise->subname)."\n";
	echo "</div>";
}

echo '		</div>
		</div>
		<div id="image">';

echo '		<img src="images/meal_course'.$frontend->actualCourse.'_lang'.$frontend->language.'.jpg" />';

if($frontend->actualCourse == count($frontend->meal)-1) echo '<a href="includes/load_order.php?meal_order=1"><div class="meal_further_button meal_order">'._MEAL_ORDER_WITHOUT.'</div></a>';



echo '		</div>
		<div class="clear"></div>
	 </div> ';
	 
	 
?>