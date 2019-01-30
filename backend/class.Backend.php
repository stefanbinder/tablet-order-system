<?php
require_once("class.inc.php");
require_once("class.screens.php");
require_once("../includes/conn.php");
require_once("../includes/phpMailer/class.phpmailer.php");

error_reporting(E_ALL);

class Backend
{
	/*
	*	TODO: Settings option for Administration
	*/
	
	const PERSONAL_DESK = 52;
	const KITCHEN_SEAT = 681;
	const BAR_SEAT = 682;
	
	
    protected $user = null;
	protected $password = null;
	protected $loggedIn = false;
	
    protected $product_blocks = array();
	protected $tables = array();
	
    public function createLogin()
    {
        $result = "";
		/* preserve GET parameters */
		$get = $_GET;
		unset($get["username"]);
		unset($get["password"]);
		unset($get["logout"]);
		$getString = "";
		$first = TRUE;
		foreach ($get as $key => $value) {
				if ($first) {
						$first = FALSE;
						$getString .= "?";
				} else {
						$getString .= "&";
				}
				$getString .= urlencode($key) . "=" . urlencode($value);
		}
		$result .= sprintf("<form method=\"POST\" action=\"%s%s\">\n", $_SERVER["PHP_SELF"], $getString);
		$result .= "<table class=\"login\">\n";
		$result .= "<tr><td class=\"label\">Username</td><td><input type=\"text\" name=\"username\" size=\"15\"></td></tr>\n";
		$result .= "<tr><td class=\"label\">Password</td><td><input type=\"password\" name=\"password\" size=\"15\"></td></tr>\n";
		$result .= "<tr><td><button type=\"submit\">Log in</button></td></tr>\n";
		$result .= "</table>\n";
		/* preserve POST */
		foreach ($_POST as $key => $value) {
				switch ($key) {
				case "username":
				case "password":
				case "logout":
						break;
				default:
						$result .= sprintf("<input type=\"hidden\" name=\"%s\" value=\"%s\">\n", $key, $value);
						break;
				}
		}

		$result .= "</form>\n";

		return $result;

    }
	
	public function createMenu($expand)
    {
        $menu = "<div id='menu'><ul> \n";
		$menu .= "<a href='?action=login'><li class='login_button'>Login</li></a> \n";
		$menu .= $expand;
		$menu .= "</ul></div> \n";
		return $menu;
    }
	
	public function handleAction($action, $get = null)
	{
		switch ($action) {
			case 'changePublished':
				if(isset($get['field'])) self::changePublished($get['table'], $get['id'], $get['pub'], $get['field']);
				else self::changePublished($get['table'], $get['id'], $get['pub']);
				
				if(isset($get['location']) && $get['location'] != "") header("Location: index.php?action=".$get['location']);
				else header("Location: index.php?action=manage".ucfirst($get['table']));
				break;
			case 'deleteItem':
				self::deleteItem($get['table'], $get['id']);
				if(isset($get['location']) && $get['location'] != "") header("Location: index.php?action=".$get['location']);
				else header("Location: index.php?action=manage".ucfirst($get['table']));
				break;
			case 'setDeleteFlag':
				self::setDeleteFlag($get['table'], $get['id']);
				header("Location: index.php?action=manage".ucfirst($get['table']));
				break;
			case 'callWaiter':
				self::callWaiter($get['seat_id']);
				header("Location: index.php?action=orders");
				break;
			case 'moreInfo':
				return self::createContent(self::moreInfo($get['product_id']));
				break;
			
		}
	}
	
	public function changePublished($table, $item_id, $published_state, $field = "published")
	{
		if($published_state == 1) $pub = 0;
		else $pub = 1;
		$sql = "UPDATE $table SET $field = '$pub' WHERE id = '$item_id'";
		mysql_query($sql);
	}
	
	public function deleteItem($table, $item_id)
	{
		if($table == "products")
		{
			mysql_query("DELETE FROM products_has_additives WHERE products_id = '$item_id'");
			mysql_query("DELETE FROM products_has_ingredients WHERE products_id = '$item_id'");
		}
		mysql_query("DELETE FROM $table WHERE id = '$item_id'");
	}
	
