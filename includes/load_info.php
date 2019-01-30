<?php
require_once("../class.Frontend.php");
require_once("functions.php");

session_start();
$frontend = $_SESSION['frontend'];
$frontend->loadLanguageConstants();

echo "<div style='color:#FFF;' class='dialog more_info'>";

$id 	= (int)$_GET['id'];
$lang	= $frontend->language;

$product = selectDB("*", "products", "id = '$id'");
$product = mysql_fetch_object($product);

$ingredients = mysql_query("SELECT id, name
							FROM ingredients
							JOIN products_has_ingredients ON ingredients.id = products_has_ingredients.ingredients_id
							WHERE products_has_ingredients.products_id = $product->id
							AND languages_id = '$frontend->language' 
							AND ingredients.published = 1");

echo "<h1>".utf8_encode($product->name)."</h1>";
echo "<div class='price'>&euro; ". number_format(calcBruttoPreis($product->price, $product->tax), 2) . "</div>";
echo "<div class='image'><img src='speisen/".$product->pic_more_info."' alt='speisen/".$product->pic_more_info."' /></div>" ;
echo "<div class='ingredients'><h4>". _INGREDIENTS .":</h4>";

$ingredients_text = "";
while($ingredient = mysql_fetch_object($ingredients))
{
	$ingredients_text .= $ingredient->name.", ";
}
$ingredients_text = substr($ingredients_text,0,-2);
echo utf8_encode($ingredients_text);
echo "</div>";
echo "<div class='allergy-hint'>".utf8_encode(stripslashes($product->allergy_hint))." </div>";
?>
<div class="bestellen lightbox button" href="includes/load_order.php?id=<?= $product->id; ?>&name=<?= utf8_encode($product->name); ?>&price=<?= calcBruttoPreis($product->price, $product->tax); ?>&lightbox[width]=500&lightbox[height]=600"><?= _ORDER; ?></div>

<?php
 
echo "</div>";

?>