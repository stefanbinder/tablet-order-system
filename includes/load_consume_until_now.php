<?php
require_once("../class.Frontend.php");
require_once("functions.php");

session_start();

$frontend = $_SESSION['frontend'];
$frontend->loadLanguageConstants();

$frontend->order->update();

echo "<div style='color:#FFF;' class='dialog consume_until_now'>";

echo "<h3>Bisher konsumiert:</h3>";
echo "<fieldset class='bottom_border'><legend>"._PLACE ." ". $frontend->order->getPlace()."</legend>";
echo $frontend->order->displayProducts();
echo "</fieldset>";

echo "<h2>" . _ALL_ROUND_PRICE . "</h2>";
echo "<div class='price_total'> &euro; <span id='actuall_price'>".number_format($frontend->order->getPrice(), 2)."</span> </div>";
echo "<input class='button' onclick='jQuery.lightbox().close()' type='reset' value='"._BUTTON_BACK_TO_CARD."' />";
echo "<a href='karte.php?action=payment'><input class='button' type='submit' value='"._BUTTON_FURTHER_TO_PAY."' /></a>";



echo "</div>";



?>