	public function setDeleteFlag($table, $item_id)
	{
		mysql_query("UPDATE $table SET deleted = '1', published = '0' WHERE id = '$item_id'");
	}
	
	public function validLogin($username, $password)
	{
		$user = mysql_query("SELECT * FROM user WHERE username = '".$username."' AND mastercode = '".md5($password)."'");
		$count = mysql_num_rows($user);
		if($count == 1) {
			$user = mysql_fetch_object($user);
			
			return $user->type;
		}
		return 'nothing';
	}
	
	public function isLoggedIn()
	{
		return $this->loggedIn;
	}

    /**
     * Short description of method createOrders
     *
     * @access public
     * @return mixed
     *
    */
	public function createOrders($result, $screen)
    {
        $output = "";
		
		$products = array();		
				
		while($block = mysql_fetch_object($result))
		{
			$product = new Product($block->products_id, $block->orders_id, $block->time);
			$product->bar_time = $block->bar_time;
			$product->table_id = $block->table_id;
			$product->view_row = $block;
			
			$alreadyAdded = false;
			
			foreach($products as $p)
			{
				if($p->compareToBE($product)) //  && $screen != 'kitchen'
				{
					$p->number++;
					$p->same_products[] = $product;
					$alreadyAdded = true;
				}
			}
			if(!$alreadyAdded) 
			{
				$products[] = $product;
			}
		}
		
		$date = "";
		$table = "";
		$count = 1;
		$p_keys = array();
		$lastround = false;
		
		//$this->debugVar($products);
		
		for($i = 0; $i < count($products); $i++)
		{
			if($products[$i]->status == Status::open) $status = 'open';
			if($products[$i]->status == Status::inWork) $status = 'inWork';
			if($products[$i]->status == Status::ready) $status = 'ready';
			if($products[$i]->status == Status::readyForWaiter) $status = 'ready';
			if($products[$i]->status == Status::served) $status = 'served';
			if($products[$i]->status == Status::finished) $status = 'finished';
			
			if($date != $products[$i]->bar_time || $table != $products[$i]->table_id)
			{
				if($count != 1) {
					$keys = "";
					foreach($p_keys as $k) $keys .= $k."|";
					$keys = substr($keys,0,-1);
					$p_keys = array();
					
					if($screen == 'waiter' || $screen == 'bar') $link = "<a href='#' onclick='window.open(\"index.php?action=changeAllStatus&array_keys=$keys\", \"Print Bon\", \"status=0,toolbar=0,location=0,menubar=0,directories=0,height=700,width=300,scrollbars=0\");'>";
					else $link = "<a href='index.php?action=changeAllStatus&array_keys=$keys'>";
					
					$output .= "</div>".$link;
					
					($lastround) ? $t = $i : $t = $i-1;
					
					$output .= "<div class='block' id='".$products[$t]->table_id."'> Tisch ".utf8_encode($products[$t]->view_row->table_name)."<div class='time'>".$products[$t]->view_row->diff."</div> </div> \n";
					$output .= "</a><div class='clear'></div></div> \n\n";
				}
				if(!$lastround)
				{
					$output .= "<div class='table bg".($count % 2)."'> \n";
					$output .= "<div class='products'> \n";
					
					$count++;
				}
			}
			
			if(!$lastround)
			{
				$output .= "<a href='index.php?action=changeStatus&array_key=$i'>";
				
				//$output .= "<div class='p ".$status."'><span class='place'>Sitz: ".$products[$i]->view_row->seat_name."</span><span class='count'>".$products[$i]->number."x </span><span class='p_name'>".$products[$i]->getName()."</span><span class='additiv'>".stripslashes($products[$i]->getAdditivesAsText())."</span><a href='#' onclick='window.open(\"index.php?action=moreInfo&product_id=".$products[$i]->getID()."\", \"Print Bon\", \"status=0,toolbar=0,location=0,menubar=0,directories=0,height=800,width=1000,scrollbars=0\");'><div class='info'>I</div></a></div> \n";
				
				($products[$i]->isInMeal() > 0) ? $course = "<span class='course'>(".$products[$i]->isInMeal().". Gang)</span>" : $course = "";
				
				$output .= "<div class='p ".$status."'><span class='place'>Sitz: ".$products[$i]->view_row->seat_name."</span><span class='count'>".$products[$i]->number."x </span><span class='p_name'>".$products[$i]->getName()."</span><span class='additiv'>".stripslashes($products[$i]->getAdditivesAsText())."</span>".$course."<div class='info'>I</div></div> \n";

				$output .= "</a>";
			
				$p_keys[] = $i;
			
				$table = $products[$i]->table_id;
				$date = $products[$i]->bar_time;
			}
			
			if($i == (count($products)-1) && !$lastround)
			{
				$i--;
				$lastround = true;
				$table = '';
				$date = '';
			}
		}
		
		$this->product_blocks = $products;
				
        return "<div class='".get_class($this)."'>$output</div>";
    }
    

