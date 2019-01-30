<?php

error_reporting(E_ALL);

require_once('class.Backend.php');
require_once('class.Bill.php');

class BarScreen extends Backend
{
	function __construct($name, $password)
	{
		$this->username = $name;
		$this->password = $password;
		
		$this->loggedIn = true;
	}
	
	public function createOrders()
    {
		$result = mysql_query("SELECT *, TIMEDIFF(SYSDATE(), bar_time) diff FROM view_orders 
									WHERE status >= '".Status::open."' AND status <= '".Status::ready."' AND products_type_id = '2'
									ORDER BY bar_time ASC, table_id ASC, time ASC");
		
		return parent::createOrders($result, 'bar');
    }
	
	/**
     * Short description of method createMenu
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
	 
	 
    public function createMenu()
    {
		$count = mysql_query("SELECT count(*) FROM view_orders 
							   WHERE status >= '".Status::open."' AND status <= '".Status::served."' AND products_type_id = '2'
							   GROUP BY bar_time");	

		$count_waiter_calls = mysql_query("SELECT count(*) anz FROM waiters_call");
											//WHERE call_time >= DATE_SUB(SYSDATE(), INTERVAL 10 MINUTE)");
		$count_waiter_calls = mysql_fetch_object($count_waiter_calls)->anz;		
				   
		$pay_intention_count = mysql_query("SELECT count(*) count FROM orders o WHERE o.pay_intention = '1' AND o.paid = '0'");
		$pay_intention_count = mysql_fetch_object($pay_intention_count);
		
		
		
		$menu  = "<a href='?action=orders'><li>Bestellungen <div id='count_orders' class='count'>".mysql_num_rows($count)."</div></li></a> \n";
		$menu .= "<a href='?action=lastOrders'><li>letzen Bestellungen</li></a> \n";
     	$menu .= "<a href='?action=reservations'><li>Reservierungen</li></a> \n";
		
		($pay_intention_count->count == 0) ? $class = "" : $class = "red";
		$menu .= "<a href='?action=createAdministration'><li>Rechnungen <div id='count_orders' class='count ".$class."'>".$pay_intention_count->count."</div></li></a> \n";
		
		($count_waiter_calls == 0) ? $class = "" : $class = "red";
		$menu .= "<a href='?action=waiterCalls'><li>Kellner gerufen <div id='count_orders' class='count ".$class."'>".$count_waiter_calls."</div></li></a> \n";
		return parent::createMenu($menu);
    }
	
	/**
     * Short description of method createTopMenu
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function createTopMenu()
    {
        $output = "<div id='top_menu'>";
		
		$output .= "<ul><a href='?action=createAdministration'><li>offene Bestellungen</li></a>";
		$output .= "<a href='?action=createHistory'><li>History</li></a>";
		$output .= "<a href='?action=articleManagement'><li>Artikel verwalten</li></a>";
		$output .= "<a href='?action=createReports'><li>Berichte</li></a>";
		$output .= "</ul>";
		
		$output .= "</div>";
		
		$output .= '
					<style type="text/css" title="currentStyle">
						body {
							padding-top: 75px;
						}
					</style>';
		
		return $output;
    }
	
	public function changeStatus($key, $new_status = null)
	{
		if((int) $this->product_blocks[$key]->status == Status::open || $new_status != null) 
		{
			if(isset($new_status)) $this->product_blocks[$key]->status = $new_status;
			else $this->product_blocks[$key]->status = Status::ready;
			
			
			$this->product_blocks[$key]->updateDB();
			
			if(isset($this->product_blocks[$key]->same_products))
			{
				foreach($this->product_blocks[$key]->same_products as $p)
				{
					if(isset($new_status)) $p->status = $new_status;
					else $p->status = Status::ready;
					
					$p->updateDB();
				}
			}
		}
	}
	public function areAllReady($key_array)
	{
		foreach($key_array as $key)
		{
			if($this->product_blocks[$key]->status != Status::ready) return false;
		}
		return true;
	}

    /**
     * Short description of method createReservations
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function createReservations()
    {
        return "reservations";
    }

    /**
     * Short description of method createAdministration
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function createAdministration()
    {
		$output = "<h1>Offene Bestellungen</h1>";
		$tables_result = mysql_query("  SELECT o.id `orders_id`, s.id `seat_id`, d.id `table_id`, d.name `table_name`
										FROM orders o
										JOIN seat s ON s.id = o.seat_id
										JOIN desk d ON d.id = s.table_id
										WHERE o.paid = 0 
										ORDER BY s.table_id asc");
		
		$i = -1;
		$tables = array();
		$table_id = 0;
		$last_table_id = 0;
			
		while($t = mysql_fetch_object($tables_result))
		{
			if($table_id == $t->table_id)
			{
				$tables[$i]['orders'][] = new Order(null, null, null, $t->orders_id);
				
			} else {
				if($last_table_id != $t->table_id) $i++;
				
				$tables[$i]['table_id'] = $t->table_id;
				$tables[$i]['table_name'] = $t->table_name;
				$tables[$i]['orders'][] = new Order(null, null, null, $t->orders_id);
			}
			
			$last_table_id = $table_id;		
			$table_id = $t->table_id;
		}
				
		//$this->debugVar($tables, true);
		$output .= "<table width='100%'><tr><td valign='top'>";
		$output .= "<table class='zebra'><thead><th>Tisch</th><th>Personen</th><th>&nbsp;</th></thead>";
		for($i = 0; $i < count($tables); $i++)
		{
			$output .= "<tr><td>".$tables[$i]['table_name'] ."</td><td>" . count($tables[$i]['orders']) . "</td><td><a href='index.php?action=createTableOverview&table_key=".$i."'><img src='images/look.png' alt='look' /></a></td></tr>";
			
		}
		$output .= "</table>";
		$output .= "</td>";
		$this->tables = $tables;
		
		$output .= "<td valign='top'>";
		
		$pay_intentions = mysql_query("SELECT * FROM view_orders o WHERE o.pay_intention = '1' AND o.paid = '0' GROUP BY orders_id");
		//$pay_intentions = mysql_fetch_object($pay_intentions);
		
		$output .= "<table class='zebra'><thead><th>Tisch/Platz</th><th>Zahlungsart</th><th></th></thead>";
		
		while($pay_intention = mysql_fetch_object($pay_intentions))
		{
			$output .= "<tr><td>".$pay_intention->table_name.$pay_intention->seat_name."</td><td>".$pay_intention->payment_method."</td>";
			$output .= "<td><a href='#' onclick='window.open(\"index.php?action=printOrder&order_id=".$pay_intention->orders_id."\", \"Print Order\", \"status=0,toolbar=0,location=0,menubar=0,directories=0,height=700,width=300,scrollbars=0\");'><img src='images/print.png' alt='look' /></a></td>\n";
			$output .= "</tr>";
		}
		
		$output .= "</table></td></tr></table>";
        return $output;
    }
	
	public function createTableOverview($key)
	{
		$this->createAdministration();
		$table = $this->tables[$key];
		
		$output = "<table><thead>";
		$output .= "<th>Tisch ".$table['table_name']."</th>";
		$output .= "<th></th>";
		$output .= "<th></th>";
		$output .= "<th></th>";	// <a href='index.php?action=stornoOrder&table_key=".$key."&delete=all'><img src='images/cancle.png' alt='storno' /></a>
		$output .= "<th></th>"; // <a href='index.php?action=printOrderManuell&table_key=".$key."&delete=all'><img src='images/print.png' alt='print' /></a>
		$output .= "<th></th>";
		$output .= "</thead><tr><td>&nbsp;</td></tr>";
		
		foreach($table['orders'] as $index=>$order)
		{
			$output .= "<tr> <td>Platz ".$order->getPlace()."</td>";
			$output .= "<td>&euro; ".$order->getPrice()."</td>";
			$output .= "<td><a href='index.php?action=createOrderOverview&table_key=".$key."&order_key=".$index."'><img src='images/look.png' alt='look' /></a></td>\n";
			$output .= "<td><a href='index.php?action=stornoOrder&table_key=".$key."&order_key=".$index."'><img src='images/cancle.png' alt='look' /></a></td>\n";
			$output .= "<td><a href='index.php?action=printOrderManuell&table_key=".$key."&order_key=".$index."'><img src='images/print.png' alt='look' /></a></td>\n";
			$output .= "<td><a href='index.php?action=manuellOrder&table_key=".$key."&order_key=".$index."'><img src='images/order.png' alt='look' /></a></td>\n";
			$output .= "</tr>";
		}
		
		$output .= "</table>\n";
		$output .= "<a href='index.php?action=createAdministration'>Zurück</a>\n";
		
		//echo $this->debugVar($this->tables);
		
		return $output;
	}

    public function createWaiterCalls()
    {
		$calls = mysql_query("SELECT wc.id wcid, wc.seat_id, TIMEDIFF(SYSDATE(), wc.call_time) diff, s.name seat_name, d.name desk_name FROM waiters_call wc, seat s, desk d WHERE wc.seat_id = s.id AND s.table_id = d.id ORDER BY diff ASC");
		$output = "<table id='filter' class='zebra waiters_call'>";
		$output .= "<thead><th>Tisch/Platz</th><th>Gerufen vor...</th></thead>";
		while($call = mysql_fetch_object($calls))
		{
			$output .= "<tr id='".$call->wcid."'><td>".utf8_encode($call->seat_name.$call->desk_name)."</td><td>".$call->diff."</td></tr>";
		}
		$output .= "</table>";
		
        return $output;
		
    }
	
	public function createOrderOverview($table_key, $order_key)
	{
		$this->createAdministration();
		$order = $this->tables[$table_key]['orders'][$order_key];
		
		$output = "<table><thead>\n";
		$output .= "<th>Platz ".$order->getPlace()."</th>\n";
		$output .= "<th></th>\n";
		$output .= "<th></th>\n";
		$output .= "<th></th>\n";
		$output .= "</thead>\n";
		$output .= "</thead><tr><td>&nbsp;</td></tr>\n";
		
		foreach($order->products as $product_key=>$product)
		{
			$output .= "<tr> <td>".$product->getName()." </td>\n";
			$output .= "<td>".$product->getPrice()."</td>\n";
			//$output .= "<td><a href='index.php?action=stornoProducts&table_key=".$table_key."&order_key=".$order_key."&product_key=".$product_key."'><img src='images/cancle.png' alt='storno' /></a></td>";
			$output .= "<td><a href='index.php?action=discountProduct&table_key=".$table_key."&order_key=".$order_key."&product_key=".$product_key."'><img src='images/discount.png' alt='discount' /></a></td> </tr>\n";
		}
		$output .= "</table>\n";
		$output .= "<a href='index.php?action=createTableOverview&table_key=".$table_key."'>Zur&uuml;ck</a>\n";
		
		return $output;
	}
	
	public function discountProduct($table_key, $order_key, $product_key)
	{		
		$this->createAdministration();
		
		if(isset($_POST['submit'], $_POST['discount']))
		{
			$product = $this->tables[$table_key]['orders'][$order_key]->products[$product_key];
			$product->setDiscount($_POST['discount']);
			$this->tables[$table_key]['orders'][$order_key]->products[$product_key] = $product;
			
			unset($_POST);
			header("location: index.php?action=createOrderOverview&table_key=".$table_key."&order_key=".$order_key);
			
		} else {
			$product = $this->tables[$table_key]['orders'][$order_key]->products[$product_key];
			
			$output = "<form class='validate' action'index.php?action=discountProduct$table_key=".$table_key."&order_key=".$order_key."&product_key=".$product_key."' method='post'>\n";
			$output .= "<h3>Wähle den Rabatt für das Produkt</h3>";
			$output .= "<label width='300'>Discount (%):</label><input class='number' type='text' name='discount' value='' />";
			
			$output .= "<div><input type='submit' name='submit' value='durchführen' />";
			$output .= "<a href='index.php?action=createOrderOverview&table_key=".$table_key."&order_key=".$order_key."'><input type='button' value='abbrechen' /></a>";
			
			$output .= "</form>";
			
			return $output;
		}
		
	}
	
	public function stornoOrder($table_key, $order_key)
	{
		$this->createAdministration();
		
		if(isset($_POST['submit'], $_POST['storno_product_keys'], $_POST['reason']))
		{
			/*
			*	One or more products will be canceled...
			*/
			foreach($_POST['storno_product_keys'] as $product_key) 
			{
				$product = $this->tables[$table_key]['orders'][$order_key]->products[$product_key];
				$product->storno($_POST['reason']);
			}
			unset($_POST);
			header("location: index.php?action=stornoOrder&table_key=".$table_key."&order_key=".$order_key);
			
		} elseif(isset($_POST['submit_storno_all'], $_POST['reason'])) {
			/*
			*	The whole order will be canceled
			*/
			$order = $this->tables[$table_key]['orders'][$order_key];
			$order->storno($_POST['reason']);
			
			unset($_POST);
			header("location: index.php?action=createAdministration");
			
		}else {
			//$this->debugVar($this->tables);
			
			$order = $this->tables[$table_key]['orders'][$order_key];
			
			$output = "<form class='validate' action'index.php?action=stornoOrder$table_key=".$table_key."&order_key=".$order_key."' method='post'>\n";
			
			$output .= "<div class='storno_left'>\n";
			$output .= "<h2>Was soll storniert werden?</h2>\n";
			$output .= "<p>Platz: ".$order->getPlace()."</p>\n";
			$output .= "<table>\n";
			foreach($order->products as $product_key=>$product)
			{
				$output .= "<tr>\n";
				$output .= "<td class='product_name'>".$product->getName()."</td>\n";
				$output .= "<td class='checkbox'><input type='checkbox' name='storno_product_keys[]' value='".$product_key."' /></td>\n";
				$output .= "</tr>\n";
			}
			
			$output .= "</table></div><div class='storno_right'> \n";
			$output .= "<h2>Grund?</h2>\n";
			
			$output .= "<table>\n";
			
			$reasons = mysql_query("SELECT * FROM storno_reason WHERE published = '1' AND deleted = '0'");
			while($reason = mysql_fetch_object($reasons))
			{
				$output .= "<tr><td>".$reason->name."</td><td><input class='required' type='radio' name='reason' value='".$reason->id."' /></td></tr>";
			}
			
			$output .= "</table></div>";
			$output .= "<div class='buttons'><input type='submit' name='submit' value='durchführen' />";
			$output .= "<a href='index.php?action=createTableOverview&table_key=".$table_key."'><input type='button' value='abbrechen' /></a><br /><br />";
			
			$output .= "<input type='submit' name='submit_storno_all' value='Komplette Order stornieren' /></div></form>";
			
			return $output;
		}
	}
	
	public function printOrder($order_id)
	{		
		$bill = mysql_query("SELECT * FROM invoices i WHERE i.orders_id = '$order_id'");
		$bill = mysql_fetch_object($bill);
		
		$items = mysql_query("SELECT count(name) anz, ihi.* FROM `invoices_has_items` ihi WHERE invoices_id = '$bill->invoice_number' GROUP BY name");
		
		$output = "<link rel='stylesheet' type='text/css' media='print' href='css/print.css' />";
		$output .= "<img class='logo' src='images/logo.png' />";
		$output .= "<p class='center'>Pan &amp; Stone TC GmbH<br />Johannesgasse 16<br />A- 1010 Wien</p>";
		$output .= "<h1>RECHNUNG</h1>";
		$output .= "<p style='font-size:0.9em;'>RNr.: ".$bill->invoice_number."<br />Tisch/Platz: ".$bill->table."<br />";
		$output .= "------------------------------------------</p>";
		
		$output .= "<table width='90%' style='font-size:0.8em;'>";
		while($item = mysql_fetch_object($items))
		{
			$output .= "<tr>";
			$output .= "<td>".$item->anz."</td>";
			$output .= "<td>".$item->name."</td>";
			$output .= "<td align='right'>".number_format($item->price, 2)."</td>";
			$output .= "<td>".number_format(($item->price)*$item->anz, 2)."</td>";
			$output .= "</tr>";
		}
		$output .= "<tr><td>&nbsp;</td><td>SUMME</td><td align='right'>Euro</td><td><strong>".number_format($bill->amount_brutto, 2)."</strong></td></tr>";
		if($bill->coupon_value > 0) $output .= "<tr><td>&nbsp;</td><td>".utf8_encode($bill->coupon_text)."</td><td align='right'>-</td><td>".number_format($bill->coupon_value, 2)."</td></tr>";
		$output .= "</table>";
		$output .= "<p><br /></p>";
		$output .= "<p style='font-size:0.9em;'>Zahlungsart: ".$bill->payment_method."<br />";
		$output .= "<table width='90%' style='font-size:0.8em;'>";
		$output .= "<tr><td>Umsatz</td><td>Netto</td><td>MWST</td><td>Brutto</td></tr>";
		$output .= "<tr><td>10.0%</td><td>".$bill->amount_10_netto."</td><td>".number_format($bill->amount_10_brutto-$bill->amount_10_netto, 2)."</td><td>".$bill->amount_10_brutto."</td></tr>";
		$output .= "<tr><td>20.0%</td><td>".$bill->amount_20_netto."</td><td>".number_format($bill->amount_20_brutto-$bill->amount_20_netto, 2)."</td><td>".$bill->amount_20_brutto."</td></tr>";
		$output .= "</table>";
		$output .= "<br />";
		$output .= "Datum: ".substr($bill->date, 0, 16)."</p>";
		$output .= "<p class='center'> Vielen Dank für Ihren Besuch im<br /> Viereck - Restaurant | Bar<br />www.viereck-restaurant.at<br />UID: ATU66780646</p>";
		$output .= "<p class='white'></p>";
		$output .= parent::newWindowJS();
		
		$sql = "UPDATE orders SET paid = '1' WHERE id = '$order_id'";
		$update = mysql_query($sql);
		
		return $output;
	}
	
	
	public function manuellOrder($table_key, $order_key)
	{
		$output = "";
		if(isset($_POST['submit']) && ($_POST['products_id_1'] != '' || $_POST['products_id_2'] != ''))
		{
			$order = $this->tables[$table_key]['orders'][$order_key];
			
			if($_POST['products_id_1'] != '')
			{
				$p = new Product($_POST['products_id_1'], $order->getID());
				$order->addProduct($p);
				$output .= "<p>Produkt ".$p->getName()." wurde hinzugefügt!</p>";
			}
			if($_POST['products_id_2'] != '')
			{
				$p = new Product($_POST['products_id_2'], $order->getID());
				$order->addProduct($p);
				$output .= "<p>Produkt ".$p->getName()." wurde hinzugefügt!</p>";
			}
			
		}
		
		$products = mysql_query("SELECT p.id pid, p.name pname, c.products_type_id FROM products p, categories c WHERE p.categories_id = c.id ORDER BY p.name asc");
		
		$output .= "<h1>Produkt Order hinzuf&uuml;gen</h1>\n";
		
		$output .= "<form action='' method='post'>\n";
		
		$select_speise = "<label>Speisen</label>\n<select name='products_id_1'>\n<option value=''>---</option>\n";
		$select_drinks = "<label>Getränke</label>\n<select name='products_id_2'>\n<option value=''>---</option>\n";
		
		while($p = mysql_fetch_object($products))
		{
			if($p->products_type_id == 1) $select_speise .= "<option value='$p->pid'>".utf8_encode($p->pname)."</option>\n";
			if($p->products_type_id == 2) $select_drinks .= "<option value='$p->pid'>".utf8_encode($p->pname)."</option>\n";
		}
		$select_speise .= "</select>\n";
		$select_drinks .= "</select>\n";
		
		$output .= $select_speise;
		$output .= $select_drinks;
		
		$output .= "<input type='submit' name='submit' value='Produkte hinzufügen'>\n";
		$output .= "<a href='index.php?action=createTableOverview&table_key=$table_key'><input type='button' value='Zur&uuml;ck'></a>\n";
		$output .= "</form>\n";
		return $output;
	}
	
	public function createReports()
	{
		$output = "";
		
		if(isset($_POST['date']))
		{
			list ($day, $month, $year) = explode('.', $_POST['date']);
			
			if(isset($_POST['dailyReport'])) {
				
				$from = mktime(6,0,0, $month, $day, $year);
				$to = mktime(5,59,59,$month,$day+1,$year);
				
				$sql = "SELECT * FROM invoices WHERE date > '".date('Y-m-d h:i:s',$from)."' && date < '".date('Y-m-d h:i:s',$to)."'";
				//echo $sql;
				$invoices = mysql_query($sql);
				//echo mysql_num_rows($invoices);
				
				
				
				$output .= "<h1>Tagesbericht</h1>";
				$output .= "<form action='index.php?action=printDailyReport' target='_new' method='POST'>";
				$output .= "<input type='hidden' name='from' value='".date('Y-m-d h:i:s', $from)."' />";
				$output .= "<input type='hidden' name='to' value='".date('Y-m-d h:i:s', $to)."' />";
				
				$output .= "<label>Brutto Ist-Stand:</label>";
				$output .= "<input type='text' name='brutto_ist' />\n";
				
				$output .= "<input type='submit' name='submit' value='Erstellen' />\n";
				$output .= "<a href='index.php?action=createReports'><input type='button' value='Zurück' /></a>\n";
				
				$output .= "</form>";
				//echo date('Y-m-d h:i:s', $from)."-";
				
				//echo date('Y-m-d h:i:s', $to);
				//onSubmit='window.open(\"index.php?action=createReports\", \"Report\", \"status=0,toolbar=0,location=0,menubar=0,directories=0,height=700,width=300,scrollbars=0\")'
				
				
			} elseif (isset($_POST['weeklyReport'])) {
				
			} elseif (isset($_POST['monthlyReport'])) {
				
			} elseif (isset($_POST['dailyCostOfSales'])) {
				
			}
			
		} else {
		
			$output .= "<h1>Berichte</h1>";
			
			$output .= "<form action='index.php?action=createReports' method='post'>";
			$output .= "<label>Datum:</label>";
			$output .= "<input class='datepicker' type='text' name='date' />";
			
			$output .= "<input type='submit' name='dailyReport' value='Tagesbericht erstellen' />\n";
			$output .= "<input type='button' name='weeklyReport' value='Wochenbericht erstellen' />\n";
			$output .= "<input type='button' name='monthlyReport' value='Monatsbericht erstellen' /><br >\n";
			$output .= "</form>";
			
			
			$output .= "<form action='index.php?action=createCostOfSales' target='_blank' method='post'>";
			$output .= "<label>Datum:</label>";
			$output .= "<input class='datepicker' type='text' name='date' />";
			
			$output .= "<input type='submit' name='dailyCostOfSales' value='täglichen Wareneinsatz ermitteln' />\n";
			$output .= "<input type='button' name='weeklyCostOfSales' value='wöchentlichen Wareneinsatz ermitteln' />\n";
			
			$output .= "<p>Beim ausgewählten Datum wird Woche und Monat ausgelesen, also bitte für die Woche einen Tag dieser Woche auswählen.</p>";
			$output .= "</form>";
		}
		return $output;
	}
	
	public function printDailyReport()
	{
		//$this->debugVar($_POST);
		$from		= $_POST['from'];
		$to			= $_POST['to'];
		$ist 		= $_POST['brutto_ist'];
		
		$amounts = mysql_query("SELECT 	sum(amount_brutto) total_brutto, sum(amount_netto) total_netto, sum(amount_10_brutto) total_brutto_10, sum(amount_10_netto) total_netto_10, 
										sum(amount_20_brutto) total_brutto_20, sum(amount_20_netto) total_netto_20, sum(coupon_value) discount, 
										count(*) count, (sum(amount_brutto) / count(*)) invoice_average
								FROM invoices
								WHERE date > '$from' && date < '$to'");
		
		$amounts = mysql_fetch_object($amounts);
		
		$amounts_payments = mysql_query("SELECT payment_method, sum(amount_brutto) total_brutto, sum(amount_netto) total_netto, sum(amount_10_brutto) total_brutto_10, 
												sum(amount_10_netto) total_netto_10, sum(amount_20_brutto) total_brutto_20, sum(amount_20_netto) total_netto_20, sum(coupon_value) discount, 
												count(*) count, (sum(amount_brutto) / count(*)) invoice_average
										FROM invoices
										WHERE date > '$from' && date < '$to'
										GROUP BY payment_method");
		
		$output = "<html><head>";
		$output .= "<link rel='stylesheet' type='text/css' media='print' href='css/report.css' />";
		$output .= "<link rel='stylesheet' type='text/css' media='screen' href='css/report.css' />";
		$output .= "</head><body>";
		
		$output .= "<img class='logo' src='images/logo.png' />";
		$output .= "<p class='center'>Pan &amp; Stone TC GmbH<br />Johannesgasse 16<br />A- 1010 Wien</p>";
				
		$output .= '
				<table width="100%" border="0" cellspacing="3" cellpadding="0">
				  <tr>
					<td colspan="2"><h2>Tageskassenbericht</h2></td>
					<td colspan="2" align="center">Datum: '.date("Y-m-d", time()).'</td>
				  </tr>
				  <tr>
					<td>Berichts Nr.:</td>
					<td>&nbsp;</td>
					<td>Verkaufsdatum:</td>
					<td>'.substr($from, 0, 10).'</td>
				  </tr>
				  <tr>
					<td>Gästezahl:</td>
					<td>'.$amounts->count.'</td>
					<td>Durchschnittbon:</td>
					<td>'.$amounts->invoice_average.'</td>
				  </tr>
				  <tr> 
					<td>Nettorückgaben Gesamt</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				  </tr>
				</table>';
		
		$output .= '
				<table width="100%" border="0" cellspacing="3" cellpadding="0">
				  <tr>
					<td>&nbsp;</td>
					<td align="right">Soll</td>
					<td align="right">Ist</td>
					<td align="right">Differenz</td>
				  </tr>
				  <tr>
					<td><strong>Brutto</strong></td>
					<td align="right">'.$amounts->total_brutto.'</td>
					<td align="right">'.$ist.'</td>
					<td align="right">'.($ist-$amounts->total_brutto).'</td>
				  </tr>
				  <tr>
					<td>10% </td>
					<td align="right">'.$amounts->total_brutto_10.'</td>
					<td align="right">&nbsp;</td>
					<td align="right">&nbsp;</td>
				  </tr>
				  <tr>
					<td>20% </td>
					<td align="right">'.$amounts->total_brutto_20.'</td>
					<td align="right">&nbsp;</td>
					<td align="right">&nbsp;</td>
				  </tr>
				  <tr>
					<td><strong>Netto</strong></td>
					<td align="right">'.$amounts->total_netto.'</td>
					<td align="right">&nbsp;</td>
					<td align="right">&nbsp;</td>
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td align="right">&nbsp;</td>
					<td align="right">&nbsp;</td>
					<td align="right">&nbsp;</td>
				  </tr>
				</table>';
		
		
		$output .= '
				<table width="100%" border="0" cellspacing="3" cellpadding="0">
				  <tr>
					<td><strong>Zahlungsart</strong></td>
					<td><strong>Soll</strong></td>
					<td><strong>Ist</strong></td>
					<td><strong>Differenz</strong></td>
					<td><strong>Anzahl</strong></td>
				  </tr>';
		while($amount_payment = mysql_fetch_object($amounts_payments))
		{
			$output .= '
				  <tr>
					<td>'.$amount_payment->payment_method.'</td>
					<td>'.$amount_payment->total_brutto.'</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>'.$amount_payment->count.'</td>
				  </tr>';
		}
				  
		$output .= '
				  <tr>
					<td><strong>Gesamt</strong></td>
					<td>'.$amounts->total_brutto.'</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>'.$amounts->count.'</td>
				  </tr>
				</table>';
		
		$output .= "</body></html>";
		//$output .= $this->newWindowJS();
		
		return $output;
	}
	
	public function printWeeklyReport()
	{
		
	}
	
	public function printMonthlyReport()
	{
		
	}
	
	public function createCostOfSales()
	{
		$output = "<html><head>";
		$output .= "<link rel='stylesheet' type='text/css' media='print' href='css/report.css' />";
		$output .= "<link rel='stylesheet' type='text/css' media='screen' href='css/report.css' />";
		$output .= "</head><body>";
		
		$output .= "<img class='logo' src='images/logo.png' />";
		$output .= "<p class='center'>Pan &amp; Stone TC GmbH<br />Johannesgasse 16<br />A- 1010 Wien</p>";
		$output .= "<h1>Wareneinsatz</h1>";
		
		
		
		if(isset($_POST['date']))
		{
			list ($day, $month, $year) = explode('.', $_POST['date']);
			
			if(isset($_POST['dailyCostOfSales'])) {
				
				$from = mktime(6,0,0, $month, $day, $year);
				$to = mktime(5,59,59,$month,$day+1,$year);
				
				$saledProducts = mysql_query("SELECT p.name, count(*) count
											  FROM orders_has_products ohp 
											  JOIN products p ON ohp.products_id = p.id
											  WHERE ohp.time > '".date('Y-m-d h:i:s',$from)."' && ohp.time < '".date('Y-m-d h:i:s',$to)."'
											  GROUP BY p.id
											  ORDER BY p.name");
				
				$usedIngredients = mysql_query("SELECT i.name, count(*) count
												FROM orders_has_products ohp
												JOIN products_has_ingredients phi ON ohp.products_id = phi.products_id
												JOIN ingredients i ON phi.ingredients_id = i.id
												WHERE ohp.time > '".date('Y-m-d h:i:s',$from)."' && ohp.time < '".date('Y-m-d h:i:s',$to)."'
												GROUP BY i.name
												ORDER BY i.name");
				
				$usedAdditives 	 = mysql_query("SELECT a.name, count(*) count
												FROM orders_has_products_has_additives ohpha
												JOIN additives a ON ohpha.additives_id = a.id
												WHERE ohpha.time > '".date('Y-m-d h:i:s',$from)."' && ohpha.time < '".date('Y-m-d h:i:s',$to)."'
												GROUP BY a.id
												ORDER BY a.name");
				
				$output .= "<table><thead><th>Produkt</th><th>Anzahl</th><th>Produkt</th><th>Anzahl</th></thead><tbody>";
				$i = 0;
				while($p = mysql_fetch_object($saledProducts))
				{
					$newline = ""; $endline = "";
					($i%2 == 0) ? $newline = "<tr>" : $endline = "</tr><tr>";
					echo $newline;
					$output .= "$newline <td>".utf8_encode($p->name)."</td><td align='center'>$p->count</td> $endline";
					$i++;
				}// && $i = mysql_fetch_object($usedIngredients)  $output .= "<td>$i->name</td><td>$i->count</td></tr>";
				$output .= "</tr></tbody><table><br /><br />";
				
				$output .= "<table><thead><th>Zutaten</th><th>Anzahl</th><th>Zutaten</th><th>Anzahl</th></thead><tbody>";
				$i = 0;
				while($ing = mysql_fetch_object($usedIngredients))
				{
					$newline = ""; $endline = "";
					($i%2 == 0) ? $newline = "<tr>" : $endline = "</tr><tr>";
					echo $newline;
					$output .= "$newline <td>".utf8_encode($ing->name)."</td><td align='center'>$ing->count</td> $endline";
					$i++;
				}// && $i = mysql_fetch_object($usedIngredients)  $output .= "<td>$i->name</td><td>$i->count</td></tr>";
				$output .= "</tr></tbody><table><br /><br />";
				
				$output .= "<table><thead><th>Zusätze</th><th>Anzahl</th><th>Zusätze</th><th>Anzahl</th></thead><tbody>";
				$i = 0;
				while($add = mysql_fetch_object($usedAdditives))
				{
					$newline = ""; $endline = "";
					($i%2 == 0) ? $newline = "<tr>" : $endline = "</tr><tr>";
					echo $newline;
					$output .= "$newline <td>".utf8_encode($add->name)."</td><td align='center'>$add->count</td> $endline";
					$i++;
				}// && $i = mysql_fetch_object($usedIngredients)  $output .= "<td>$i->name</td><td>$i->count</td></tr>";
				$output .= "</tr></tbody><table><br /><br />";
			}
		}
		
		return $output;
	}
	
	public function handleAction($action, $get = null)
	{
		parent::handleAction($action, $get);
		
		switch ($action) {
			case 'login':
				return parent::createContent(parent::createLogin(), self::createMenu());
				break;
			case 'orders':
				return parent::createContent(self::createOrders(), self::createMenu(), parent::createJSrefresh()); //
				break;
			case 'lastOrders':
				return parent::createContent(parent::lastOrders(2), self::createMenu());
				break;
			case 'changeStatus':
				if(isset($get['array_key'])) self::changeStatus($get['array_key']);
				header("location: index.php?action=orders");
				break;
			case 'changeAllStatus':
				if(isset($get['array_keys']))
				{
					$keys = explode("|", $get['array_keys']);
					$status = Status::finished;
					// if($this->areAllReady($keys)) $status = Status::finished;
					
					foreach($keys as $k) self::changeStatus($k, $status);
					return parent::createContent(parent::printBon($keys));
				}
				header("location: index.php?action=orders");
				break;
			case 'reservations':
				return parent::createContent(self::createReservations(), self::createMenu());
				break;
			case 'createAdministration':
				return parent::createContent(self::createAdministration(), self::createMenu(), self::createTopMenu(), parent::createJSrefresh(7000));
				break;
			case 'createTableOverview':
				return parent::createContent(self::createTableOverview($get['table_key']), self::createMenu(), self::createTopMenu());
				break;
			case 'createOrderOverview':
				return parent::createContent(self::createOrderOverview($get['table_key'], $get['order_key']), self::createMenu(), self::createTopMenu());
				break;
			case 'discountProduct':
				return parent::createContent(self::discountProduct($get['table_key'], $get['order_key'], $get['product_key']), self::createMenu(), self::createTopMenu());
				break;
			case 'stornoOrder':
				return parent::createContent(self::stornoOrder($get['table_key'], $get['order_key']), self::createMenu(), self::createTopMenu());
				break;
			case 'discountOrder':
				return parent::createContent(self::discountOrder($get['table_key'], $get['order_key']), self::createMenu(), self::createTopMenu());
				break;
			case 'printBon':
				if(isset($get['bar_time'], $get['table_id'])) return parent::createContent(parent::printBon(null, $get['bar_time'], $get['table_id'], $get['products_type_id']));
				else return parent::createContent(parent::printBon($get['key_array']));
				break;
			case 'printOrder':
				return parent::createContent(self::printOrder($get['order_id']));
				break;
			case 'printOrderManuell':
				
				$order = $this->tables[$get['table_key']]['orders'][$get['order_key']];
				$invoice = mysql_query("SELECT * FROM invoices WHERE orders_id = '".$order->getID()."'");
				if(mysql_num_rows($invoice) == 0) $order->userPay('1');
				
				echo "<script type='text/javascript'>
						window.open(\"index.php?action=printOrder&order_id=".$order->getID()."\", \"Print Order\", \"status=0,toolbar=0,location=0,menubar=0,directories=0,height=700,width=300,scrollbars=0\"); 
						window.location.href = 'index.php?action=createAdministration';		
					  </script>";
				
				break;
			case 'manuellOrder':
				return parent::createContent(self::manuellOrder($get['table_key'], $get['order_key']), self::createMenu(), self::createTopMenu());
				break;
			case 'createHistory':
				if(isset($get['table_id'])) return parent::createContent(self::createHistory($get['table_id'], $get['group_time']), self::createMenu(), self::createTopMenu());
				else return parent::createContent(parent::createHistory(), self::createMenu(), self::createTopMenu());
				break;
			case 'articleManagement':
				return parent::createContent(parent::createArticleManagement(2), self::createMenu(), self::createTopMenu());
				break;
			case 'createReports':				
				return parent::createContent(self::createReports(), self::createMenu(), self::createTopMenu());
				break;
			case 'printDailyReport':
				return self::printDailyReport();
				break;
			case 'printWeeklyReport':
				return self::printWeeklyReport();
				break;
			case 'printMonthlyReport':
				return self::printMonthlyReport();
				break;
			case 'createCostOfSales':
				return self::createCostOfSales();
				break;
			case 'waiterCalls':
				return parent::createContent(self::createWaiterCalls(), self::createMenu(), parent::createJSrefresh());
				break;
			default:
				parent::printError('Action not found, please use the navigation-items!');
				break;
		}
	}
}
?>
