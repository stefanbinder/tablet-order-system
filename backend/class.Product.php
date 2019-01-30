<?php

error_reporting(E_ALL);

require_once('class.Order.php');
require_once('class.Additive.php');
require_once('class.Coupon.php');

class Product
{
    private $id = null;
	private $name = null;
	private $subname = null;
	private $price = null;
	private $tax = null;
	
	private $isInDB = null;
	private $inMeal = 0;
	
	public $order_id = null;	
    public $status = null;
    public $time = null;
    public $number = 1;
    public $discount = null;
    public $additives = array();
	
	public $coupon = null;
	
	// ATTRIBUTE FOR MEAL -> these products must have inMeal = 1
	public $meal_products = array();
	
	// ATTRIBUTES FOR BACKEND
	public $same_products = null;
	public $bar_time = null;
	public $table_id = null;
	public $view_row = null;
	
    public function __construct($id, $order_id, $time = NULL)
	{
		$this->id 	= $id;
		$this->order_id = $order_id;
		
		if($time == NULL) {
			$this->time = date('Y-m-d H:i:s');
			$this->isInDB = false;						
		} else {
			$this->time = $time;
			$this->isInDB = true;
		}
		
		$this->loadDB();
	}
	
	/*
	* not in use
	
	public function changeOrderID($id)
	{
		$this->order_id = $id;
		$this->isInDB = false;
		
		
	}
	*/
	
	public function addAdditive($a)
	{
		if($a instanceof Additive)
		{
			$a->insertDB($this->order_id, $this->id);
			$this->additives[] = $a;
			return true;
		} else {
			return false;
		}
	}
	
	public function getID()
	{
		return $this->id;
	}
	
	public function getName()
	{
		if(isset($this->name)) return $this->name;
		else return "empty";
	}
	
	public function getSubname()
	{
		return $this->subname;
	}
	
	public function getNormalPrice($brutto = true, $tax = null)
	{
		$price = number_format(round(($this->price/100)*(100+$this->tax), 2), 2);
		if(!empty($this->additives))
		{
			foreach($this->additives as $a)
			{
				$price += $a->getPrice($brutto, $tax);
			}
		}
		return number_format($price, 2);
	}
	
	public function getPrice($brutto = true, $tax = null)
	{
		$price = 0;
		
		if($brutto)
		{
			if($this->tax == $tax) $price = round(($this->price/100)*(100+$this->tax), 2);
			if($tax == null) $price = round(($this->price/100)*(100+$this->tax), 2);
		} else {
			if($this->tax == $tax) $price = $this->price;
			if($tax == null) $price = $this->price;
		}
		
		if(!empty($this->additives))
		{
			foreach($this->additives as $a)
			{
				$price += $a->getPrice($brutto, $tax);
			}
		}

		if($this->coupon != null) $price = $this->coupon->calculateDiscount($price);
		if(isset($this->discount) && $this->discount > 0) $price = $price * ($this->discount / 100);
		
		if($price < 0) $price = 0;
		
		return number_format($price, 2);
	}
	
	public function setInMeal($int)
	{
		$this->inMeal = (int) $int;
		/*
		if($int || !$int)
		{
			$this->inMeal = $int;
			return true;
		} else {
			return false;
		}*/
	}
	
	public function isInMeal()
	{
		return $this->inMeal;
	}
	
	public function setTime($time)
	{
		$this->time = $time;
	}
	
	public function setCoupon($coupon)
	{
		$this->coupon = $coupon;
	}
	
	public function displayNameAdditivePrice($count = 1)
	{
		return sprintf("<div class='consumedUntilNow'><span class='name'>%s</span> <span class='additive'>%s</span> <span class='count'>%sx </span><span class='price'>%s &euro;</span></div>", 
						$this->name, $this->getAdditivesAsText(), $count, number_format($count*$this->getPrice(), 2));
	}
	
	public function displayAsTableRow($count = 1)
	{
		return sprintf("<tr><td class='text'>%s <span>%s</span></td><td class='count'>%sx&nbsp;%s</td><td class='price'>&euro;&nbsp;%s</td>", $this->name, $this->getAdditivesAsText(), $count, $this->getPrice(), number_format($count*$this->getPrice(), 2));
	}
	
	public function getAdditivesAsText()
	{
		$output = "";
		foreach($this->additives as $a)
		{
			$output .= $a->name . ", ";
		}
		$output = substr($output, 0, -2);
		return $output;
	}
	
    public function displayBackendLine()
    {
		return sprintf("%s %s %s <br />", $this->name, $this->getAdditivesAsText(), $this->number);
    }
	
