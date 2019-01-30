<?php
require_once("../class.Frontend.php");
require_once("functions.php");

session_start();
$frontend = $_SESSION['frontend'];
$frontend->loadLanguageConstants();

$id 	= (int)$_POST['id'];
$lang	= $frontend->language;

$meal = "";
if(isset($_POST['action']) && $_POST['action'] != '') $meal = $_POST['action'];


$speise = selectDB("*", "products", "id = '$id' AND languages_id = '$lang'"); //  AND published = '1'   -> remove, because if the product unpublished, it will never displayed on the left side.
$speise = mysql_fetch_object($speise);

$photo = trim($speise->pic); // Just one photo / product

if($photo != "" && $meal == "")
{
	echo "<img src='speisen/".$photo."' />";
}

/*									SLIDER FOR MORE PHOTOS
$foto2 = trim($speise->foto2);
$foto3 = trim($speise->foto3);

if($foto1 != "" || $foto2 != "" || $foto3 != "")
{
	$image_fader = "<div id='image_fade'>";
	if($foto1 != "") $image_fader .= "<div class='slide'><img src='speisen/".$foto1."' /></div>";
	if($foto2 != "") $image_fader .= "<div class='slide'><img src='speisen/".$foto2."' /></div>";
	if($foto3 != "") $image_fader .= "<div class='slide'><img src='speisen/".$foto3."' /></div>";
	$image_fader .= "</div>";
	echo $image_fader;
	?>
	<script language='javascript'>$("#image #image_fade").innerfade({ speed: 2000, timeout: 4000, type: 'sequence', containerheight: '528px' });</script>
    <?php
}
*/

?>

<div class="mehr_infos lightbox" id="<?= $speise->id; ?>" href="includes/load_info.php?id=<?= $speise->id; ?>&lightbox[width]=500&lightbox[height]=600"><?= _MORE_INFO; ?></div>


<?php

if(isset($meal) && $meal == 'meal')
{
	$next_course = $frontend->actualCourse+1;
	
	echo '<div class="bestellen lightbox" id="'.$speise->id.'" href="includes/load_order.php?id='.$speise->id.'&name='.utf8_encode($speise->name).'&action='.$meal.'&next_course='.$next_course.'&price='.calcBruttoPreis($speise->price, $speise->tax).'&lightbox[width]=500&lightbox[height]=500">'. _MEAL_WISH .'</div>';
	
	if($frontend->actualCourse == count($frontend->meal)-1)
	{
		echo '<a href="includes/load_order.php?product_id='.$speise->id.'&action='.$meal.'&submit=1&next_course='.$next_course.'"><div class="meal_further_button">'. _MEAL_ORDER_WITH .'</div></a>';
	} else {
		echo '<a href="includes/load_order.php?product_id='.$speise->id.'&action='.$meal.'&submit=1&next_course='.$next_course.'"><div class="meal_further_button">'. _MEAL_FURTHER_COURSE .'</div></a>';
	}
	
	/* OLD further link:
	echo '<a href="karte.php?action=lunch'.($frontend->actualCourse + 1).'"><div class="meal_further_button">'. _MEAL_FURTHER_COURSE .'</div></a>';
	*/
	
	
	/*
	if(isset($_POST['inMenu']) && $_POST['inMenu'] == 1)
	{
		$half = "";
		if(!$frontend->mealFull()) 
		{
			$half = "half ";
			echo '<a href="includes/load_order.php?product_id='.$speise->id.'&action='.$meal.'&submit=1"><div class="meal_add_button '.$half.' add_half">'. _MEAL_ADD .'</div></a>';
		}
		echo '<a href="includes/load_order.php?product_id='.$speise->id.'&action='.$meal.'&remove=1"><div class="meal_add_button remove '.$half.'"> '. _MEAL_REMOVE .'</div>';
	} else {
		if(!$frontend->mealFull()) echo '<a href="includes/load_order.php?product_id='.$speise->id.'&action='.$meal.'&submit=1"><div class="meal_add_button">'. _MEAL_ADD .'</div></a>';
	}
	*/
	
} else {
	
	echo "<div class=\"bestellen lightbox\" id=\"$speise->id\" href=\"includes/load_order.php?id=".$speise->id."&name=".utf8_encode($speise->name)."&price=".calcBruttoPreis($speise->price, $speise->tax)."&lightbox[width]=500&lightbox[height]=500\">". _ORDER ."</div>";
	
	/*
	##
	##	Überspringt die Bestätigungslightbox falls keine Additives für ein Produkt vorhanden sind.
	##
	
	$adds = mysql_query("SELECT * FROM products_has_additives WHERE products_id = '".$speise->id."'");
	
	if(mysql_num_rows($adds) == 0)
	{
		
		echo "<a href=\"includes/load_order.php?product_id=".$speise->id."&submit=1\"><div class=\"bestellen\" id=\"$speise->id\">". _ORDER ."</div></a>";
		
	} else {
	}
	*/
	
		

}
?>

<p id="mehr_infos" style="display:none; width:80%; padding:10px; margin-right:auto; margin-left:auto;"></p>