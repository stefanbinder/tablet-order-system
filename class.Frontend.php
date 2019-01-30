<?php
require_once("backend/class.inc.php");
require_once("includes/conn.php");
require_once("includes/phpMailer/class.phpmailer.php");
/*
* 	HC-Media Class Frontend
*	TODO: für komplette "OOP" und einheitliche Erstellung durch die Klasse müssen die Sub Kategorien und Bottom Menus und JS hier in dieser Klasse erstellt werden.
*/

class Frontend
{
	public $main_categorie_id = 2;	// Parent-ID from the main categories for products (top-menu)
	public $language = 1; // default 1 -> de, 2 -> en
	
	public $mainCategories = array();
	public $subCategories = array();
	public $all_products = array();
	public $all_additives = array();
	
	
	/*
	*	For meals with courses, we need to store all possible products in $meals.
	*	Added products by customer, are stored in $meal_products
	*	$meal_products_id is the same field in the database-table meals.products_id -> needed for adding a visible product for the customer
	* 	$meal_products_id_extra same like upon
	*/ 
	public $meal = array();
	public $meal_products = array();
	public $meal_products_id = NULL;
	public $meal_products_id_extra = NULL;
	public $actualCourse = 0;
	
	public $order = NULL;
	public $neighborOrders = array();
	
	
	/*
	*	In $action is the current action of the Frondend e.g. lunch, payment, and so on
	*/
	private $action = NULL;
		
	public function __construct()
	{
		
	}
	
	/*
	*	By logging in the order will with these three variables initilized.
	*	Automatically the seat and desk will be stored in order-object
	*/
	public function setOrder($table, $seat, $mastercode, $relogin)
	{
		//echo "relogin: ".$relogin;
		
		if(!isset($table, $seat) || $table == '' || $seat == '') 
		{
			header("location: login.php?error=Please enter table-number and seat!");
		} elseif(!$this->validMastercode($mastercode)) {
			header("location: login.php?error=Please control your mastercode, it is invalid!");
		} else {
			
			$desk_seat 		= mysql_query("SELECT s.id seat_id, s.name seat_name, d.id desk_id, d.name desk_name FROM seat s, desk d WHERE s.name = '$seat' AND d.name = '$table' AND d.id = s.table_id");
			if(mysql_num_rows($desk_seat) == 1)
			{
				$desk_seat 		= mysql_fetch_object($desk_seat);
				$table 			= $desk_seat->desk_id;
				$seat			= $desk_seat->seat_id;
				
				$open_order = mysql_query("SELECT * FROM orders WHERE seat_id = '".$seat."' AND pay_intention = '0' AND paid = '0'");
				
				if($relogin == 0)
				{
					if(mysql_num_rows($open_order) > 0) 
					{
						header("location: login.php?error=An order is already active with! Place: ".$desk_seat->desk_name.$desk_seat->seat_name." ");
					} else {
						$this->order 	= new Order($table, $seat, $mastercode);
						header("location: chooseLanguage.php");
					}
					
				} else {
					if(mysql_num_rows($open_order) == 0) 
					{
						header("location: login.php?error=There is not an open order! Place: ".$desk_seat->desk_name.$desk_seat->seat_name." ");
					} else {
						$open_order = mysql_fetch_object($open_order);
						$this->order 	= new Order(null, null, null, $open_order->id);
						
						header("location: chooseLanguage.php");
					}
				}
			}
			else header("location: login.php?error=You have choose a wrong seat or table!");
		}
	}
	
	public function validMastercode($code)
	{
		$user = mysql_query("SELECT * FROM user WHERE mastercode = '$code'");
		$count = mysql_num_rows($user);
		if($count == 1) {
			$user = mysql_fetch_object($user);
			$this->user = $user->name;
			$this->usertype = $user->type;
			return true;
		} else {
			return false;
		}
	}
	public function isLoggedIn()
	{
		if(isset($this->order) && $this->order->isValidForUser())
		{
			return $this->validMastercode($this->order->getMastercode());
		}
		return false;
	}
	public function mealFull()
	{
		$max = $this->meal_products[$this->actualCourse]['max'];
		
		if(count($this->meal_products[$this->actualCourse]['products']) < $max) return false;
		return true;
		
	}
	public function loadLanguageConstants()
	{
		$lang_id 	= $this->language;
		$lang 		= mysql_query("SELECT code FROM languages WHERE id = '$lang_id'");
		$lang		= mysql_fetch_object($lang);
		$lang_code	= $lang->code;
		if(strlen(trim($lang_code)) > 0) require_once('includes/constants_'.$lang_code.'.php'); 
		else require_once('includes/constants_de.php'); 
		return true;
	}
	
