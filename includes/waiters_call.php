<?php
require_once("../class.Frontend.php");
require_once("functions.php");

session_start();

$frontend = $_SESSION['frontend'];
$frontend->loadLanguageConstants();
$seat = $frontend->order->seat;

if(isset($_POST['submit']))
{
	$seat_id 	= (int)$_POST['seat_id'];
	
	if($frontend->callWaiter($seat_id))
	{
		$_SESSION['message'] =  _SUCCESSFULL_WAITERCALL;
		header("location: ../karte.php");
	} else {
		$_SESSION['message'] =  _FAILED_WAITERCALL;
		header("location: ../karte.php");
	}
}

$lang			= $frontend->language;
echo "<h1>"._CALL_WAITER."</h1>";


echo "<form class='call_waiter' action='".$_SERVER['PHP_SELF']."' method='post'>";
echo "<input type='hidden' name='seat_id' value='".$seat."' />";
echo "<div class='call_waiter_question'>"._CALL_WAITER_QUESTION."</div>";
echo "<input class='button'  type='submit' name='submit' value='". _YES ."' />";
echo "<input class='button' type='reset' onclick='jQuery.lightbox().close();' name='submit' value='". _NO ."' />";
echo "</form>";

echo "<span class='call_waiter_footer'>"._CALL_WAITER_INFO."</span>";

?>