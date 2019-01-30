<?php

error_reporting(E_ALL);

require_once('class.Backend.php');


class AdminScreen extends Backend
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
		//$menu = "<a href='?action=orders'><li>Bestellungen <div id='count_orders' class='count'> </div></li></a> \n";
		$menu = "<a href='?action=articleManagement'><li>Management</li></a> \n";
		//$menu .= "<a href='?action=callWaiter'><li>Nix da</li></a> \n";
		return parent::createMenu($menu);
    }
	
    public function createArticleManagement()
    {
        $output	= 	"<p><a href='index.php?action=manageIngredients'>Manage ingredients</a></p>";
		$output .= 	"<p><a href='index.php?action=manageAdditives'>Manage additives</a></p>";
		$output .= 	"<p><a href='index.php?action=manageProducts'>Manage products</a></p>";
		$output .= 	"<p><a href='index.php?action=manageMeals'>Manage meals</a></p>";
		$output .= 	"<p><a href='index.php?action=manageCoupons'>Manage coupons</a></p>";
		$output .= 	"<p><a href='index.php?action=manageStornoReason'>Manage Storno Reasons</a></p>";
		$output .= 	"<p><a href='index.php?action=manageCategories'>Manage Categories</a></p>";
		
		return $output;
    }
	
	public function manageIngredients()
	{
		$output = "<h1>Ingredients</h1>";
		$output .= "<p><a href='index.php?action=articleManagement'><img src='images/backToOverview.png'></a><a href='index.php?action=createIngredient'><img src='images/new.png' /></a></p>\n";
		$output	.= "<table cellpadding='5' id='filter' class='zebra'><thead><th>ingredient</th><th>language</th><th>Aktivieren</th><th>Löschen</th><th>Foodfilter</th></thead>\n";
		$output .= "<tbody>";
		$ingredients = mysql_query("SELECT i.id, i.name iname, i.published, i.foodfilter, l.name lname FROM ingredients i, languages l WHERE i.languages_id = l.id ORDER BY languages_id ASC, iname ASC");
		while($ingredient = mysql_fetch_object($ingredients))
		{
			if($ingredient->published == 1) $pub_icon = "cancle.png";
			else $pub_icon = "publish.png";
			if($ingredient->foodfilter == 1) $foodfilter_icon = "cancle.png";
			else $foodfilter_icon = "publish.png";
			
			$output .= "<tr><td>".utf8_encode($ingredient->iname)."</td><td>".utf8_encode($ingredient->lname)."</td>\n";
			$output .= "<td align='center'><a href='index.php?action=changePublished&table=ingredients&id=$ingredient->id&pub=$ingredient->published'><img src='images/$pub_icon' /></a></td>\n";
			$output .= "<td align='center'><a href='index.php?action=deleteItem&table=ingredients&id=$ingredient->id'><img src='images/delete.png' /></a></td>\n";
			$output .= "<td align='center'><a href='index.php?action=changePublished&table=ingredients&id=$ingredient->id&pub=$ingredient->foodfilter&field=foodfilter'><img src='images/$foodfilter_icon' /></a></td>\n";
			$output .= "</tr>\n";
		}
		$output .= "</tbody></table>\n\n";
		$output .= "<p><br /></p><p><br /></p><p><br /></p>\n";
		return $output;
	}
	
	public function manageAdditives()
	{
		$output = "<h1>Additives</h1>";
		$output .= "<p><a href='index.php?action=articleManagement'><img src='images/backToOverview.png'></a><a href='index.php?action=createAdditive'><img src='images/new.png' /></a></p>";
		$output	.= "<table cellpadding='5' id='filter' class='zebra'><thead><th>additive</th><th>price</th><th>tax</th><th>language</th><th>activate</th><th>delete</th></thead>";
		$output .= "<tbody>";
		$additives = mysql_query("SELECT a.id, a.name aname, a.price, a.tax, a.published, l.name lname FROM additives a, languages l WHERE a.languages_id = l.id ORDER BY languages_id ASC, aname ASC");
		while($additive = mysql_fetch_object($additives))
		{
			if($additive->published == 1) $pub_icon = "cancle.png";
			else $pub_icon = "publish.png";
			
			$output .= "<tr><td>".utf8_encode($additive->aname)."</td><td>".$additive->price."</td><td>".$additive->tax."</td><td>".utf8_encode($additive->lname)."</td>";
			$output .= "<td align='center'><a href='index.php?action=changePublished&table=additives&id=$additive->id&pub=$additive->published'><img alt='Publish/Unpublish' src='images/$pub_icon' /></a></td>";
			$output .= "<td align='center'><a href='index.php?action=deleteItem&table=additives&id=$additive->id'><img alt='Löschen' src='images/delete.png' /></a></td>";
			$output .= "</tr>";
		}
		$output .= "</tbody></table>";
		$output .= "<p><br /></p><p><br /></p><p><br /></p>";
		return $output;
	}
	
	public function manageProducts()
	{
		if(isset($_GET['lang'])) $lang = "AND l.id = '".$_GET['lang']."'";
		else $lang = "";
		$output = "<h1>Products - $lang</h1>";
		$output .= "<p><a href='index.php?action=articleManagement'><img src='images/backToOverview.png'></a><a href='index.php?action=createProduct'><img src='images/new.png' /></a></p>";
		$output	.= "<table cellpadding='5' id='filter' class='zebra'><thead><th>product</th><th>subname</th><th>start time</th><th>end time</th><th>price</th><th>tax</th><th>spicy</th><th>vegetarian</th><th>pic</th><th>pic_more_info</th><th>language</th><th>category</th><th>ordering</th><th>change</th><th>activate</th><th>delete</th></thead>"; // <th>allergy_hint</th>
		
		$products = mysql_query("SELECT p.id, p.name pname, p.subname, p.start_time, p.end_time, p.price, p.tax, if(p.spicy = 1, 'Yes', 'No') spicy, if(p.vegetarian = 1, 'Yes', 'No') vegetarian, p.pic, p.pic_more_info, p.allergy_hint, l.name lname, c.name cname, p.ordering, p.published
								    FROM products p, languages l, categories c
								   WHERE p.languages_id = l.id 
								   	 $lang
									 AND p.categories_id = c.id
									 AND p.deleted = '0'
								ORDER BY p.languages_id ASC, categories_id ASC, ordering ASC");
		echo "<tbody>";
		while($product = mysql_fetch_object($products))
		{
			if($product->published == 1) $pub_icon = "cancle.png";
			else $pub_icon = "publish.png";
			
			$output .= "<tr><td>".utf8_encode($product->pname)."</td><td>".utf8_encode($product->subname)."</td><td>".utf8_encode($product->start_time)."</td><td>".utf8_encode($product->end_time)."</td><td>".$product->price."</td><td>".$product->tax."</td><td>".$product->spicy."</td><td>".$product->vegetarian."</td><td>".$product->pic."</td><td>".$product->pic_more_info."</td><td>".utf8_encode($product->lname)."</td><td>".utf8_encode($product->cname)."</td><td>".$product->ordering."</td>"; // <td>".substr(utf8_encode(stripslashes($product->allergy_hint)), 0, 150)."...</td>
			$output .= "<td align='center'><a href='index.php?action=editProduct&product_id=$product->id'><img alt='edit' src='images/edit.png' /></a></td>";
			$output .= "<td align='center'><a href='index.php?action=changePublished&table=products&id=$product->id&pub=$product->published'><img alt='Publish/Unpublish' src='images/$pub_icon' /></a></td>";
			$output .= "<td align='center'><a href='index.php?action=setDeleteFlag&table=products&id=$product->id'><img alt='Löschen' src='images/delete.png' /></a></td>";
			$output .= "</tr>";
		}
		$output .= "</tbody></table>";
		$output .= "<p><br /></p><p><br /></p><p><br /></p>";
		return $output;
	}
	
	public function manageMeals()
	{
		$output = "<h1>Meals</h1>";
		
		$actualMeals = mysql_query("SELECT *, m.start_time `meal_start_time`, m.end_time `meal_end_time`, m.id `meal_id` FROM meals `m` LEFT JOIN categories ON m.categories_id = categories.id WHERE m.end_time > NOW() ");
		
		$output .= "<p><a href='index.php?action=articleManagement'><img src='images/backToOverview.png'></a><a href='index.php?action=createMeal'><img src='images/new.png' alt='new' /></a></p>";
		
		$output .= "<table class='zebra' id='filter' cellpadding='5'>";
		$output .= "<thead><tr><th></th><th>Name</th><th>Start</th><th>End</th><th>Product</th><th>Extra Product</th><th>C1 MAX</th><th>C2 MAX</th><th>C3 MAX</th><th>Course 1</th><th>Course 2</th><th>Course 3</th></thead>";
		$output .= "<tbody>";
		while($m = mysql_fetch_object($actualMeals))
		{
			$product 		= mysql_fetch_object(mysql_query("SELECT name FROM products WHERE id = '$m->products_id'"));
			$product_extra 	= mysql_fetch_object(mysql_query("SELECT name FROM products WHERE id = '$m->products_id_extra'"));
			
			$output .= "<tr><td><a href='index.php?action=editMeal&meal_id=$m->meal_id'><img src='images/edit.png' alt='edit' /></a>";
			$output .= "<a href='index.php?action=deleteItem&table=meals&id=$m->meal_id'><img src='images/delete.png' alt='edit' /></a></td> ";
			
			$output .= "<td>".utf8_encode($m->name)."</td><td>$m->meal_start_time</td><td>$m->meal_end_time</td><td>". utf8_encode($product->name)." </td><td> ".utf8_encode($product_extra->name)." </td>";
			$output .= "<td> $m->course_1_max </td><td> $m->course_2_max </td><td> $m->course_3_max </td>";
			
			$c1_arr = explode(",", $m->course_1);
			$c2_arr = explode(",", $m->course_2);
			$c3_arr = explode(",", $m->course_3);
			
			$output .= "<td>";
			foreach($c1_arr as $pid) 
			{
				$p = mysql_fetch_object(mysql_query("SELECT name FROM products WHERE id = '$pid'"));
				$output .= utf8_encode($p->name)."<br />";
			}
			$output .= "</td><td>";
			foreach($c2_arr as $pid) 
			{
				$p = mysql_fetch_object(mysql_query("SELECT name FROM products WHERE id = '$pid'"));
				$output .= utf8_encode($p->name)."<br />";
			}
			$output .= "</td><td>";
			foreach($c3_arr as $pid) 
			{
				$p = mysql_fetch_object(mysql_query("SELECT name FROM products WHERE id = '$pid'"));
				$output .= utf8_encode($p->name)."<br />";
			}
			$output .= "</td></tr>";
		}
		$output .= "</tbody></table>";
		return $output;
	}
	
	public function manageCoupons()
	{
		$output = "<h1>Coupons</h1>";
		
		$coupons = mysql_query("SELECT * FROM coupons WHERE deleted = '0'");
		
		$output .= "<p><a href='index.php?action=articleManagement'><img src='images/backToOverview.png'></a><a href='index.php?action=createCoupon'><img src='images/new.png' alt='new' /></a></p>";
		
		$output .= "<table class='zebra' id='filter' cellpadding='5'>";
		$output .= "<thead><tr><th></th><th>code</th><th>Coupon Text</th><th>price</th><th>percent</th><th>product id</th><th>count</th><th>daily Code</th></tr></thead>";
		$output .= "<tbody>";
		
		while($coupon = mysql_fetch_object($coupons))
		{
			$output .= "<tr><td><a href='index.php?action=editCoupon&coupon_id=$coupon->id'><img src='images/edit.png' alt='edit' /></a>";
			$output .= "<a href='index.php?action=setDeleteFlag&table=coupons&id=$coupon->id'><img src='images/delete.png' alt='edit' /></a></td> ";
			$output .= "<td>$coupon->code</td>";
			$output .= "<td>".utf8_encode($coupon->coupon_text)."</td>";
			$output .= "<td>$coupon->price</td>";
			$output .= "<td>$coupon->percent</td>";
			$output .= "<td>$coupon->product_id</td>";
			$output .= "<td>$coupon->count</td>";
			$output .= "<td>$coupon->dailyCode</td></tr>";
		}
		
		$output .= "</tbody></table>";
		$output .= "<p><br /></p><p><br /></p><p><br /></p>";
		
		return $output;
	}
	
	public function manageStornoReason()
	{
		$output = "<h1>Stornos Reasons</h1>";
		
		$output .= "<p><a href='index.php?action=articleManagement'><img src='images/backToOverview.png'></a><a href='index.php?action=createStornoReason'><img src='images/new.png' alt='new' /></a></p>";
		
		$reasons = mysql_query("SELECT * FROM storno_reason WHERE deleted = '0'");
		
		$output .= "<table class='zebra' id='filter' cellpadding='5'>";
		$output .= "<thead><th>Reason</th><th>published</th><th>delete</th></thead>";
		$output .= "<tbody>";
		
		while($reason = mysql_fetch_object($reasons))
		{
			if($reason->published == 1) $pub_icon = "cancle.png";
			else $pub_icon = "publish.png";
			
			$output .= "<tr><td>".utf8_encode($reason->name)."</td>";
			$output .= "<td align='center'><a href='index.php?action=changePublished&table=storno_reason&id=$reason->id&pub=$reason->published'><img alt='Publish/Unpublish' src='images/$pub_icon' /></a></td>";
			$output .= "<td align='center'><a href='index.php?action=setDeleteFlag&table=storno_reason&id=$reason->id'><img src='images/delete.png' alt='delete' /></a></td></tr>";
			
		}
		
		$output .= "</tbody></table>";
		return $output;
	}
	
	public function manageCategories()
	{
		$output = "<h1>Manage Categories</h1>";
		$output .= '<script type="text/javascript" src="js/_lib/jquery.cookie.js"></script>
					<script type="text/javascript" src="js/_lib/jquery.hotkeys.js"></script>
					<script type="text/javascript" src="js/jquery.jstree.js"></script>';
		
		$output .= '<link type="text/css" rel="stylesheet" href="js/_docs/syntax/!style.css"/>
					<link type="text/css" rel="stylesheet" href="js/_docs/!style.css"/>
					<script type="text/javascript" src="js/_docs/syntax/!script.js"></script>';
		
		$output .= '<script type="text/javascript" language="javascript" src="js/manageCategories.js"></script>';
		
		$output .= '<div id="mmenu" style="height:30px; overflow:auto;">
					<input type="button" id="add_folder" value="add Category" style="display:block; float:left;"/>
					<input type="button" id="rename" value="rename" style="display:block; float:left;"/>
					<input type="button" id="remove" value="remove" style="display:block; float:left;"/>
					<input type="button" id="cut" value="cut" style="display:block; float:left;"/>
					<input type="button" id="copy" value="copy" style="display:block; float:left;"/>
					<input type="button" id="paste" value="paste" style="display:block; float:left;"/>
					</div>
					
					<!-- the tree container (notice NOT an UL node) -->
					<div id="demo" class="demo" style="height:500px;"></div>
					<div style="height:30px; text-align:center;">
						<input type="button" style="width:170px; height:24px; margin:5px auto;" value="reconstruct" onclick="$.get(\'./server.php?reconstruct\', function () { $(\'#demo\').jstree(\'refresh\',-1); });" />
						<input type="button" style="width:170px; height:24px; margin:5px auto;" id="analyze" value="analyze" onclick="$(\'#alog\').load(\'./server.php?analyze\');" />
						<input type="button" style="width:170px; height:24px; margin:5px auto;" value="refresh" onclick="$(\'#demo\').jstree(\'refresh\',-1);" />
					</div>
					';
		
		

		
		return $output;
	}
	
	public function createIngredient()
	{
		$output = "";
		if(isset($_POST['ingredient_submit']))
		{
			$name 	= mysql_real_escape_string(utf8_decode(trim($_POST['ingredient'])));
			$lang	= mysql_real_escape_string(utf8_decode(trim($_POST['lang'])));
			if(isset($_POST['published'])) $pub = 1;
			else $pub = 0;
			if(isset($_POST['foodfilter'])) $foodfilter = 1;
			else $foodfilter = 0;
			
			$insert = mysql_query("INSERT INTO ingredients (name, languages_id, published, foodfilter) VALUES ('$name', '$lang', '$pub', '$foodfilter')");
			if($insert) $output .= "<p>Ingredient erstellt.</p>";
		}
		$langs	= mysql_query("SELECT id, name FROM languages ORDER BY id ASC");
		
		$output .= "<form action='index.php?action=createIngredient' method='post'>\n";
		$output .= "<label>ingredient name</label><input type='text' name='ingredient' class='required' minlength='2' maxlength='80' />\n";
		$output .= "<label>language</label><select name='lang' />\n";
		while($lang = mysql_fetch_object($langs)) $output .=  "<option value='".$lang->id."'>".$lang->name."</option>\n";
		$output .= "</select>\n";
		$output .= "<label>published</label><input type='checkbox' name='published' />\n";
		$output .= "<label>Foodfilter</label><input type='checkbox' name='foodfilter' />\n";
		$output .= "<label>&nbsp;</label><input type='submit' name='ingredient_submit' value='Erstellen' />\n";
		$output .= "</form>\n";
		$output .= "<p><br /></p><p><br /></p><p><br /></p>";
		return $output;
	}
	
	public function createAdditive()
	{
		$output = "";
		if(isset($_POST['additive_submit']))
		{
			$name 	= mysql_real_escape_string(utf8_decode(trim($_POST['additive'])));
			$price 	= mysql_real_escape_string(utf8_decode(str_replace(",", ".", trim($_POST['price']))));
			$tax 	= mysql_real_escape_string(utf8_decode(trim($_POST['tax'])));
			$lang	= mysql_real_escape_string(utf8_decode(trim($_POST['lang'])));
			if(isset($_POST['published'])) $pub = 1;
			else $pub = 0;
			
			$insert = mysql_query("INSERT INTO additives (name, price, tax, languages_id, published) VALUES ('$name', '$price', '$tax', '$lang', '$pub')");
			if($insert) $output .= "<p>Additive erstellt.</p>";
		}
		$langs	= mysql_query("SELECT id, name FROM languages ORDER BY id ASC");
		
		$output .= "<form action='index.php?action=createAdditive' method='post'>\n";
		$output .= "<label>additive name</label><input type='text' name='additive' class='required' minlength='2' maxlength='80' />\n";
		$output .= "<label>price</label><input type='text' name='price' class='required' minlength='1' maxlength='7' />\n";
		$output .= "<label>tax</label><select name='tax' /><option value='10'>10%</option><option value='20'>20%</option></select>\n";
		$output .= "<label>language</label><select name='lang' />\n";
		while($lang = mysql_fetch_object($langs)) $output .=  "<option value='".$lang->id."'>".$lang->name."</option>\n";
		$output .= "</select>\n";
		
		$output .= "<label>published</label><input type='checkbox' name='published' />\n";
		$output .= "<label>&nbsp;</label><input type='submit' name='additive_submit' value='Erstellen' />\n";
		$output .= "</form>\n";
		$output .= "<p><br /></p><p><br /></p><p><br /></p>";
		return $output;
	}
	
	public function createProduct()
	{
		$output = "";
		if(isset($_POST['product_submit']))
		{
			$name 			= mysql_real_escape_string(utf8_decode(trim($_POST['product'])));
			$subname		= mysql_real_escape_string(utf8_decode(trim($_POST['subname'])));
			$start_time		= mysql_real_escape_string(utf8_decode(trim($_POST['start_time'])));
			$end_time		= mysql_real_escape_string(utf8_decode(trim($_POST['end_time'])));
			$price 			= mysql_real_escape_string(utf8_decode(str_replace(",", ".", trim($_POST['price']))));
			$tax 			= mysql_real_escape_string(utf8_decode(trim($_POST['tax'])));
			$pic 			= mysql_real_escape_string(utf8_decode(trim($_POST['pic'])));
			$pic_more_info 	= mysql_real_escape_string(utf8_decode(trim($_POST['pic_more_info'])));
			$allergy_hint 	= mysql_real_escape_string(utf8_decode(trim($_POST['allergy_hint'])));
			$lang			= mysql_real_escape_string(utf8_decode(trim($_POST['lang'])));
			$cat			= mysql_real_escape_string(utf8_decode(trim($_POST['cat'])));
			$ordering		= mysql_real_escape_string(utf8_decode(trim($_POST['ordering'])));
			
			if(isset($_POST['published'])) $pub = 1;
			else $pub = 0;
			if(isset($_POST['spicy'])) $spicy = 1;
			else $spicy = 0;
			if(isset($_POST['vegetarian'])) $veg = 1;
			else $veg = 0;
			
			$insert = "INSERT INTO products (name, subname, start_time, end_time, spicy, vegetarian, price, tax, pic, pic_more_info, allergy_hint, languages_id, categories_id, ordering, published) VALUES ('$name', '$subname', '$start_time', '$end_time', '$spicy', '$veg', '$price', '$tax', '$pic', '$pic_more_info', '$allergy_hint', '$lang', '$cat', '$ordering', '$pub')";
			$insert = mysql_query($insert);
			$pid	= mysql_insert_id();
			
			if(isset($_POST['add']))
			{
				foreach($_POST['add'] as $add)
				{
					mysql_query("INSERT INTO products_has_additives (products_id, additives_id) VALUES ('$pid', '$add')");
				}
			}
			
			if(isset($_POST['ing']))
			{
				foreach($_POST['ing'] as $ing)
				{
					mysql_query("INSERT INTO products_has_ingredients (products_id, ingredients_id) VALUES ('$pid', '$ing')");
				}
			}
			
			if($insert) $output .= "<p>Product erstellt.</p>";
		}
		
		$langs			= mysql_query("SELECT id, name FROM languages ORDER BY id ASC");
		$cats			= mysql_query("SELECT id, name FROM categories WHERE parent > 0 ORDER BY id ASC");
		$additives 		= mysql_query("SELECT a.id, a.name FROM additives a WHERE published = '1' ORDER BY name ASC");
		$ingredients 	= mysql_query("SELECT i.id, i.name FROM ingredients i WHERE published = '1' ORDER BY name ASC");
		
		$output .= "<form action='index.php?action=createProduct' method='post'>\n";
		$output .= "<label>product name</label><input type='text' name='product' class='required' minlength='2' maxlength='30' />\n";
		$output .= "<label>subname</label><input type='text' name='subname' class='required' minlength='2' maxlength='50' />\n";
		$output .= "<label>start time</label><input type='text' name='start_time' class='required' minlength='8' maxlength='8' value='00:00:00' />\n";
		$output .= "<label>end time</label><input type='text' name='end_time' class='required' minlength='8' maxlength='8' value='23:59:59' />\n";
		$output .= "<label>price</label><input type='text' name='price' class='required' minlength='1' maxlength='7' />\n";
		$output .= "<label>tax</label><select name='tax' /><option value='10'>10%</option><option value='20'>20%</option></select>\n";
		$output .= "<label>spicy</label><input type='checkbox' name='spicy' />\n";
		$output .= "<label>vegetarian</label><input type='checkbox' name='vegetarian' />\n";
		$output .= "<label>pic</label><input type='text' name='pic' class='required' minlength='4' maxlength='100' />\n";
		$output .= "<label>more info pic</label><input type='text' name='pic_more_info' class='required' minlength='4' maxlength='100' />\n";
		$output .= "<label>allergy hint</label><textarea class='allergy_hint' name='allergy_hint' size='300' cols='50' rows='10'/></textarea>\n";
		$output .= "<label>language</label><select name='lang' />\n";
		while($lang = mysql_fetch_object($langs)) $output .=  "<option value='".$lang->id."'>".utf8_encode($lang->name)."</option>\n";
		$output .= "</select>\n";
		$output .= "<label>category</label><select name='cat' />\n";
		while($cat = mysql_fetch_object($cats)) $output .=  "<option value='".$cat->id."'>".utf8_encode($cat->name)."</option>\n";
		$output .= "</select>\n";
		$output .= "<label>additives</label><select class='multiple' multiple size='3' name='add[]' />\n";
		while($add = mysql_fetch_object($additives)) $output .=  "<option value='".$add->id."'>".utf8_encode($add->name)."</option>\n";
		$output .= "</select>\n";
		$output .= "<label>ingredients</label><select class='multiple' multiple size='3' name='ing[]' />\n";
		while($ing = mysql_fetch_object($ingredients)) $output .=  "<option value='".$ing->id."'>".utf8_encode($ing->name)."</option>\n";
		$output .= "</select>\n";
		
		$output .= "<label>ordering</label><input type='text' name='ordering' minlength='1' maxlength='3' />\n";
		$output .= "<label>published</label><input type='checkbox' name='published' />\n";
		$output .= "<label>&nbsp;</label><input type='submit' name='product_submit' value='Erstellen' />\n";
		$output .= "</form>\n";
		$output .= "<p><br /></p><p><br /></p><p><br /></p>";
		return $output;
	}
	
	public function editProduct($product_id)
	{
		$output = "";
		
		if(isset($_POST['product_submit']))
		{
			$id 			= mysql_real_escape_string(utf8_decode(trim($_POST['product_id'])));
			$name 			= mysql_real_escape_string(utf8_decode(trim($_POST['product'])));
			$subname		= mysql_real_escape_string(utf8_decode(trim($_POST['subname'])));
			$start_time		= mysql_real_escape_string(utf8_decode(trim($_POST['start_time'])));
			$end_time		= mysql_real_escape_string(utf8_decode(trim($_POST['end_time'])));
			$price 			= mysql_real_escape_string(utf8_decode(str_replace(",", ".", trim($_POST['price']))));
			$tax 			= mysql_real_escape_string(utf8_decode(trim($_POST['tax'])));
			$pic 			= mysql_real_escape_string(utf8_decode(trim($_POST['pic'])));
			$pic_more_info 	= mysql_real_escape_string(utf8_decode(trim($_POST['pic_more_info'])));
			$allergy_hint 	= mysql_real_escape_string(utf8_decode(trim($_POST['allergy_hint'])));
			$lang			= mysql_real_escape_string(utf8_decode(trim($_POST['lang'])));
			$cat			= mysql_real_escape_string(utf8_decode(trim($_POST['cat'])));
			$ordering		= mysql_real_escape_string(utf8_decode(trim($_POST['ordering'])));
			
			if(isset($_POST['published'])) $pub = 1;
			else $pub = 0;
			if(isset($_POST['spicy'])) $spicy = 1;
			else $spicy = 0;
			if(isset($_POST['vegetarian'])) $veg = 1;
			else $veg = 0;
			
			$update = "UPDATE products SET name = '$name', subname = '$subname', start_time = '$start_time', end_time = '$end_time', spicy = '$spicy', vegetarian = '$veg', price = '$price', tax = '$tax', pic = '$pic', pic_more_info = '$pic_more_info', allergy_hint = '$allergy_hint', languages_id = '$lang', categories_id = '$cat', ordering = '$ordering', published = '$pub' WHERE id = '$id'"; 
			
			$update = mysql_query($update);
			
			$delete_add_entries = mysql_query("DELETE FROM products_has_additives WHERE products_id = '$id'");
			$delete_ing_entries = mysql_query("DELETE FROM products_has_ingredients WHERE products_id = '$id'");
			
			if(isset($_POST['add']))
			{
				foreach($_POST['add'] as $add)
				{
					mysql_query("INSERT INTO products_has_additives (products_id, additives_id) VALUES ('$id', '$add')");
				}
			}
			
			if(isset($_POST['ing']))
			{
				foreach($_POST['ing'] as $ing)
				{
					mysql_query("INSERT INTO products_has_ingredients (products_id, ingredients_id) VALUES ('$id', '$ing')");
				}
			}
			
			if($update) $output .= "<p>Product wurde editiert.</p>";
		}
		$product		= mysql_query("SELECT * FROM products WHERE id = '$product_id'");
		$product 		= mysql_fetch_object($product);
		
		$selected_ingredients_q	= mysql_query("SELECT ingredients_id FROM products_has_ingredients WHERE products_id = '$product->id'");
		$selected_additives_q 	= mysql_query("SELECT additives_id FROM products_has_additives WHERE products_id = '$product->id'");
		
		$selected_ingredients 	= array();
		$selected_additives 	= array();
		while($i = mysql_fetch_object($selected_ingredients_q)) $selected_ingredients[] = (int)$i->ingredients_id;
		while($a = mysql_fetch_object($selected_additives_q)) $selected_additives[] = (int)$a->additives_id;
		
		//$this->debugVar($selected_additives);
		
		$tax_10 = "";
		$tax_20 = "";
		($product->tax == 10) ? $tax_10 = "selected" : $tax_20 = "selected";
		
		$langs			= mysql_query("SELECT id, name FROM languages ORDER BY id ASC");
		$cats			= mysql_query("SELECT id, name FROM categories WHERE parent > 0 ORDER BY id ASC");
		$additives 		= mysql_query("SELECT a.id, a.name FROM additives a WHERE published = '1' ORDER BY name ASC");
		$ingredients 	= mysql_query("SELECT i.id, i.name FROM ingredients i WHERE published = '1' ORDER BY name ASC");
		
		$output .= "<h2>Product: $product->name (id: $product->id)</h2>";
		
		$output .= "<form action='index.php?action=editProduct&product_id=$product->id' method='post'>\n";
		$output .= "<label>product name</label><input type='text' name='product' class='required' minlength='2' maxlength='30' value='".utf8_encode($product->name)."' />\n";
		$output .= "<label>subname</label><input type='text' name='subname' class='required' minlength='2' maxlength='50' value='".utf8_encode($product->subname)."' />\n";
		$output .= "<label>start time</label><input type='text' name='start_time' class='required' minlength='8' maxlength='8' value='".utf8_encode($product->start_time)."' />\n";
		$output .= "<label>end time</label><input type='text' name='end_time' class='required' minlength='8' maxlength='8' value='".utf8_encode($product->end_time)."' />\n";
		$output .= "<label>price</label><input type='text' name='price' class='required' minlength='1' maxlength='7' value='$product->price' />\n";
		
		$output .= "<label>tax</label><select name='tax' /><option value='10' ".$tax_10.">10%</option><option value='20' ".$tax_20.">20%</option></select>\n";
		
		$output .= "<label>spicy</label><input type='checkbox' name='spicy' ".$this->checked($product->spicy)." />\n";
		$output .= "<label>vegetarian</label><input type='checkbox' name='vegetarian' ".$this->checked($product->vegetarian)." />\n";
		$output .= "<label>pic</label><input type='text' name='pic' class='required' minlength='4' maxlength='100' value='".utf8_encode($product->pic)."' />\n";
		$output .= "<label>more info pic</label><input type='text' name='pic_more_info' class='required' minlength='4' maxlength='100' value='".utf8_encode($product->pic_more_info)."' />\n";
		$output .= "<label>allergy hint</label><textarea class='allergy_hint' name='allergy_hint' size='300' cols='100'/>".utf8_encode($product->allergy_hint)."</textarea>\n";
		$output .= "<label>language</label><select name='lang' />\n";
		while($lang = mysql_fetch_object($langs)) 
		{
			($lang->id == $product->languages_id) ? $select = "selected " : $select = "";
			$output .=  "<option value='".$lang->id."' ".$select.">".utf8_encode($lang->name)."</option>\n";
		}
		$output .= "</select>\n";
		$output .= "<label>category</label><select name='cat' />\n";
		while($cat = mysql_fetch_object($cats)) 
		{
			($cat->id == $product->categories_id) ? $select = "selected " : $select = "";
			$output .=  "<option value='".$cat->id."' ".$select.">".utf8_encode($cat->name)."</option>\n";
		}
		$output .= "</select>\n";
		$output .= "<label>additives</label><select class='multiple' multiple size='3' name='add[]' />\n";
		while($add = mysql_fetch_object($additives)) 
		{
			(in_array($add->id, $selected_additives)) ? $select = "selected " : $select = "";
			$output .=  "<option value='".$add->id."' ".$select.">".utf8_encode($add->name)."</option>\n";
		}
		$output .= "</select>\n";
		$output .= "<label>ingredients</label><select class='multiple' multiple size='3' name='ing[]' />\n";
		while($ing = mysql_fetch_object($ingredients)) 
		{
			(in_array($ing->id, $selected_ingredients)) ? $select = "selected " : $select = "";
			$output .=  "<option value='".$ing->id."' ".$select.">".utf8_encode($ing->name)."</option>\n";
		}
		$output .= "</select>\n";
		
		$output .= "<label>ordering</label><input type='text' name='ordering' minlength='1' maxlength='3' value='".$product->ordering."' />\n";
		$output .= "<label>published</label><input type='checkbox' name='published' ".$this->checked($product->published)." />\n";
		$output .= "<input type='hidden' name='product_id' value='".$product->id."' />";
		$output .= "<label>&nbsp;</label><a href='index.php?action=manageProducts'><input type='button' value='Back' style='float: left;' /></a><input type='submit' name='product_submit' value='Edit' />\n";
		$output .= "</form>\n";
		
		
		return $output;
	}
	
	public function createMeal()
	{
		$output = "";
		if(isset($_POST['submit']))
		{
			$start_time 			= mysql_real_escape_string(utf8_decode(trim($_POST['start_time'])));
			$end_time				= mysql_real_escape_string(utf8_decode(trim($_POST['end_time'])));
			$categories_id			= mysql_real_escape_string(utf8_decode(trim($_POST['categories_id'])));
			$products_id			= mysql_real_escape_string(utf8_decode(trim($_POST['products_id'])));
			$products_id_extra 		= mysql_real_escape_string(utf8_decode(trim($_POST['products_id_extra'])));
			$languages_id			= mysql_real_escape_string(utf8_decode(trim($_POST['lang'])));
			$course_1				= implode(",", $_POST['course_1']);
			$course_2				= implode(",", $_POST['course_2']);
			$course_3				= implode(",", $_POST['course_3']);
			$course_1_max			= mysql_real_escape_string(utf8_decode(trim($_POST['course_1_max'])));
			$course_2_max			= mysql_real_escape_string(utf8_decode(trim($_POST['course_2_max'])));
			$course_3_max			= mysql_real_escape_string(utf8_decode(trim($_POST['course_3_max'])));
			
			$sql = "INSERT INTO meals SET start_time = '$start_time', end_time = '$end_time', categories_id = '$categories_id', products_id = '$products_id', products_id_extra = '$products_id_extra', languages_id = '$languages_id', course_1 = '$course_1', course_2 = '$course_2', course_3 = '$course_3', course_1_max = '$course_1_max', course_2_max = '$course_2_max', course_3_max = '$course_3_max'";			
			$insert = mysql_query($sql);
			
			if($insert) $output .= "Meal is created!";
			else $output .= "There was a problem, try it again!";
		}
		
		$output .= "<h1>New Meal</h1>";
		
		$langs			= mysql_query("SELECT id, name FROM languages ORDER BY id ASC");
		$cats			= mysql_query("SELECT id, name FROM categories WHERE parent = 0 && action != '' ORDER BY id ASC");
		
		$product_cats 	= array();
		
		$output .= "<form action='index.php?action=createMeal' method='POST'>";
		$output .= "<label>Categorie</label><select name='categories_id'>";
		while($cat = mysql_fetch_object($cats))
		{
			$output .= "<option value='$cat->id'>".utf8_encode($cat->name)."</option>";	
			$product_cats[] = $cat->id;
		}
		$output .= "</select>";
		
		$products		= mysql_query("SELECT * FROM products WHERE categories_id IN (".implode(",", $product_cats).")");
		
		$output .= "<label>Product</label><select name='products_id'>";
		while($p = mysql_fetch_object($products))
		{
			$output .= "<option value='$p->id'>".utf8_encode($p->name)."</option>";	
		}
		$output .= "</select>";
		mysql_data_seek($products, 0);
		$output .= "<label>Extra Product</label><select name='products_id_extra'>";
		while($p = mysql_fetch_object($products))
		{
			$output .= "<option value='$p->id'>".utf8_encode($p->name)."</option>";	
		}
		$output .= "</select>";
		
		$output .= "<label>language</label><select name='lang' />\n";
		while($lang = mysql_fetch_object($langs)) $output .=  "<option value='".$lang->id."'>".utf8_encode($lang->name)."</option>\n";
		$output .= "</select>\n";
		
		$output .= "<div style='position: relative; top: -140px; left: 400px; margin-bottom: -10px;'><label>Start Time</label><input type='text' name='start_time' />";
		$output .= "<label>End Time</label><input type='text' name='end_time' />(Format: YYYY-mm-dd hh:mm:ii)</div>";
		
		
		
		$products		= mysql_query("SELECT p.id, p.name, p.subname, p.categories_id, c.products_type_id FROM products `p`
										LEFT JOIN categories `c` ON p.categories_id = c.id
										WHERE p.categories_id NOT IN (".implode(",", $product_cats).") AND c.products_type_id = 1");
		$i = 1;
		
		while($i < 4)
		{
			$output .= "<label>Course $i</label><select class='multiple' multiple size='3' name='course_".$i."[]'>\n";
			while($p = mysql_fetch_object($products)) $output .=  "<option value='".$p->id."'>".utf8_encode($p->name)."</option>\n";
			$output .= "</select>\n";
			$output .= "<div style='position: relative; left: 400px; top: -100px; margin-bottom: -60px;'>maximal choosing for Customer <input type='text' name='course_".$i."_max' value='1' /></div>";
			mysql_data_seek($products, 0);
			$i++;
		}
		
		$output .= "<input type='submit' name='submit' value='create Meal'>";
		$output .= "<a href='index.php?action=manageMeals'><input type='button' value='back to overview'></a>";
		$output .= "</form>";
		return $output;
	}
	
	public function editMeal($meal_id)
	{
		$output = "";
		if(isset($_POST['submit']))
		{
			$start_time 			= mysql_real_escape_string(utf8_decode(trim($_POST['start_time'])));
			$end_time				= mysql_real_escape_string(utf8_decode(trim($_POST['end_time'])));
			$categories_id			= mysql_real_escape_string(utf8_decode(trim($_POST['categories_id'])));
			$products_id			= mysql_real_escape_string(utf8_decode(trim($_POST['products_id'])));
			$products_id_extra 		= mysql_real_escape_string(utf8_decode(trim($_POST['products_id_extra'])));
			$languages_id			= mysql_real_escape_string(utf8_decode(trim($_POST['lang'])));
			$course_1				= implode(",", $_POST['course_1']);
			$course_2				= implode(",", $_POST['course_2']);
			$course_3				= implode(",", $_POST['course_3']);
			$course_1_max			= mysql_real_escape_string(utf8_decode(trim($_POST['course_1_max'])));
			$course_2_max			= mysql_real_escape_string(utf8_decode(trim($_POST['course_2_max'])));
			$course_3_max			= mysql_real_escape_string(utf8_decode(trim($_POST['course_3_max'])));
			
			$sql = "UPDATE meals SET start_time = '$start_time', end_time = '$end_time', categories_id = '$categories_id', products_id = '$products_id', products_id_extra = '$products_id_extra', languages_id = '$languages_id', course_1 = '$course_1', course_2 = '$course_2', course_3 = '$course_3', course_1_max = '$course_1_max', course_2_max = '$course_2_max', course_3_max = '$course_3_max' WHERE id = '$meal_id'";			
			$update = mysql_query($sql);
			
			if($update) $output .= "Meal is edited!<br /><br />";
			else $output .= "There was a problem, try it again!<br /><br />";
		}
		
		$output .= "<h1>Edit Meal</h1>";
		
		$meal 			= mysql_fetch_object(mysql_query("SELECT * FROM meals WHERE id = '$meal_id'"));
		$langs			= mysql_query("SELECT id, name FROM languages ORDER BY id ASC");
		$cats			= mysql_query("SELECT id, name FROM categories WHERE parent = 0 && action != '' ORDER BY id ASC");
		
		$product_cats 	= array();
		
		$output .= "<form action='index.php?action=editMeal&meal_id=$meal_id' method='POST'>";
		$output .= "<label>Categorie</label><select name='categories_id'>";
		while($cat = mysql_fetch_object($cats))
		{
			($meal->categories_id == $cat->id) ? $select = "selected " : $select = "";
			$output .= "<option value='$cat->id' $select>".utf8_encode($cat->name)."</option>";	
			$product_cats[] = $cat->id;
		}
		$output .= "</select>";
		
		$products		= mysql_query("SELECT * FROM products WHERE categories_id IN (".implode(",", $product_cats).")");
		
		$output .= "<label>Product</label><select name='products_id'>";
		while($p = mysql_fetch_object($products))
		{
			($meal->products_id == $p->id) ? $select = "selected " : $select = "";
			$output .= "<option value='$p->id' $select>".utf8_encode($p->name)."</option>";	
		}
		$output .= "</select>";
		mysql_data_seek($products, 0);
		$output .= "<label>Extra Product</label><select name='products_id_extra'>";
		while($p = mysql_fetch_object($products))
		{
			($meal->products_id_extra == $p->id) ? $select = "selected " : $select = "";
			$output .= "<option value='$p->id' $select>".utf8_encode($p->name)."</option>";	
		}
		$output .= "</select>";
		
		$output .= "<label>language</label><select name='lang' />\n";
		while($lang = mysql_fetch_object($langs)) 
		{
			($meal->languages_id == $lang->id) ? $select = "selected " : $select = "";
			$output .=  "<option value='".$lang->id."' $select>".utf8_encode($lang->name)."</option>\n";
		}
		$output .= "</select>\n";
		
		$output .= "<div style='position: relative; top: -140px; left: 400px; margin-bottom: -10px;'><label>Start Time</label><input type='text' name='start_time' value='$meal->start_time' />";
		$output .= "<label>End Time</label><input type='text' name='end_time' value='$meal->end_time' />(Format: YYYY-mm-dd hh:mm:ii)</div>";
		
		
		
		$products		= mysql_query("SELECT p.id, p.name, p.subname, p.categories_id, c.products_type_id FROM products `p`
										LEFT JOIN categories `c` ON p.categories_id = c.id
										WHERE p.categories_id NOT IN (".implode(",", $product_cats).") AND c.products_type_id = 1");
		$i = 1;
				
		while($i < 4)
		{
			$choosed_products = explode(",", $meal->{'course_'.$i});
			
			$output .= "<label>Course $i</label><select class='multiple' multiple size='3' name='course_".$i."[]' />\n";
			while($p = mysql_fetch_object($products)) 
			{
				(in_array($p->id, $choosed_products)) ? $select = "selected " : $select = "";
				$output .=  "<option value='".$p->id."' $select>".utf8_encode($p->name)."</option>\n";
			}
			$output .= "</select>\n";
			$output .= "<div style='position: relative; left: 400px; top: -100px; margin-bottom: -60px;'>maximal choosing for Customer <input type='text' name='course_".$i."_max' value='".$meal->{'course_'.$i.'_max'}."' /></div>";
			mysql_data_seek($products, 0);
			$i++;
		}
		
		$output .= "<input type='submit' name='submit' value='edit Meal'>";
		$output .= "<a href='index.php?action=manageMeals'><input type='button' value='back to overview'></a>";
		$output .= "</form>";
		return $output;
	}
	
	public function createCoupon()
	{
		$output = "";
		
		if(isset($_POST['submit']))
		{
			$code			= mysql_real_escape_string(utf8_decode(trim($_POST['code'])));
			$coupon_text	= mysql_real_escape_string(utf8_decode(trim($_POST['coupon_text'])));
			$price			= mysql_real_escape_string(utf8_decode(trim($_POST['price'])));
			$percent		= mysql_real_escape_string(utf8_decode(trim($_POST['percent'])));
			$product_id		= mysql_real_escape_string(utf8_decode(trim($_POST['product_id'])));
			$count			= mysql_real_escape_string(utf8_decode(trim($_POST['count'])));
						
			if($price != "" && $product_id != "" && isset($_POST['dailyCode'])) $sql = "INSERT INTO coupons SET code = '$code', coupon_text = '$coupon_text', price = '$price', products_id = '$product_id', dailyCode = '1', count = '$count'";
			if($percent != "" && $product_id != "" && isset($_POST['dailyCode'])) $sql = "INSERT INTO coupons SET code = '$code', coupon_text = '$coupon_text', percent = '$percent', products_id = '$product_id', dailyCode = '1', count = '$count'";
			if($price != "" && isset($_POST['dailyCode'])) $sql = "INSERT INTO coupons SET code = '$code', coupon_text = '$coupon_text', price = '$price', dailyCode = '1', count = '$count'";
			if($percent != "" && isset($_POST['dailyCode'])) $sql = "INSERT INTO coupons SET code = '$code', coupon_text = '$coupon_text', percent = '$percent', dailyCode = '1', count = '$count'";
			
			if($price != "" && $product_id != "") $sql = "INSERT INTO coupons SET code = '$code', coupon_text = '$coupon_text', price = '$price', products_id = '$product_id', count = '$count'";
			if($percent != "" && $product_id != "") $sql = "INSERT INTO coupons SET code = '$code', coupon_text = '$coupon_text', percent = '$percent', products_id = '$product_id', count = '$count'";
			if($price != "") $sql = "INSERT INTO coupons SET code = '$code', coupon_text = '$coupon_text', price = '$price', count = '$count'";
			if($percent != "") $sql = "INSERT INTO coupons SET code = '$code', coupon_text = '$coupon_text', percent = '$percent', count = '$count'";
			
			$insert = mysql_query($sql);
			if($insert) $output .= "<p>The Coupon is created</p>";
			else $output .= "<p>There was something wrong!</p>";
			
		}
		
		$products = mysql_query("SELECT * FROM products");
		
		$output .= "<h1>Create Coupon</h1>";
		$output .= "<form action='index.php?action=createCoupon' method='post'>";
		$output .= "<label>Code:</label><input type='text' name='code' />";
		$output .= "<label>Coupon Text:</label><input type='text' name='coupon_text' />";
		$output .= "<p>Please choose price OR percent! Both is not allowed!</p>";
		$output .= "<label>Price:</label><input type='text' name='price' />";
		$output .= "<label>Percent:</label><input type='text' name='percent' /><br /><br />";
		$output .= "<p>Please choose a product, if the discount is just for this one product! If the discount is for the whole order, leave this field empty!</p>";
		$output .= "<label>Product:</label><select name='product_id' />";
		$output .= "<option value=''>---</option>";
		while($product = mysql_fetch_object($products))
		{
			$output .= "<option value='$product->id'>".utf8_encode($product->name)."</option>";
		}
		
		$output .= "</select>";
		$output .= "<label>Count:</label><input type='text' name='count' />";
		$output .= "<label>dailyCode:</label><input type='checkbox' name='dailyCode' />";
		$output .= "<a href='index.php?action=manageCoupons'><input type='button' value='Back to overview' /></a>";
		$output .= "<input type='submit' name='submit' value='create Coupon' />";
		$output .= "</form>";
		
		return $output;
	}
	
	public function editCoupon($coupon_id)
	{
		$output = "";
		
		if(isset($_POST['submit']))
		{
			$code			= mysql_real_escape_string(utf8_decode(trim($_POST['code'])));
			$coupon_text	= mysql_real_escape_string(utf8_decode(trim($_POST['coupon_text'])));
			$price			= mysql_real_escape_string(utf8_decode(trim($_POST['price'])));
			$percent		= mysql_real_escape_string(utf8_decode(trim($_POST['percent'])));
			$product_id		= mysql_real_escape_string(utf8_decode(trim($_POST['product_id'])));
			$count			= mysql_real_escape_string(utf8_decode(trim($_POST['count'])));
			
			
			
			if($price != "" && $product_id != "" && isset($_POST['dailyCode'])) $sql = "UPDATE coupons SET code = '$code', coupon_text = '$coupon_text', price = '$price', products_id = '$product_id', dailyCode = '1', count = '$count' WHERE id = '$coupon_id'";
			if($percent != "" && $product_id != "" && isset($_POST['dailyCode'])) $sql = "UPDATE coupons SET code = '$code', coupon_text = '$coupon_text', percent = '$percent', products_id = '$product_id', dailyCode = '1', count = '$count' WHERE id = '$coupon_id'";
			if($price != "" && isset($_POST['dailyCode'])) $sql = "UPDATE coupons SET code = '$code', coupon_text = '$coupon_text', price = '$price', dailyCode = '1', count = '$count' WHERE id = '$coupon_id'";
			if($percent != "" && isset($_POST['dailyCode'])) $sql = "UPDATE coupons SET code = '$code', coupon_text = '$coupon_text', percent = '$percent', dailyCode = '1', count = '$count' WHERE id = '$coupon_id'";
			
			if($price != "" && $product_id != "") $sql = "UPDATE coupons SET code = '$code', coupon_text = '$coupon_text', price = '$price', products_id = '$product_id', count = '$count' WHERE id = '$coupon_id'";
			if($percent != "" && $product_id != "") $sql = "UPDATE coupons SET code = '$code', coupon_text = '$coupon_text', percent = '$percent', products_id = '$product_id', count = '$count' WHERE id = '$coupon_id'";
			if($price != "") $sql = "UPDATE coupons SET code = '$code', coupon_text = '$coupon_text', price = '$price', count = '$count' WHERE id = '$coupon_id'";
			if($percent != "") $sql = "UPDATE coupons SET code = '$code', coupon_text = '$coupon_text', percent = '$percent', count = '$count' WHERE id = '$coupon_id'";
			
			//echo $sql;
			
			$insert = mysql_query($sql);
			if($insert) $output .= "<p>The Coupon is edited!</p>";
			else $output .= "<p>There was something wrong!</p>";
			
		}
		$coupon = mysql_fetch_object(mysql_query("SELECT * FROM coupons WHERE id = '$coupon_id'"));
		
		$products = mysql_query("SELECT * FROM products");
		
		$output .= "<h1>Edit Coupon</h1>";
		$output .= "<form action='index.php?action=editCoupon&coupon_id=$coupon_id' method='post'>";
		$output .= "<label>Code:</label><input type='text' name='code' value='$coupon->code' />";
		$output .= "<label>Coupon Text:</label><input type='text' name='coupon_text' value='$coupon->coupon_text' />";
		$output .= "<p>Please choose price OR percent! Both is not allowed!</p>";
		$output .= "<label>Price:</label><input type='text' name='price' value='$coupon->price' />";
		$output .= "<label>Percent:</label><input type='text' name='percent' value='$coupon->percent' /><br /><br />";
		$output .= "<p>Please choose a product, if the discount is just for this one product! If the discount is for the whole order, leave this field empty!</p>";
		$output .= "<label>Product:</label><select name='product_id' />";
		$output .= "<option value=''>---</option>";
		while($product = mysql_fetch_object($products))
		{
			($coupon->products_id = $product->id) ? $select = 'selected ' : $select = '';
			$output .= "<option value='$product->id' $select>".utf8_encode($product->name)."</option>";
		}
		
		$output .= "</select>";
		$output .= "<label>Count:</label><input type='text' name='count' />";
		$output .= "<label>dailyCode:</label><input type='checkbox' name='dailyCode' />";
		$output .= "<a href='index.php?action=manageCoupons'><input type='button' value='Back to overview' /></a>";
		$output .= "<input type='submit' name='submit' value='edit Coupon' />";
		$output .= "</form>";
		
		return $output;
	}
	
	private function checked($int)
	{
		if($int == 0) return "";
		else return "checked";
	}
	
	public function handleAction($action, $get = null)
	{
		parent::handleAction($action, $get);
		
		switch ($action) {
			case 'login':
				return parent::createContent(parent::createLogin(), self::createMenu());
				break;			
			case 'articleManagement':
				return parent::createContent(self::createArticleManagement(), self::createMenu());
				break;
			case 'manageIngredients':
				return parent::createContent(self::manageIngredients(), self::createMenu());
				break;
			case 'manageAdditives':
				return parent::createContent(self::manageAdditives(), self::createMenu());
				break;
			case 'manageProducts':
				return parent::createContent(self::manageProducts(), self::createMenu());
				break;
			case 'manageMeals':
				return parent::createContent(self::manageMeals(), self::createMenu());
				break;
			case 'manageCoupons':
				return parent::createContent(self::manageCoupons(), self::createMenu());
				break;
			case 'manageStornoReason':
				return parent::createContent(self::manageStornoReason(), self::createMenu());
				break;
			case 'manageCategories':
				return parent::createContent(self::manageCategories(), self::createMenu());
				break;
			case 'createIngredient':
				return parent::createContent(self::createIngredient(), self::createMenu());
				break;
			case 'createAdditive':
				return parent::createContent(self::createAdditive(), self::createMenu());
				break;	
			case 'createProduct':
				return parent::createContent(self::createProduct(), self::createMenu());
				break;
			case 'createMeal':
				return parent::createContent(self::createMeal(), self::createMenu());
				break;
			case 'createStornoReason':
				return parent::createContent(self::createStornoReason(), self::createMenu());
				break;
			case 'createCoupon':
				return parent::createContent(self::createCoupon(), self::createMenu());
				break;
			case 'editProduct':
				return parent::createContent(self::editProduct($get['product_id']), self::createMenu());
				break;
			case 'editMeal':
				return parent::createContent(self::editMeal($get['meal_id']), self::createMenu());
				break;
			case 'editCoupon':
				return parent::createContent(self::editCoupon($get['coupon_id']), self::createMenu());
				break;
			default:
				// print error message
				break;
		}
	}

} /* end of class KitchenScreen */

?>