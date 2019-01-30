<?php

error_reporting(E_ALL);

require_once('class.Backend.php');
require_once('class.Bill.php');

class WaiterScreen extends Backend
{
	function __construct($name, $password)
	{
		$this->username = $name;
		$this->password = $password;
		
		$this->loggedIn = true;
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
		//$count_orders = 1; // TODO: SELECT * FROM orders SUM TO count (oda irgendwie so :D ) - Christof: jQuery Lösung
		$count = mysql_query("SELECT count(*) FROM view_orders 
							   WHERE status >= '".Status::readyForWaiter."' AND status <= '".Status::served."' AND products_type_id = '1'
							   GROUP BY bar_time");
		
		$count_waiter_calls = mysql_query("SELECT count(*) anz FROM waiters_call");
											//WHERE call_time >= DATE_SUB(SYSDATE(), INTERVAL 10 MINUTE)");
		$count_waiter_calls = mysql_fetch_object($count_waiter_calls)->anz;
		
		($count_waiter_calls == 0) ? $class = "" : $class = "red";
		
		$menu = "<a href='?action=orders'><li> Bestellungen  <div id='count_orders' class='count'>".mysql_num_rows($count)."</div></li></a> \n";
		$menu .= "<a href='?action=waiterCalls'><li>Kellner gerufen <div id='count_orders' class='count ".$class."'>".$count_waiter_calls."</div></li></a> \n";
		$menu .= "<a href='?action=lastOrders'><li>letzen Bestellungen</li></a> \n";
		//$menu .= "<a href='?action=payment'><li>Zu Bezahlen</li></a> \n";
		return parent::createMenu($menu);
    }
	
	public function createOrders()
    {
        $result = mysql_query("SELECT *, TIMEDIFF(SYSDATE(), bar_time) diff FROM view_orders 
							   WHERE status >= '".Status::readyForWaiter."' AND status <= '".Status::served."' AND products_type_id = '1'
							   ORDER BY bar_time ASC, table_id ASC, time ASC");
		
		return parent::createOrders($result, 'waiter');
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
	
	public function payment()
	{
		$output = "<h1>Open Payments</h1>";
		
		$payments = mysql_query("SELECT * FROM orders WHERE pay_intention = 1 AND paid = 0");
		
		while($order = mysql_fetch_object($payments))
		{
			$output .= $order->id . " ";
			$output .= $order->start_time . " <br />";
		}
		
		return $output;
	}
	
	public function changeStatus($key, $new_status = null)
	{		
		if((int) $this->product_blocks[$key]->status >= Status::ready && (int) $this->product_blocks[$key]->status < Status::served || $new_status != null) 
		{
			if(isset($new_status)) $this->product_blocks[$key]->status = $new_status;
			elseif($this->product_blocks[$key]->status == Status::ready) $this->product_blocks[$key]->status = Status::served;
			else $this->product_blocks[$key]->status++;
			
			$this->product_blocks[$key]->updateDB();
			
			if(isset($this->product_blocks[$key]->same_products))
			{
				foreach($this->product_blocks[$key]->same_products as $p)
				{
					if(isset($new_status)) $p->status = $new_status;
					elseif($p->status == Status::ready) $p->status = Status::served;
					else $p->status++;
					
					$p->updateDB();
				}
			}
		}
	}
	
	public function areAllReady($key_array)
	{
		foreach($key_array as $key)
		{
			if($this->product_blocks[$key]->status != Status::served) return false;
		}
		return true;
	}
	
	public function handleAction($action, $get = null)
	{
		parent::handleAction($action, $get);
		
		switch ($action) {
			case 'login':
				return parent::createContent(parent::createLogin(), self::createMenu());
				break;
			case 'orders':
				return parent::createContent(self::createOrders(), self::createMenu(), parent::createJSrefresh());
				break;
			case 'lastOrders':
				return parent::createContent(parent::lastOrders(1), self::createMenu());
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
			case 'waiterCalls':
				return parent::createContent(self::createWaiterCalls(), self::createMenu(), parent::createJSrefresh());
				break;
			case 'payment':
				return parent::createContent(self::payment(), self::createMenu(), parent::createJSrefresh());
				break;
			case 'createHistory':
				if(isset($get['table_id'])) return parent::createContent(self::createHistory($get['table_id']), self::createMenu());
				else return parent::createContent(parent::createHistory(), self::createMenu());
				break;
			case 'printBon':
				if(isset($get['bar_time'], $get['table_id'])) return parent::createContent(parent::printBon(null, $get['bar_time'], $get['table_id'], $get['products_type_id']));
				else return parent::createContent(parent::printBon($get['key_array']));
				break;
			default:
				// print error message
				break;
		}
	}

} /* end of class WaiterScreen */
?>