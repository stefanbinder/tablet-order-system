<?php

error_reporting(E_ALL);

require_once('class.Bill.php');
require_once('class.Coupon.php');
require_once('class.Product.php');

class Order
{
    private $id = null;
	private $mastercode = null;
	
	public $table = null;
	public $seat = null;
    public $startTime = null;
    public $endTime = null;
    public $products = array();
	
	public $mealProducts = array();		// Needed for the products, which are not in the invoice, e.g. lunch, breakfast etc.
	
	public $foodfilter = array();
    public $coupon = null;
    public $billID = null;
	
	public $payIntention = null;
	public $paid = null;
	public $paymentMethode = '';
	public $invoice_id = null;
	
	public function __construct($table, $seat, $mastercode, $id = NULL)
    {
		if($id == NULL)
		{
			$this->table = $table;
			$this->seat = $seat;
			$this->mastercode = $mastercode;
			
			$this->startTime = date('Y-m-d H:i:s');
			
			$this->updateDB();
			
		} else {
			$this->id = (int) $id;
			$this->loadDB();
		}
    }
	
	public function setCoupon($coupon)
	{
		if($coupon->forSpecialProduct())
		{
			foreach($this->products as $p) 
			{
				if($coupon->forSpecialProduct($p->getID())) 
				{
					$p->setCoupon($coupon);
					break;
				}
			}
		}
		$this->coupon = $coupon;
	}
	
    public function getMasterCode()
	{
		return $this->mastercode;
	}
	
	public function getUserID()
	{
		$user_id = mysql_query("SELECT id FROM user WHERE mastercode = '$this->mastercode'");
		$user_id = mysql_fetch_object($user_id);
		return $user_id->id;
	}
	
	public function getPlace()
	{
		$place = mysql_query("SELECT d.name `table`, s.name `seat` FROM desk d, seat s WHERE d.id = '$this->table' AND s.id = '$this->seat'");
		$place = mysql_fetch_object($place);
		
		return $place->table.$place->seat;
	}
	
	public function getPrice($brutto = true, $tax = null, $withoutCoupon = false)
	{
		if(isset($this->products))
		{
			$price = 0;
			
			foreach($this->products as $p)
			{
				$price += $p->getPrice($brutto, $tax);
			}
			
			if($this->coupon != null && !$this->coupon->forSpecialProduct() && !$withoutCoupon) $price = $this->coupon->calculateDiscount($price);
			
			if($price < 0) $price = 0;
			return number_format($price, 2);
		} else {
			return number_format(0, 2);
		}
	}
	
	public function getID()
	{
		return $this->id;
	}
	
    public function addProduct($p)
    {
        if($p instanceof Product)
		{
			$p->updateDB();
			if($p->isInMeal()) $this->mealProducts[] = $p;
			else $this->products[] = $p;
			return true;
		} else {
			return false;
		}
    }
	
	public function isIngredientInFoodfilter($id)
	{
		if(in_array($id, $this->foodfilter)) return true;
		else return false;
	}
	
	public function isValidForUser()
	{
		if($this->paid == 1 || $this->payIntention == 1) return false;
		return true;
	}
	
	public function getFoodfilter()
	{
		$output = "";
		foreach($this->foodfilter as $i)
		{
			$output .= $i."<br />";
		}
		return $output;
	}
	
