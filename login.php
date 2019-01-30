<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Bestellsystem Login</title>
<link rel="stylesheet" type="text/css" href="css/fonts.css" />
<link rel="stylesheet" type="text/css" href="css/style_start.css" />
<link rel="stylesheet" type="text/css" href="js/lightbox/themes/default/jquery.lightbox.css" />
<script type="text/javascript" language="javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="js/lightbox/jquery.lightbox.min.js"></script>
<script type="text/javascript" language="javascript" src="js/my.js"></script>
</head>
<body class="black">
    <div id="wrapper">
    
        <div class="platzeingabe">
            <form action="index.php?action=language" method="post">
                <label for="tisch_nr">Tisch Nr.: </label><input type="text" name="tisch_nr" /><br />
                <label for="platz_nr">Platz Nr.: </label><input type="text" name="platz_nr" /><br />
                <label for="mastercode">Master Code: </label><input type="password" name="mastercode" /><br />
                <input type="submit" name="submit" value="Einloggen" class="button" /><br  /><br /><br />
                <label for="relogin">Relogin:</label><label class="label_checkbox"><input type="checkbox" name="relogin" /></label>
            </form>
        <?php
			if(isset($_REQUEST['error'])) echo "<br /><br /><br /><h2>".$_REQUEST['error']."</h2>";
		?>
        
        </div>
    	
	</div>
</body>
</html>