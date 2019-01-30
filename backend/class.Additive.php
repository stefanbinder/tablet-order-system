<?php
class Additive
{
	private $id = null;
	private $price = null;
	private $tax = null;
	private $time = null;
	
	public $name = null;
	
	private $isInDB = null;
	
	public function __construct($id, $time, $isInDB=false)
	{
		$this->id = (int)$id;
		$this->time = $time;
		$this->isInDB = $isInDB;
		
		/*
		if($time == NULL) {
			$this->time = date('Y-m-d H:i:s');
			$this->isInDB = false;
		} else {
			$this->time = $time;
			$this->isInDB = true;
		}*/
		$this->loadDB();
	}
	
	public function loadDB()
	{
		$additive = mysql_query("SELECT * FROM additives WHERE id = '$this->id'");
		$additive = mysql_fetch_object($additive);
		
		$this->name 			= utf8_encode($additive->name);
		$this->price 			= $additive->price;
		$this->tax 				= $additive->tax;
	}
	
	public function insertDB($order_id, $product_id)
	{
		if($this->isInDB) {
			return false;
		} else {
			$insertAdditiv = mysql_query("INSERT INTO orders_has_products_has_additives VALUES ('$product_id', '$order_id', '$this->id', '$this->time')");
			if($insertAdditiv) return true;
			else return false;
		}
	}
	
	/*
	*	Not in use
	
	public function delete($order_id, $product_id)
	{
		$sql = "DELETE FROM orders_has_products_has_additives WHERE products_id = '".$product_id."' AND orders_id = '".$order_id."' AND time = '".$o."' ";
		$delete = mysql_query($sql);
	}
	*/
	
	public function getPrice($brutto = true, $tax = null)
	{
		if($brutto)
		{
			if($this->tax == $tax) return round(($this->price/100)*(100+$this->tax), 2);
			if($tax == null) return round(($this->price/100)*(100+$this->tax), 2);
		} else {
			if($this->tax == $tax) return $this->price;
			if($tax == null) return $this->price;
		}
		return 0;
	}
	
	public function getID()
	{
		return $this->id;
	}
	
	public function compareTo($a)
	{
		if($this->id == $a->getID() && $this->name == $a->name) return true;
		else return false;
	}
}
?>