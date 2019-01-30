<?php
require_once("../class.Frontend.php");
require_once("functions.php");

session_start();
if(!isset($_SESSION['frontend']) || !$_SESSION['frontend']->isLoggedIn()) header ("Location: index.php");

$frontend = $_SESSION['frontend'];
$frontend->order->update();

if(isset($_POST['pay']))
{
	$frontend->order->userPay($_POST['payment_method']);
	unset($_POST);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Bestellsystem</title>
<link rel="stylesheet" type="text/css" href="../css/fonts.css" />
<link rel="stylesheet" type="text/css" href="../css/style.css" />
<script type="text/javascript">
setTimeout("window.location.href='../index.php?action=logout'", 60000);
</script>
</head>
<body>
<div id="wrapper" align="center">
	<br /><br /><br />
    <h1>Vielen Dank für Ihren Besuch!</h1>
    <br  /><br />
    
    <?php
		
		if($frontend->order->getPrice() != 0)
		{
			echo '<h4>Der Kellner kommt in Kürze.</h4>';
			echo '<h4>Gesamtbetrag: €' . $frontend->order->getPrice() . '</h4>';
		} else {
			echo '<h4>Da Sie nichts konsumiert haben bzw. Ihr Tischnachbar die Rechnung übernommen hat wird in kürze nur das Tablet abgeholt!</h4>';
		}
	?>
    
    <br /><br /><br />
    <h3>Wir wünschen Ihnen noch einen angenehmen Tag und hoffen, Sie bald wieder bei uns begrü&szlig;en zu dürfen!</h3>
    
    <a href="../index.php?action=logout">Index</a>
    
</div>

</body>
</html>