	public function compareTo($p)
	{
		//  && $this->order_id == $p->order_id
		if($this->id == $p->getID() && $this->name == $p->getName() && $this->subname == $p->getSubname() && $this->coupon == $p->coupon && $this->inMeal == $p->inMeal && $this->discount == $p->discount) 
		{
			if(isset($this->additives) && count($this->additives) == count($p->additives))
			{
				for($i = 0; $i < count($this->additives); $i++)
				{
					if(!$this->additives[$i]->compareTo($p->additives[$i])) return false;
				}
				return true;
			} else {
				return false;
			}
			return true;
		} else {
			return false;
		}
	}
	
	public function compareToBE($p)
	{
		if(!$this->compareTo($p)) return false;
		if($this->status != $p->status) return false;
		if($this->table_id != $p->table_id) return false;
		if($this->bar_time != $p->bar_time) return false;
		return true;
		
	}
	
	public function storno($reason_id)
	{
		$sql = "INSERT INTO storno (order_id, product_id, time, user_id, reason_id)
				SELECT '".$this->order_id."', '".$this->id."', '".date('Y-m-d H:i:s')."', user_id, '".$reason_id."' FROM orders WHERE id = '".$this->order_id."'";
		$reason_log = mysql_query($sql);
		
		$delete = mysql_query("DELETE FROM orders_has_products_has_additives WHERE products_id = '".$this->id."' AND orders_id = '".$this->order_id."' AND time = '".$this->time."'");
		$delete = mysql_query("DELETE FROM orders_has_products WHERE products_id = '".$this->id."' AND orders_id = '".$this->order_id."' AND time = '".$this->time."'");
		
	}
	
	public function setDiscount($discount)
	{
		$this->discount = $discount;
		$this->updateDB();
	}
	
	private function loadDB()
	{
		$sql = "SELECT * FROM products WHERE id = '$this->id'";
		//echo $sql;
		$product = mysql_query($sql);
		$product = mysql_fetch_object($product);
		
		$this->name 	= utf8_encode($product->name);
		$this->subname 	= utf8_encode($product->subname);
		$this->price 	= $product->price;
		$this->tax		= $product->tax;
		
		$this->status	= Status::open;
		
		if($this->isInDB)
		{
			$ohp_row = mysql_query("SELECT * FROM orders_has_products WHERE orders_id = '$this->order_id' AND products_id = '$this->id' AND time = '$this->time'");
			$ohp_row = mysql_fetch_object($ohp_row);
			$this->status 	= $ohp_row->status_id;
			$this->number 	= $ohp_row->number;
			$this->discount = $ohp_row->discount;
			$this->inMeal 	= $ohp_row->inMeal;
			
			$additives = mysql_query("SELECT * FROM orders_has_products_has_additives WHERE products_id = '".$this->id."' AND orders_id = '$this->order_id' AND time = '$this->time'");
			while($additive = mysql_fetch_object($additives))
			{
				$this->addAdditive(new Additive($additive->additives_id, $this->time, true));
			}
		}
	}
	
	public function updateDB()
	{
		if($this->isInDB)
		{
			$sql = "UPDATE orders_has_products SET status_id = '$this->status', discount = '$this->discount', inMeal = '$this->inMeal'
					WHERE products_id = '$this->id' AND orders_id = '$this->order_id' AND time = '$this->time'";
			
			//echo $sql . "<br>";
			//echo $this->inMeal;
			
			$update = mysql_query($sql);
			
			if(isset($this->same_products)){
				foreach($this->same_products as $p)
				{
					$update = mysql_query("UPDATE orders_has_products SET status_id = '$p->status', discount = '$p->discount', inMeal = '$this->inMeal'
								   		   WHERE products_id = '$p->id' AND orders_id = '$p->order_id' AND time = '$p->time'");
				}
			}
		} else {
			$sql = "INSERT INTO orders_has_products (products_id, orders_id, status_id, time, number, inMeal)
					VALUES ('$this->id', '$this->order_id', '$this->status', '$this->time', '1', '$this->inMeal');";
			$insert = mysql_query($sql);
			$this->isInDB = true;
			
		}
		if(isset($this->additives))
		{
			foreach($this->additives as $a)
			{
				$a->insertDB($this->order_id, $this->id);
			}
		}
	}
	
	/*
	* not in use
	public function delete()
	{
		foreach($this->additives as $additive) $additive->delete($this->order_id, $this->id);
		
		deleting query
		
	}
	*/
	
	public function __tostring()
	{
		return "id: ".$this->id.", p: ".$this->name.", status: ".$this->status.", t_id:".$this->table_id.", bartime:".$this->bar_time.", inMeal: ".$this->inMeal."<br />";
	}
}
?>