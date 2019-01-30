<?php
header("Content-Type: text/html; charset=utf-8");

require_once("class.Frontend.php");
require_once("includes/functions.php");

session_start();
if(!isset($_SESSION['frontend']) || !$_SESSION['frontend']->isLoggedIn()) header ("Location: index.php");

$frontend = $_SESSION['frontend'];
$frontend->loadLanguageConstants();

if(isset($_GET['action']) && $_GET['action'] != '')
{
	$frontend->setAction($_GET['action']);
} else {
	$frontend->setAction(NULL);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Bestellsystem</title>
<link rel="stylesheet" type="text/css" href="/css/fonts.css" />
<link rel="stylesheet" type="text/css" href="/css/style.css" />
<link rel="stylesheet" type="text/css" href="/js/lightbox/themes/default/jquery.lightbox.css" />
<link rel="stylesheet" type="text/css" href="/css/custom-theme/jquery-ui-1.8.17.custom.css" />

<script type="text/javascript" language="javascript" src="/js/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="/js/jquery.prefixfree.min.js"></script>
<script type="text/javascript" language="javascript" src="/js/jquery-ui-1.8.17.custom.min.js"></script>
<script type="text/javascript" language="javascript" src="/js/lightbox/jquery.lightbox.min.js"></script>
<script type="text/javascript" language="javascript" src="/js/iscroll-min.js"></script>
<script type="text/javascript" language="javascript" src="/js/jquery.innerfade.js"></script>
<script type="text/javascript" language="javascript" src="/js/jquery.validate.min.js"></script>
<script type="text/javascript" language="javascript" src="/js/my.js"></script>

</head>
<body>
<div id="wrapper">
    <div id="foodmenu_top">
        <?= $frontend->createTopMenu(); ?>
    </div>
    <div id="foodmenu_sub">
    	<?php
		if($frontend->getMenuSub() != '')
		{
			echo $frontend->getMenuSub();
		} elseif (isset($_SESSION['actual_sub_categorie'])) {
			include ("includes/load_subkategorien.php");
		}
		?>
    </div>
    <div id="contentwrapper">
    	<?php		
		/*
		unset($frontend->meal_products);
		unset($frontend->meal);
		*/
		//$frontend->debugVar($frontend->meal_products);
		
        if($frontend->getContent() != NULL) 
        {
            echo "<div id='content'>";
			if(file_exists($frontend->getContent())) include $frontend->getContent();
			echo "</div>";
			echo '	<script type="text/javascript">
					$(document).ready(function() {
						$("#show_products").hide();
					});
					</script>';
        }
        ?>
       <div id="show_products">
            <div id="foodwrapper">
                <div class="food" id="food">
                <?php
                if (isset($_SESSION['actual_products'])) {
                    include ("includes/load_speisen.php");
                }
                ?>
                </div>
            </div>
            <div id="image">
            
			<?php
			if (isset($_SESSION['actual_products'])) {
				include ("includes/load_kategoriebild.php");
			}
			?>
            
            </div>
            <div class="clear"></div>
       </div> 
    </div>
    <div id="footer">
    	<div class="footer_button lightbox" id="kellner_rufen"  	href="includes/waiters_call.php?lightbox[width]=500&lightbox[height]=440">
        	<div class="text"><img src="images/footer_icons/<?= $frontend->language; ?>_kellner.png" /></div></div>
        
        <div class="footer_button" id="ueber_uns">
        	<div class="text"><a href="karte.php?action=aboutus"><img src="images/footer_icons/<?= $frontend->language; ?>_team.png" /></a></div></div>
        
        <div class="footer_button lightbox" id="info"       		href="info.html?lightbox[width]=500&lightbox[height]=600">
        	<div class="text"><img src="images/footer_icons/<?= $frontend->language; ?>_info.png" /></div></div>
        
        <div class="footer_button lightbox" id="filter"       		href="includes/load_filter.php?lightbox[width]=500&lightbox[height]=600">
        	<div class="text"><img src="images/footer_icons/<?= $frontend->language; ?>_filter.png" /></div></div>
        
        <div class="footer_button lightbox" id="bisher_bestellt" 	href="includes/load_consume_until_now.php?lightbox[width]=500&lightbox[height]=600">
        	<div class="text"><img src="images/footer_icons/<?= $frontend->language; ?>_bisher.png" /></div></div>
        
        <div class="footer_button" 			id="bezahlen">
        	<div class="text"><a href="karte.php?action=payment"><img src="images/footer_icons/<?= $frontend->language; ?>_bezahlen.png" /></a></div></div>
    </div>
</div>
<?php
if(isset($_SESSION['message']) && $_SESSION['message'] != '' || isset($_GET['message']) && $_GET['message'] != '')
{
	
if(isset($_SESSION['message']) && $_SESSION['message'] != '')
{
	$message = $_SESSION['message'];
} else {
	$message = $_GET['message'];
}
?>
<script type='text/javascript'>
$(document).ready(function(){
	var html = $("<div class='center'><p> <?= $message; ?></p></div>");
	
	$.lightbox(html, {
		width   : 390,
		height  : 250
	});
});
</script>
<?php
$_SESSION['message'] = '';
}
?>
<a href="index.php?action=logout">logout</a>

</body>
</html>