<?php
require_once("../class.Frontend.php");
require_once("functions.php");

session_start();
$frontend = $_SESSION['frontend'];
$frontend->loadLanguageConstants();

/*
*	
*
*
*/


if(isset($_REQUEST['submit']))
{
	$product_id 	= (int)$_REQUEST['product_id'];
	$product 		= new Product($product_id, $frontend->order->getID());
	
	if(isset($_REQUEST['additives']))
	{
		foreach($_REQUEST['additives'] as $a)
		{
			$product->addAdditive(new Additive($a, $product->time));
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'meal')
	{
		$max = $frontend->meal_products[$frontend->actualCourse]['max'];
		
		if(count($frontend->meal_products[$frontend->actualCourse]['products']) < $max)
		{
			$frontend->meal_products[$frontend->actualCourse]['products'][] = $product;
		}
		
		header("location: ../karte.php?action=lunch".$frontend->actualCourse);
	
	} elseif($frontend->order->addProduct($product)) {
		$_SESSION['message'] =  _SUCCESSFULL_ORDER . "<br /> <span class='product'>". $product->displayNameAdditivePrice() ."</span>";
		header("location: ../karte.php");
	} else {
		$_SESSION['message'] =  _FAILED_ORDER;
		header("location: ../karte.php");
	}

} elseif(isset($_GET['remove'], $_GET['action']) && $_GET['remove'] == 1) {
	$product_id 	= (int)$_REQUEST['product_id'];
	
	foreach($frontend->meal_products[$frontend->actualCourse]['products'] as $key=>$p)
	{
		if($p->getID() == $product_id) unset($frontend->meal_products[$frontend->actualCourse]['products'][$key]);
	}
	header("location: ../karte.php?action=lunch".$frontend->actualCourse);
	
} elseif(isset($_GET['meal_order'], $_GET['action']) && $_GET['meal_order'] == 1 && $_GET['action'] == 'meal') {
	$product = new Products($frontend->meal_products_id, $frontend->order->getID());
	$frontend->order->addProduct($product);
	
	foreach($frontend->meal_products as $course)
	{
		foreach($course['products'] as $p) $frontend->order->addProduct($p, $frontend->order->getID(), NULL, true);
	}
	
	$_SESSION['message'] =  _SUCCESSFULL_ORDER . "<br /> <span class='product'>". $product->displayNameAdditivePrice() ."</span>";
	header("location: ../karte.php");
	
} else {
	
		$product_id 	= (int)$_GET['id'];
		$lang			= $frontend->language;
		$name 			= trim($_GET['name']);
		$price 			= (float)$_GET['price'];
		
		$additives = mysql_query("  SELECT id, name, price, tax, pic 
									FROM additives
									JOIN products_has_additives ON products_has_additives.additives_id = additives.id
									WHERE products_has_additives.products_id = '$product_id'
									AND additives.languages_id = '$lang'
									AND additives.published = 1");
		
		echo "<div style='color:#FFF;' class='dialog orders'>";
		
		echo "<h1>".$name."</h1>";
		echo "<div class='price'>&euro; ".$price."</div>";
		
		echo "<form class='wish' action='".$_SERVER['PHP_SELF']."' method='POST'><fieldset>";
		echo 	"<legend>". _WISH ."</legend>";
		echo 	"<input type='hidden' name='product_id' value='".$product_id."' />";
		
		if(isset($_GET['action']) && $_GET['action'] == 'meal') echo "<input type='hidden' name='action' value='".$_GET['action']."' />";
		
		echo "<table>";
		while($additiv = mysql_fetch_object($additives))
		{
			echo "<tr>";
			echo "<td><input id='a".$additiv->id."' class='additiv styled' type='checkbox' name='additives[]' value='".$additiv->id."' onclick='changeTotalAmount(this.id, ".calcBruttoPreis($additiv->price, $additiv->tax).")' /> </td>";
			echo "<td>".utf8_encode($additiv->name)."</td>";
			echo "<td> &euro; ". calcBruttoPreis($additiv->price, $additiv->tax) ."</td>";
			echo "</tr>";
		}
		
		echo "</table></fieldset>";
		
		echo "<h2>" . _PRICE_OF_ORDER . "</h2>";
		echo "<div class='price_total'> &euro; <span id='actuall_price'>".$price."</span> </div>";
		echo "<input class='button' type='submit' name='submit' value='". _ORDER_NOW ."' />";
		
		echo "</form>";
		
		echo "</div>";
		
		echo '
		<script type="text/javascript">
		
		function changeTotalAmount(id, price)
		{
			var check = document.getElementById(id);
			var newPrice = parseFloat(document.getElementById("actuall_price").innerHTML);
			price = parseFloat(price);
			
			if( check.checked == true )
			{
				newPrice = newPrice + price;
			} else {
				newPrice = newPrice - price;
			}
			
			document.getElementById("actuall_price").innerHTML = newPrice;
		}
		</script>
		';
}

?>