<?php

error_reporting(E_ALL);

require_once('class.inc.php');

class Coupon
{
	private $id = null;
	private $valid = false;
	
    public $code = null;
	public $coupon_text = null;
    public $price = null;
    public $percent = null;
    public $productID = null;
    public $count = null;
    public $dailyCode = null;
	
	public $used = false;
	
	public function __construct($code)
    {		
		$this->code = strtolower($code);
        $this->loadDB();
    }
	
    public function isValid()
    {
		return $this->valid;
    }
	
	public function forSpecialProduct($pid = null)
	{
		if($pid != null && $this->productID == $pid) return true;
		
		if($this->productID != null) return true;
		return false;
	}
	
	/*
	*	Will return the price considering (berücksichtigen) the discount.
	*	e.g. price = 10€ 	discount = 2€ 		return value = 8€
	*/
	public function calculateDiscount($price)
	{
		if($this->price != null)
		{
			return $price - $this->price;
		}
		if($this->percent != null)
		{
			return round($price - ($price * ($this->percent / 100)), 2);
		}
		return $price;
	}
	
	/*
	*	Will return the discount for a special price or calculating the percent
	*	e.g. price = 10€ 	discount = 2€ 		return value = 2€
	*/
	public function getDiscountValue($price = 0)
	{
		if($this->price != null) return $this->price;
		if($this->percent != null) return round(($price * ($this->percent / 100)), 2);
		return 0;
	}
	
	public function useCoupon()
	{
		if($this->isValid())
		{
			$this->count--;
			if($this->dailyCode != null) $this->dailyCode = 0;
			
			$this->updateDB();
			return true;
		}
		return false;
	}
	
	public function getID()
	{
		return $this->id;
	}
	
	public function getDiscount()
	{
		return $this->coupon_text;
	}
	
    public function loadDB()
	{
		$coupon = mysql_query("SELECT * FROM coupons WHERE code = '$this->code' AND published = 1 AND deleted = 0");
		if(mysql_num_rows($coupon) == 1)
		{
			$coupon = mysql_fetch_object($coupon);
			if($coupon->count < 1)
			{
				$this->valid = false;
				return false;
			}
			$this->valid = true;
			$this->id 			= $coupon->id;
			$this->coupon_text  = utf8_encode($coupon->coupon_text);
			$this->price 		= $coupon->price;
			$this->percent 		= $coupon->percent;
			$this->productID 	= $coupon->product_id;
			$this->count 		= $coupon->count;
			$this->dailyCode 	= $coupon->dailyCode;
			
			return true;
		} else {
			$this->valid = false;
			return false;
		}
	}
	
	public function updateDB()
	{
		$update = mysql_query("UPDATE coupons SET count = '$this->count', dailyCode = '$this->dailyCode' WHERE id = '$this->id'");
	}
	
	public function __toString()
	{
		printf("id: %s, valid: %s, code: %s, price: %s, percent: %s, product_id: %s, count: %s, dailyCode: %s, used: %s", $this->id, $this->valid, $this->code, $this->price, $this->percent, $this->productID, $this->count, $this->dailyCode, $this->used);
		return "";
	}
} /* end of class Coupon */

?>