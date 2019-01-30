<?php

error_reporting(E_ALL);

require_once('class.Backend.php');
require_once('class.Bill.php');

class KitchenScreen extends Backend
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
		$count = mysql_query("SELECT count(*) FROM view_orders 
							   WHERE status >= '".Status::open."' AND status <= '".Status::ready."' AND products_type_id = '1'
							   GROUP BY bar_time");
		
		$menu = "<a href='?action=orders'><li>Bestellungen <div id='count_orders' class='count'>".mysql_num_rows($count)."</div></li></a> \n";
		$menu .= "<a href='?action=articleManagement'><li>ProduktList</li></a> \n";
		$menu .= "<a href='?action=callWaiter&seat_id=". Backend::KITCHEN_SEAT ."'><li>Kellner rufen</li></a> \n";
		$menu .= "<a href='?action=orders'><li>Uhrzeit: ".date('H:i')."</li></a> \n";
		return parent::createMenu($menu);
    }
	
	public function createOrders()
    {
		$result = mysql_query("SELECT *, TIMEDIFF(SYSDATE(), bar_time) diff FROM view_orders 
							   WHERE status >= '".Status::open."' AND status <= '".Status::ready."' AND products_type_id = '1'
							   ORDER BY bar_time ASC, table_id ASC, time ASC");
		
		return parent::createOrders($result, 'kitchen');
    }
	
	public function changeStatus($key, $new_status = null)
	{		
		if((int) $this->product_blocks[$key]->status < Status::ready || $new_status != null) 
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
			case 'changeStatus':
				if(isset($get['array_key'])) self::changeStatus($get['array_key']);
				header("location: index.php?action=orders");
				break;
			case 'changeAllStatus':
				if(isset($get['array_keys']))
				{
					$keys = explode("|", $get['array_keys']);
					$status = null;
					if($this->areAllReady($keys)) $status = Status::readyForWaiter;
					
					foreach($keys as $k) self::changeStatus($k, $status);
				}
				header("location: index.php?action=orders");
				break;
			case 'articleManagement':
				return parent::createContent(parent::createArticleManagement(1), self::createMenu());
				break;
			default:
				// print error message
				break;
		}
	}

} /* end of class KitchenScreen */

?>
