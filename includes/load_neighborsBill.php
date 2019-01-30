<?php
require_once("../class.Frontend.php");
require_once("functions.php");

session_start();

$frontend = $_SESSION['frontend'];
$frontend->loadLanguageConstants();

if(isset($_GET['transferOrder']) && $_GET['transferOrder'] != '')
{
	$transferedOrder = $frontend->neighborOrders[$_GET['transferOrder']];	
	
	if($transferedOrder->transfer($frontend->order->getID()))
	{
		$_SESSION['message'] = _TRANSFER_MESSAGE;
		header("location: ../karte.php?action=payment");
	} else {
		$_SESSION['message'] = "An error occurs, please try it again!";
		header("location: ../karte.php?action=payment");
	}
	
} elseif(isset($_GET['transferAll']) && $_GET['transferAll'] == '1') {
	
	foreach($frontend->neighborOrders as $o)
	{
		$o->transfer($frontend->order->getID());
	}
	$_SESSION['message'] = _TRANSFER_MESSAGE;
	header("location: ../karte.php?action=payment");
	
} else {

	$table_id = (int) $_GET['table_id'];
	
	$orders = mysql_query(" SELECT * FROM orders
							WHERE seat_id IN (SELECT id FROM seat WHERE table_id = '$table_id')
							AND pay_intention = '0' AND paid = '0' ");
	
	echo "<div style='color:#FFF;' class='dialog neighborsBill'>";
	
	echo "<h3>"._TRANSFER."</h3>";
	echo "<div class='button transfer_all'><a onclick='confirm(\" "._REALLY_TRANSFER_ALL." \")' href='includes/load_neighborsBill.php?transferAll=1'>"._TRANSFER_ALL."</a></div>";
	
	while($order = mysql_fetch_object($orders))
	{
		if($order->id != $frontend->order->getID())
		{
			$o = new Order(null, null, null, $order->id);
			if($o->getPrice() > 0 )
			{
				echo "<div class='button transfer_one'><a href='includes/load_neighborsBill.php?transferOrder=".$o->getID()."'>". _TRANSFER ."</a></div>"._PLACE ." ".$o->getPlace().": ";
				echo "&euro; ".$o->getPrice()."";
			}
			$frontend->neighborOrders[$o->getID()] = $o;
		}
		
	}
	
	echo "</div>";
	
}
?>