	public function displayProducts()
	{
		$countOfProducts = mysql_query("  SELECT products_id, count( * ) `count`
								FROM orders_has_products
								WHERE orders_id = '$this->id'
								GROUP BY products_id");
		$output = "";
		
		while($p = mysql_fetch_object($countOfProducts))
		{
			$parts = array();
			
			foreach($this->products as $product)
			{
				if((int)$product->getID() == (int)$p->products_id) {
					$parts[] = $product;
				}
			}
			
			$alreadyCounted = array();
			
			for($i = 0; $i < count($parts); $i++)
			{
				$count = 1;
				for($j = $i+1; $j < count($parts);$j++)
				{
					if($parts[$i]->compareTo($parts[$j]))
					{
						$count++;
						$alreadyCounted[] = $parts[$j];						
					}
					
				}
				if(!in_array($parts[$i], $alreadyCounted)) $output .= stripslashes($parts[$i]->displayAsTableRow($count));
				
			}
		}
		return "<table class='allProducts'>".$output."</table>";
	}
	
    public function userPay($payment_method)
    {
		$this->endTime = date('Y-m-d H:i:s');
		
		if($this->getPrice() != 0)
		{
			$this->payIntention = 1;
			$this->paymentMethode = $payment_method;
			$this->createInvoice($payment_method);
		} else {
			$this->paid = 1;
		}
		
		$this->updateDB();
    }

    public function createInvoice($payment_method)
    {
		/*
		* 1. Schritt: DB mit statischen Werten befüllen / Coupon beachten
		* 2. Schritt: pdf erstellen
		* 3. Schritt: Waiter Screen als Rechnung/zu Bezahlen anzeigen
		*/
		if($this->invoice_id == null)
		{
			$amount_brutto = $this->getPrice();
			$amount_netto = $this->getPrice(false);
			
			$amount_10_brutto = $this->getPrice(true, 10, true);
			$amount_10_netto = $this->getPrice(false, 10, true);
			
			$amount_20_brutto = $this->getPrice(true, 20, true);
			$amount_20_netto = $this->getPrice(false, 20, true);
			
			
			//echo "Brutto: $amount_brutto Netto: $amount_netto <br />";
			//echo "Brutto10: $amount_10_brutto Netto10: $amount_10_netto <br />";
			//echo "Brutto20: $amount_20_brutto Netto20: $amount_20_netto <br />";
			
			if($this->coupon != null && !$this->coupon->forSpecialProduct()) 
			{
				$coupon_text 	= utf8_decode($this->coupon->getDiscount());
				$coupon_value 	= $this->coupon->getDiscountValue($this->getPrice(true, null, true));
				
				$factor = ($this->getPrice() / ($this->getPrice(true, NULL, true) / 100)) / 100;
				
				$amount_10_brutto 	= number_format($amount_10_brutto * $factor, 2);
				$amount_10_netto 	= number_format($amount_10_netto 	* $factor, 2);
				$amount_20_brutto 	= number_format($amount_20_brutto * $factor, 2);
				$amount_20_netto 	= number_format($amount_20_netto 	* $factor, 2);
				
				$amount_netto = $amount_10_netto + $amount_20_netto;
				
			} else {
				$coupon_text = "";
				$coupon_value = "";
			}
			
			$payment_method_name = mysql_fetch_object(mysql_query("SELECT name FROM payment_method WHERE id = '$payment_method'"));
			
			$invoice = mysql_query("INSERT INTO invoices (`orders_id`, `date`, `table`, `payment_method`, `coupon_text`, `coupon_value`, `amount_brutto`, `amount_netto`, `amount_10_brutto`, `amount_10_netto`, `amount_20_brutto`, `amount_20_netto`) 
						VALUES ('$this->id', '$this->endTime', '".$this->getPlace()."', '$payment_method_name->name', '$coupon_text', '$coupon_value', '$amount_brutto', '$amount_netto', '$amount_10_brutto', '$amount_10_netto', '$amount_20_brutto', '$amount_20_netto');");
			
			$invoice_id = mysql_insert_id();
			
			foreach($this->products as $product)
			{
				if($this->coupon != null && $this->coupon->forSpecialProduct($product->getID())) 
				{
					$coupon_text 	= utf8_decode($this->coupon->getDiscount());
					$coupon_value 	= $this->coupon->getDiscountValue();
				} else {
					$coupon_text = "";
					$coupon_value = "";
				}
				$invoiceitem = mysql_query("INSERT INTO invoices_has_items (invoices_id, name, price, coupon_text, coupon_value)
											VALUES ('$invoice_id', '".$product->getName()." ".$product->getAdditivesAsText()."', '".$product->getNormalPrice()."', '$coupon_text', '$coupon_value')");
				
				unset($coupon_text, $coupon_value);
			}
			
			if($this->coupon != null) $this->coupon->useCoupon();
			
			//return printf("Zu bezahlender Betrag: %s (Netto: %s)",$amount_brutto, $amount_netto);
		}
		
    }
	/*
	* MUST BE CHANGED
	
	public function transfer($newOrder)
	{
		if(!empty($this->products))
		{
			foreach($this->products as $changingProduct)
			{
				foreach($newOrder->products as $p)
				{
					if($p->compareTo($changingProduct)) {
						$p->numbers++;
						$changingProduct->delete();
						
					} else {
						$changingProduct->changeOrderID($newOrder->getID());
						$newOrder->addProduct($changingProduct);
					}
				}
				
			}
			
			
		}
		if(!empty($this->mealProducts))
		{
			
		}
	}
	*/
	
	public function transfer($newID)
	{
		$debug = "";
		
		$sql0 = "INSERT INTO orders_has_products (orders_id, products_id, status_id, time, number, discount, inMeal)
				SELECT '".$newID."', ohp.products_id, ohp.status_id, ohp.time, ohp.number, ohp.discount, ohp.inMeal
				FROM orders_has_products ohp
				WHERE ohp.orders_id = '".$this->id."'
				ON DUPLICATE KEY UPDATE number = ohp.number + 1";		// THIS IS FOR DUPLICATED ENTRIES, PLEASE DELETE AFTER CHANGINGS
		
		$sql1 =	"UPDATE orders_has_products_has_additives
				SET orders_id = '".$newID."'
				WHERE orders_id = '".$this->id."'";
		
		$sql2 = "DELETE FROM orders_has_products WHERE orders_id = '".$this->id."'";
		
		
		
		$transfer0 = mysql_query($sql0);
		$transfer1 = mysql_query($sql1);
		$transfer2 = mysql_query($sql2);
		
		if($transfer0 && $transfer1 && $transfer2) return true;
		return false;
	}
	
	public function storno($reason_id)
	{
		$sql = "INSERT INTO storno (order_id, time, user_id, reason_id)
				SELECT '".$this->id."', '".date('Y-m-d H:i:s')."', user_id, '".$reason_id."' FROM orders WHERE id = '".$this->id."'";
		$reason_log = mysql_query($sql);

		$update_order = mysql_query("UPDATE order SET end_time = '".date('Y-m-d H:i:s')."', pay_intention = '0', paid = '2' WHERE id = '".$this->id."'");
		
		foreach($this->products as $p) $p->storno($reason_id);
		
	}
    
	private function updateDB()
	{
		if(isset($this->id))
		{
			if(isset($this->coupon)) $coupon = ", coupon_id = '".$this->coupon->getID()."'";
			else $coupon = '';
			
			$sql = " UPDATE orders 
					 SET end_time = '$this->endTime', pay_intention = '$this->payIntention', paid = '$this->paid', payment_method = '$this->paymentMethode' $coupon
					 WHERE id = '$this->id'";
			
			//echo $sql;
			
			$update = mysql_query($sql);
			if($update) return true;
			else return false;
		} else {
			$openOrder = mysql_query("SELECT id FROM orders WHERE seat_id = '$this->seat' AND pay_intention = '0' AND paid = '0'");
			if(mysql_num_rows($openOrder) == 1)
			{
				$openOrder = mysql_fetch_object($openOrder);
				$this->id = $openOrder->id;
				$this->loadDB();
			} else {
				$insert = mysql_query( "INSERT INTO orders (seat_id, user_id, start_time)
										SELECT s.id, u.id, '$this->startTime'
										  FROM seat s, user u 
										 WHERE s.id='$this->seat' 
										   AND u.mastercode = '$this->mastercode'");
							
				$this->id = mysql_insert_id();	
				
				if($insert) return true;
				else return false;
			}
		}
	}
	
	private function loadDB($justProducts = false)
	{
		if(!$justProducts)
		{
			$order = mysql_query("SELECT * FROM orders o, seat s, user u WHERE o.id = '$this->id' AND s.id = o.seat_id AND u.id = o.user_id");
			$order = mysql_fetch_object($order);
			
			$this->seat 		= $order->seat_id;
			$this->table		= $order->table_id;
			$this->startTime 	= $order->start_time;
			$this->payIntention = $order->pay_intention;
			$this->paid 		= $order->paid;
			$this->mastercode	= $order->mastercode;
			
			if($order->coupon_id != NULL)
			{
				$coupon = mysql_query("SELECT * FROM coupons WHERE id = '$order->coupon_id'");
				$coupon = mysql_fetch_object($coupon);
				
				$coupon		= new Coupon($coupon->code);
				$this->setCoupon($coupon);
			}
		}
		
		$products = mysql_query("SELECT * FROM orders_has_products WHERE orders_id = '$this->id'");
		while($product = mysql_fetch_object($products))
		{
			$p = new Product($product->products_id, $this->id, $product->time);
			$this->addProduct($p, $p->isInMeal());
		}
		if($justProducts && $this->coupon != NULL) $this->setCoupon($this->coupon);
	}
	
	public function update()
	{
		unset($this->products);
		$this->products = array();
		$this->loadDB(true);
	}
	
	public function __tostring()
	{
		$output = $this->id . " = id <br /> \n";
		$output .= $this->mastercode . " = mastercode <br /> \n";
		$output .= $this->table . " = table <br /> \n";
		$output .= $this->seat . " = seat <br /> \n";
		$output .= $this->startTime . " = openTime <br /> \n";
		$output .= $this->endTime . " = closeTime <br /> \n";
		$output .= $this->couponID . " = couponID <br /> \n";
		$output .= $this->billID . " = billID <br /> \n";
		$output .= $this->openTime . " = openTime <br /> \n";
		$output .= $this->payIntention . " = payIntention <br /> \n";
		$output .= "Products: <br />";
		foreach($this->products as $p)
		{
			$output .= $p->getName();
		}
		
		return $output;
	}

} /* end of class Order */

?>