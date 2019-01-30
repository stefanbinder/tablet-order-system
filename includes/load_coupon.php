<?php
require_once("../class.Frontend.php");
require_once("functions.php");

session_start();

$frontend = $_SESSION['frontend'];
$frontend->loadLanguageConstants();

if(isset($_POST['submit'], $_POST['coupon_code']))
{
	$coupon = new Coupon($_POST['coupon_code']);
		
	if($coupon->isValid())
	{
		$frontend->order->setCoupon($coupon);
		
		$_SESSION['message'] = $coupon->getDiscount();
		header("location: ../karte.php?action=payment");
	} else {
		$_SESSION['message'] = _COUPON_NOT_VALID . "(".$_POST['coupon_code'].")";
		unset($_POST);
		header('location: ../karte.php?action=payment');
	}
	
} else {

	echo "<div class='dialog coupon' style='color:#FFF;'>";
	
	echo "<h3>"._COUPON.":</h3>";
	echo "<form class='coupon' action='".$_SERVER['PHP_SELF']."' method='post'>";
	echo "<input class='code' type='text' name='coupon_code' />";
	echo "<input class='button' type='submit' name='submit' value='"._YES."'>";
	echo "<input class='button' type='reset' name='reset' onclick='jQuery.lightbox().close()' value='"._CANCEL."' />";
	
	echo "</form>";
	
	echo "</div>";
}

?>