	public function createTopMenu()
	{
		$menu = "";
		
		$mainCats = mysql_query("	SELECT * 
									  FROM categories 
									 WHERE languages_id = ".$this->language." 
									   AND parent = '".$this->main_categorie_id."'
									   AND published = '1'
									   AND (date_format(NOW(),'%H:%i:%s') BETWEEN start_time AND end_time
									    OR (NOT date_format(NOW(),'%H:%i:%s') BETWEEN end_time AND start_time AND start_time > end_time))
								  ORDER BY ordering ASC");
		 
		$count = mysql_num_rows($mainCats);
		$i = 1;
		
		while($mainCat = mysql_fetch_object($mainCats))
		{
			$this->mainCategories[] = $mainCat;
			
			$id 	= utf8_encode($mainCat->id);
			$name	= utf8_encode($mainCat->name);
			$action = utf8_encode($mainCat->action);
			
			if($i == 1) $x = "left";
			else if($i == $count) $x = "right";
			else $x = "middle";
			$i++;
			
			(isset($_SESSION['actual_sub_categorie']) && $_SESSION['actual_sub_categorie'] == $id) ? $active = 'active' : $active = '';
			
			if($action != '')
			{
				$menu .= "<div class='foodmenu_top_button_style ".$x." ".$active."' id='".$id."'><a href='karte.php?action=".$action."'>".$name."</a></div>\n";
			} else {
				$menu .= "<div class='foodmenu_top_button foodmenu_top_button_style ".$x." ".$active."' id='".$id."'>".$name."</div>\n";
			}
		}
		return $menu;
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
			return false;
		}
		return true;
		
		//echo "Die Nachricht wurde erfolgreich versandt";
		//mail("waiter.viereck@gmail.com", "Kellner, bitte zu Platz ".$place->seat_name.$place->desk_name." kommen!", "Komm schnell - will was bestellen!", "From: Bestellsystem <high_priority@4eck.at>");
		//if(!$call) return false;
		//return true;
	}
	
	public function setAction($action)
	{
		$this->action = $action;
	}
	
	public function getMenuSub()
	{
		$this->loadLanguageConstants();
		$output = "";
		switch($this->action)
		{
			case 'lunch0':
				$output .= "<h1 class='h1_sub_lunch'>Vorspeise <span><a href='karte.php?action=lunch1'>Zur 1. Hauptspeise</a></span><span><a href='karte.php?action=lunch2'>Zur 2. Hauptspeise</a></span><span><a href='karte.php?action=lunch3'>Zur Nachspeise</a></span></h1>";	
				break;
			case 'lunch1':
				$output .= "<h1 class='h1_sub_lunch'><span><a href='karte.php?action=lunch0'>Zur Vorspeise</a></span> 1. Hauptspeise <span><a href='karte.php?action=lunch2'>Zur 2. Hauptspeise</a></span><span><a href='karte.php?action=lunch3'>Zur Nachspeise</a></span></h1>";
				break;
			case 'lunch2':
				$output .= "<h1 class='h1_sub_lunch'><span><a href='karte.php?action=lunch0'>Zur Vorspeise</a></span><span><a href='karte.php?action=lunch1'>Zur 1. Hauptspeise</a></span> 2. Hauptspeise <span><a href='karte.php?action=lunch3'>Zur Nachspeise</a></span></h1>";
				break;
			case 'lunch3':
				$output .= "<h1 class='h1_sub_lunch'><span><a href='karte.php?action=lunch0'>Zur Vorspeise</a></span><span><a href='karte.php?action=lunch1'>Zur 1. Hauptspeise</a></span><span><a href='karte.php?action=lunch2'>Zur 2. Hauptspeise</a></span> Nachspeise</h1>";	
				break;
			case 'payment':
				$output .= "<h1>"._PAY."</h1>";
				$output .= "<div class='place'>". _PLACE . $this->order->getPlace()."</div>";
				break;
			case 'aboutus':
				$output .= "<div class='aboutus'><h1>". _ABOUT_US ."</h1></div>";
				break;
			default:
				break;
		}
		return $output;
	}
	
	public $count = 0;
	
	public function getContent()
	{
		switch($this->action)
		{
			case 'payment':				
				return 'includes/payment.php';
				break;
			case 'lunch0':
				if(empty($this->meal) || empty($this->meal_products))
				{
					$sql = "SELECT * FROM meals 
							WHERE 
							categories_id IN ('30', '31')
							AND (NOW() BETWEEN start_time AND end_time )
							AND languages_id = '$this->language'";
					
					//echo $sql;
					
					$meal_select = mysql_query($sql);
					
					$meal_select = mysql_fetch_object($meal_select);
					
					$this->meal_products_id 		= $meal_select->products_id;
					$this->meal_products_id_extra 	= $meal_select->products_id_extra;
					
					$this->meal = array();
					
					/*
					$this->meal_products[0]['max'] = $meal_select->course_1_max;
					$this->meal_products[1]['max'] = $meal_select->course_2_max;
					$this->meal_products[2]['max'] = $meal_select->course_3_max;
					
					
					$this->meal_products[0]['products'] = array();
					$this->meal_products[1]['products'] = array();
					$this->meal_products[2]['products'] = array();
					$this->meal_products[3]['products'] = array();
					*/
					
					$c1_arr = explode(",", $meal_select->course_1);
					$c2_arr = explode(",", $meal_select->course_2);
					$c3_arr = explode(",", $meal_select->course_3);
					
					foreach($c1_arr as $pid) $this->meal[0][] = $pid;
					foreach($c2_arr as $pid) $this->meal[1][] = $pid;
					foreach($c2_arr as $pid) $this->meal[2][] = $pid;
					foreach($c3_arr as $pid) $this->meal[3][] = $pid;
					
					//$this->debugVar($this->meal_products, true);
				}
				//$this->debugVar($this->meal_products, true);
				
				$this->actualCourse = 0;
				return 'includes/lunch.php';
				break;
			case 'lunch1':
				$this->actualCourse = 1;
				return 'includes/lunch.php';
				break;
			case 'lunch2':
				$this->actualCourse = 2;
				return 'includes/lunch.php';
				break;
			case 'lunch3':
				$this->actualCourse = 3;
				return 'includes/lunch.php';
				break;
			case 'aboutus':
				return 'includes/aboutus.php';
				break;
			default:
				return NULL;
				break;
		}
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
	
}

?>