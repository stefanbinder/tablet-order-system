<?php
require_once("../class.Frontend.php");
require_once("functions.php");

session_start();
$frontend = $_SESSION['frontend'];
$frontend->loadLanguageConstants();

/*
*	load.order.php
*	responsible for additives and adding products into the order-object
*	
*/


if(isset($_REQUEST['submit']))
{
	/*
	*	This IF will prevent double or more orders of a customer
	*/
	if(isset($_SESSION['lastOrderTime']) && $_SESSION['lastOrderTime'] > time()-4) 
	{
		header("location: ../karte.php");
	} else {
		$_SESSION['lastOrderTime'] = time();
		
		$product_id 	= (int)$_REQUEST['product_id'];
		$product 		= new Product($product_id, $frontend->order->getID());
		
		/*
		*	If the user chooses some additives in the frontend, now would it be added to the product
		*/
		if(isset($_REQUEST['additives']))
		{
			foreach($_REQUEST['additives'] as $a)
			{
				$product->addAdditive(new Additive($a, $product->time));
			}
		}
		
		/*
		*	Now the product will be added to the order-object
		*/
		if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'meal')
		{
			/*
			*	If the user is in meal (like lunch, breakfast, ...), and add a product to the meal / frontend-object (this will be NOT in the database)
			*/
			
			$product->setInMeal($frontend->actualCourse+1);
			$frontend->meal_products[$frontend->actualCourse] = $product;	
			
			if($_REQUEST['next_course'] == 4)
			{
				header("location: load_order.php?meal_order=1");
			} else {
				header("location: ../karte.php?action=lunch".$_REQUEST['next_course']);
			}
			
		} elseif($frontend->order->addProduct($product)) {
			/*
			*	This case will add normally a product to the order-object (automatically the object is saved in the database)
			*/
			
			$_SESSION['message'] =  _SUCCESSFULL_ORDER . "<br /> <span class='product'>". $product->displayNameAdditivePrice() ."</span>";
			header("location: ../karte.php");
		} else {
			$_SESSION['message'] =  _FAILED_ORDER;
			header("location: ../karte.php");
		}
	}

} elseif(isset($_GET['remove'], $_GET['action']) && $_GET['remove'] == 1) {
	/*
	*	The user is clicking on the "remove" button during the meal-actions
	*	So the product will be deleted from frontend-object -> NOT the order-object
	*/
	
	$product_id 	= (int)$_REQUEST['product_id'];
	
	foreach($frontend->meal_products[$frontend->actualCourse]['products'] as $key=>$p)
	{
		if($p->getID() == $product_id) unset($frontend->meal_products[$frontend->actualCourse]['products'][$key]);
	}
	header("location: ../karte.php?action=lunch".$frontend->actualCourse);
	
	
} elseif(isset($_GET['meal_order']) && $_GET['meal_order'] == 1) {
	/*
	*	The user is clicking on the "order meal" button during the meal-actions
	*	The stored products in frontend-object will be added to the order-object and order-object write this products in the database with a inMeal-flag
	* 	The user wont see meal-products in his overview (just the meal-product like: Mittagessen um 9.50
	*
	*	product & product extra is depending on user's choose on the last point -> course 3 is optional with extra charges
	*
	*	Products will stored with different times - each further course will added x minutes
	*/
	
	if(count($frontend->meal_products) < 3)
	{
		$_SESSION['message'] =  _MEAL_EMPTY;
		header("location: ../karte.php");
		
	} else {
		
		if(isset($frontend->meal_products_id_extra) && count($frontend->meal_products) > 3)
		{
			$product = new Product($frontend->meal_products_id_extra, $frontend->order->getID());
		} else {
			$product = new Product($frontend->meal_products_id, $frontend->order->getID());
		}
		
		$i = 0;
		$success_message = "";
		foreach($frontend->meal_products as $p)
		{
			$p->setTime(date('Y-m-d H:i:s',time()+($i*60* _MEAL_TIME_BETWEEN_COURSE )));
			$frontend->order->addProduct($p);
			$i++;
			$success_message .= $p->getName()."<br />";
		}
		
		$product->meal_products = $frontend->meal_products;
		
		$frontend->order->addProduct($product);
		
		unset($frontend->meal_products);
		
		$_SESSION['message'] =  _SUCCESSFULL_ORDER . "<br /> <span class='product'>". $product->displayNameAdditivePrice() ."</span><br />".$success_message;
		header("location: ../karte.php");
	}
} else {
		/*
		*	Followed is the normal output of Zusätze/Bestellen 
		*	There will be printed the possibility to choose Additives and to order the product
		*/
	
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
		echo "<div class='price'>&euro; ".number_format($price, 2)."</div>";
		
		echo "<form class='wish' action='".$_SERVER['PHP_SELF']."' method='POST'><fieldset>";
		echo 	"<legend>". _WISH ."</legend>";
		echo 	"<input type='hidden' name='product_id' value='".$product_id."' />";
		
		if(isset($_GET['action']) && $_GET['action'] == 'meal') 
		{
			echo "<input type='hidden' name='action' value='".$_GET['action']."' />";
			echo "<input type='hidden' name='next_course' value='".$_GET['next_course']."' />";
		}
		
		echo "<table>";
		while($additiv = mysql_fetch_object($additives))
		{
			echo "<tr>";
			echo "<td><label class='checkbox_big'>";
			echo "<input id='a".$additiv->id."' class='additiv styled' type='checkbox' name='additives[]' value='".$additiv->id."' onclick='changeTotalAmount(this.id, ".calcBruttoPreis($additiv->price, $additiv->tax).")' />"; 
			echo  "</label></td>";
			echo "<td><label for='a".$additiv->id."'>".utf8_encode(stripslashes($additiv->name))."</label></td>";
			echo "<td> &euro; ".number_format(calcBruttoPreis($additiv->price, $additiv->tax), 2) ."</td>";
			echo "</tr>";
		}
		
		echo "</table></fieldset>";
		
		echo "<h2>" . _PRICE_OF_ORDER . "</h2>";
		echo "<div class='price_total'> &euro; <span id='actuall_price'>".number_format($price, 2)."</span> </div>";
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
				newPrice = Math.round((newPrice + price) * 100) / 100 ;
			} else {
				newPrice = Math.round((newPrice - price) * 100) / 100;
			}
			document.getElementById("actuall_price").innerHTML = newPrice.toFixed(2);
		}
		</script>
		';
		
		
}

?>