    /**
     * Short description of method createContent
     *
     * @access public
     * @param  $element
     * @return mixed
     */
    public function createContent()
    {
		$args = func_get_args();
		
        $output = self::createHeader();
		foreach($args as $o)
		{
			$output .= $o;
		}
		$output .= self::createFooter();
		
		return $output;
    }
	
	public function createArticleManagement($products_type = 1)
    {
        $output = "<h1>Artikel verwalten</h1>";
		
		$products = mysql_query("	SELECT *, products.id `p_id`, products.name `p_name`, categories.name `c_name`, products.published `p_published`
									FROM products, categories
									WHERE categories.products_type_id = '".$products_type."'
									AND products.categories_id = categories.id");
		$output .= "<table id='filter' class='zebra'><thead><th>Produktname</th><th>Kategorie</th><th>Preis</th><th>Aktivieren</th></thead> ";
		$output .= "<tbody>";
		
		while($product = mysql_fetch_object($products))
		{
			if($product->p_published == 1) $pub_icon = "cancle.png";
			else $pub_icon = "publish.png";
						
			$output .= "<tr><td>".utf8_encode($product->p_name)."</td>";
			$output .= "<td>".utf8_encode($product->c_name)."</td>";
			$output .= "<td>".utf8_encode($product->price)."</td>";
			$output .= "<td><a href='index.php?action=changePublished&table=products&id=$product->p_id&pub=$product->p_published&location=articleManagement'><img alt='Publish/Unpublish' src='images/$pub_icon' /></a></td>";
			$output .= "</tr>";
		}
		
		$output .= "</tbody></table>";
		return $output;
    }
	
	public function createHistory($table_id = null, $group_time = null)
    {
		$output = "<h1>History - abgeschlossene Bestellungen</h1>";
		
		if($table_id == null)
		{
			$output .= "<p>Alle abgeschlossenen Bestellungen in den letzten 3 Stunde</p>";
			
			$tables = mysql_query(" SELECT  i.*, o.seat_id `seat_id`, d.id `table_id`, d.name `table_name`, count(*) persons, sum(amount_brutto) amount_brutto_sum, sum(amount_netto) amount_netto_sum, o.start_time, 
											(round(time_to_sec(o.start_time)/1000) * 1000) group_time
									FROM invoices i
									JOIN orders o ON i.orders_id = o.id
									JOIN seat s ON o.seat_id = s.id
									JOIN desk d ON s.table_id = d.id
									WHERE date > now( ) - INTERVAL 3 HOUR
									GROUP BY table_id, group_time");
			
			/*
			$tables = mysql_query("SELECT *, count(*) persons FROM view_paid_orders
							 WHERE time > date_add(now(), interval -3 hour)
							 GROUP BY table_id");
			*/
			
			$output .= "<table id='filter' class='zebra'>";
			$output .= "<thead><th>Tisch</th><th>Personen</th><th>Tablets ca. eingeloggt:</th><th>Abrechnungs-Zeit</th><th>Gesamt-Betrag</th><th></th></thead><tbody>";
			while($table = mysql_fetch_object($tables))
			{
				$output .= "<tr><td>".utf8_encode($table->table_name)."</td>";
				$output .= "<td>$table->persons</td>";
				$output .= "<td>$table->start_time</td>";
				$output .= "<td>$table->date</td>";
				$output .= "<td>$table->amount_brutto_sum</td>";
				$output .= "<td><a href='index.php?action=createHistory&table_id=".$table->table_id."&group_time=".$table->group_time."'><img src='images/look.png' alt='ansehen' /></a></td></tr>";
			}
			
			
			$output .= "</tbody></table>";
		} else {
			
			//$orders = mysql_query("SELECT * FROM view_paid_orders WHERE table_id = '$table_id'");
			$orders = mysql_query(" SELECT i.*, o.start_time, o.end_time, d.id `table_id`
									FROM invoices i 
									JOIN orders o ON i.orders_id = o.id
									JOIN seat s ON o.seat_id = s.id
									JOIN desk d ON d.id = s.table_id
									WHERE s.table_id = '$table_id' AND (round(time_to_sec(o.start_time)/1000) * 1000) = '$group_time'");
			
			$i = 0;
			
			
			while($order = mysql_fetch_object($orders))
			{
				if($i == 0){
					$output .= "<h3>Table: ".utf8_encode($order->table_name)."</h3>";
			
					$output .= "<table id='filter' class='zebra'>";
					$output .= "<thead><th>Platz</th><th>Payment Method</th><th>Tablet eingeloggt:</th><th>Am Tablet bezahlen gedrückt:</th><th>Rechnung erstellt um</th><th>Rechnung drucken</th></thead><tbody>";
					$i++;
				}
				$output .= "<tr><td>".utf8_encode($order->table)."</td>";
				$output .= "<td>".utf8_encode($order->payment_method)."</td>";
				$output .= "<td>$order->start_time</td>";
				$output .= "<td>$order->end_time</td>";
				$output .= "<td>$order->date</td>";
				$output .= "<td><a href='#' onclick='window.open(\"index.php?action=printOrder&order_id=".$order->orders_id."\", \"Print Order\", \"status=0,toolbar=0,location=0,menubar=0,directories=0,height=700,width=300,scrollbars=0\");'><img src='images/print.png' alt='look' /></a></td></tr>";
			}
			
		}
		
		return $output;
    }
	
	public function callWaiter($seat_id)
	{
		$now	= date("Y-m-d H:i:s");
		$call	= mysql_query("INSERT INTO waiters_call (call_time, seat_id) VALUES ('$now', '$seat_id')");
		$place 	= mysql_query("SELECT s.name seat_name, d.name desk_name FROM desk d, seat s WHERE s.id = '$seat_id' && s.table_id = d.id");
		$place  = mysql_fetch_object($place);
		
		$mail = new phpmailer();
		
		$mail->IsSMTP();
		$mail->Host     = "mail.4eck.at"; // SMTP-Server
		$mail->SMTPAuth = true;     // SMTP mit Authentifizierung benutzen
		$mail->Username = "web28p7";  // SMTP-Benutzername
		$mail->Password = "hugo1234"; // SMTP-Passwort
		
		$mail->From     = "seat@4eck.at";
		$mail->FromName = "Call waiter";
		$mail->AddAddress("waiter.viereck@gmail.com");
		
		$mail->WordWrap = 50;                              // Zeilenumbruch einstellen
		$mail->IsHTML(true);                               // als HTML-E-Mail senden
		
		$mail->Subject  =  "Kellner, bitte zu Platz ".$place->seat_name.$place->desk_name." kommen!";
		$mail->Body     =  "Komm schnell - will was bestellen!";
		
		if(!$mail->Send())
		{
			return "Leider gab es einen Fehler, beim Kellner rufen.";
		}
		return "Kellner wurde gerufen";
		
		//echo "Die Nachricht wurde erfolgreich versandt";
		//mail("waiter.viereck@gmail.com", "Kellner, bitte zu Platz ".$place->seat_name.$place->desk_name." kommen!", "Komm schnell - will was bestellen!", "From: Bestellsystem <high_priority@4eck.at>");
		//if(!$call) return false;
		//return true;
	
		
		mail("waiter.viereck@gmail.com", "Kellner, bitte zu Platz ".$place->seat_name.$place->desk_name." kommen!", "Komm schnell - will was bestellen!", "From: Bestellsystem <high_priority@4eck.at>");
		if(!$call) return "Leider gab es einen Fehler, beim Kellner rufen.";
		return "Kellner wurde gerufen";
	}
	
	public function lastOrders($products_type_id)
	{
		$output = "<h1>Letzten gedruckten Bestellungen</h1>";
		$output .= "<p>Bestellungen gehen bis zu 3 Stunde zurück.</p>";
		$blocks = mysql_query(" SELECT * , TIMEDIFF( SYSDATE( ) , bar_time ) diff
								FROM view_orders
								WHERE STATUS = '".Status::finished."' AND products_type_id = '$products_type_id'
								AND time > ( now( ) - INTERVAL 3 HOUR )
								GROUP BY bar_time, table_id
								ORDER BY time ASC");
		
		$output .= "<table id='filter' class='zebra'>";
		$output .= "<thead><th>Tisch</th><th>vor X Minuten bestellt</th><th>Produkte</th><th></th></thead><tbody>";
		
		while($block = mysql_fetch_object($blocks))
		{
			$products = mysql_query("SELECT * FROM view_orders WHERE table_id = '$block->table_id' AND bar_time = '$block->bar_time' AND products_type_id = '$products_type_id'");
			$p_text = "";
			while($product = mysql_fetch_object($products)) $p_text .= "Platz ".utf8_encode($product->seat_name).": ".utf8_encode($product->products_name)."<br />";
			
			$output .= "<tr><td>".utf8_encode($block->table_name)."</td>";
			$output .= "<td>".$block->diff."</td>";
			$output .= "<td>".$p_text."</td>";
			$output .= "<td><a href='#' onclick='window.open(\"index.php?action=printBon&bar_time=$block->bar_time&table_id=$block->table_id&products_type_id=$products_type_id\", \"Print Bon\", \"status=0,toolbar=0,location=0,menubar=0,directories=0,height=700,width=300,scrollbars=0\");'><img src='images/print.png' alt='print' /></a></td></tr>";
		}
		
		$output .= "</tbody></table>";
		
		return $output;
	}
	
	public function printBon($key_array, $bar_time = null, $table_id = null, $products_type_id = null)
	{
		$output = "<link rel='stylesheet' type='text/css' media='print' href='css/print.css' />";
		
		if($bar_time != null && $table_id != null)
		{
			$products = mysql_query("SELECT * FROM view_orders WHERE table_id = '$table_id' AND bar_time = '$bar_time' AND products_type_id = '$products_type_id'");
			
			$i = 0;
			while($product = mysql_fetch_object($products))
			{
				if($i == 0) 
				{
					$output .= "<h2>Bon - Tisch: ".utf8_encode($product->table_name)."</h2>";
					$i++;
				}
				$output .= utf8_encode($product->seat_name).": ";
				$output .= utf8_encode($product->products_name)."<br />";
			}
			
		} else {
			//$this->debugVar($this->product_blocks[$key_array[0]]);
			$output .= "<h2>Bon - Tisch: ".$this->product_blocks[$key_array[0]]->view_row->table_name."</h2>";
			
			foreach($key_array as $k=>$key)
			{
				$p = $this->product_blocks[$key];
				($p->getAdditivesAsText() != '') ? $additives = "<br />(".$p->getAdditivesAsText().")<br />" : $additives = "";
				($p->number > 1) ? $count = $p->number."x " : $count = "";
				$output .= utf8_encode($p->view_row->seat_name).": ".$count.$p->getName().$additives." <br />";
			}
		}
		
		$output .= self::newWindowJS();
				
		return $output;
	}
	
	public function moreInfo($product_id)
	{
		$p = mysql_fetch_object(mysql_query("SELECT * FROM products WHERE id = '$product_id'"));
		$output = "<h1>".utf8_encode($p->name)."</h1>";
		$output .= "<p>".utf8_encode($p->kitchen_info)."</p>";
		$output .= "<img src='../speisen/".utf8_encode($p->kitchen_info_pic)."' alt='".utf8_encode($p->kitchen_info_pic)."' />";
		$output .= "<button style='width: 200px; height: 70px; border: 2px solid #000; font-size: 1.3em; background-color: #333; color: #fff;' onclick='window.close()'>Close Window</button>";
		return $output;
	}
	
	protected function newWindowJS()
	{
		return "<script language='javascript'>
				$(document).ready(function(){
					window.print();
					window.close();
				});
				</script>";
	}
	
    /**
     * Short description of method createError
     *
     * @access public
     * @return mixed
     */
    public function printError($output)
    {
		$error = "<h2>An error occurs!</h2> <p>Message: <br /><br /> \n";
		$error .= $output . "</p> \n";
		
        self::printMessage($error);
    }

    /**
     * Short description of method printMessage
     *
     * @access public
     * @param  $output
     * @return mixed
     */
    public function printMessage($output)
    {
        print($output);
    }
	
	public function createJSrefresh($time = 5000){
		$script = 	"<script> \n";
		$script .=  "function refreshOrders(){ \n";
		$script .= 		"window.location.href = 'index.php?".$_SERVER['QUERY_STRING']."'; \n";
		$script .=  "} \n";
		$script .=  "setInterval(\"refreshOrders()\", $time); \n";
		$script .=  "</script> \n";
		return $script;
	}
	
	public static function debugVar($var, $exit = false) {
		echo "*** DEBUGGING VAR ***<pre>";
	 
		if (is_array($var) || is_object($var)) {
			echo htmlentities(print_r($var, true));
		} elseif (is_string($var)) {
			echo "string(" . strlen($var) . ") \"" . htmlentities($var) . "\"\n";
		} else {
			var_dump($var);
		}
	 	
		echo "\n</pre>";
	 
		if ($exit) {
			exit;
		}
	}
	
	public function createHeader()
    {
        return
<<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Bestellsystem - Backend</title>
<link rel="stylesheet" type="text/css" href="../css/fonts.css" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="../js/lightbox/themes/default/jquery.lightbox.css" />
<link rel="stylesheet" type="text/css" href="../css/custom-theme/jquery-ui-1.8.18.custom.css" />
<link rel="stylesheet" type="text/css" href="css/jHtmlArea.css" />

<link rel="stylesheet" type="text/css" href="css/jquery.dataTables_themeroller.css" />
<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css" />
<link rel="stylesheet" type="text/css" href="css/demo_page.css" />
<link rel="stylesheet" type="text/css" href="css/demo_table_jui.css" />
<link rel="stylesheet" type="text/css" href="css/demo_table.css" />
<link rel="stylesheet" type="text/css" href="css/ui-lightness/jquery-ui-1.8.18.custom.css" />


<script type="text/javascript" language="javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="../js/jquery.prefixfree.min.js"></script>
<script type="text/javascript" language="javascript" src="../js/jquery-ui-1.8.17.custom.min.js"></script>
<script type="text/javascript" language="javascript" src="../js/lightbox/jquery.lightbox.min.js"></script>
<script type="text/javascript" language="javascript" src="../js/jquery.validate.min.js"></script>
<script type="text/javascript" language="javascript" src="js/jHtmlArea-0.7.0.min.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="js/my.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.datepicker.min.js"></script>


</head>
<body>

HTML;


    }

    /**
     * Short description of method createFooter
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public static function createFooter()
    {
		return "</body></html>";
    }
	
} /* end of class Backend */

?>