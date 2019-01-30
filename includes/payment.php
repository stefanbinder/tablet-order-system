<?php
$frontend->order->update();
?>
<div class="payment_wrapper">
    <div class="payment" id="payment">
        <form class="validate" action ="includes/pay_now.php" onsubmit="return confirm('Wirklich bezahlen?')" method="post">
    	<fieldset>
        	<legend class="white"><?= _YOUR_CONSUME;?></legend>
        	
            <div class="consume">
				<?= $frontend->order->displayProducts();?>
            </div>
            
        	
			<?php
                if($frontend->order->coupon != NULL) echo '<div class="used_coupon"><span>'._COUPON_BENEFIT.'</span><br />'.$frontend->order->coupon->getDiscount().'</div>';
            ?>
            
            <div class="total">
                <div class="text"><?= _INVOICE_TOTAL;?></div>
                <div class="amount">&euro; <?= $frontend->order->getPrice();?></div>    
            </div>
        </fieldset>
        <fieldset class="bottom_border">
            <legend class="white">Wählen Sie bitte eine Zahlungsmethode</legend>
            <div class="paymentMethods">
            <?php
                $payment_methods = mysql_query("SELECT *
                                                  FROM payment_method 
                                                 WHERE published = '1' 
                                                   AND languages_id = '".$frontend->language."' 
                                              ORDER BY ordering ASC");
                
                while($method = mysql_fetch_object($payment_methods))
                {
					if($method->id == 1 || $method->id == 4) $checked = "checked";
					else $checked = "";
                    echo '<input title="Bitte wählen Sie eine Zahlungsart" type="radio" class="required" name="payment_method" value="'.$method->id.'" id="p'.$method->id.'" '.$checked.' /><label for="p'.$method->id.'">'.$method->name.'</label>';
                }
            ?>
            </div>
        </fieldset>
        <br />
        <div class="buttons">
        	<button class="button_secondary lightbox" href="includes/load_neighborsBill.php?table_id=<?= $frontend->order->table;?>&lightbox[width]=500&lightbox[height]=440"><?= _NEIGHBORS_BILL;?></button>
            <button class="button_secondary lightbox" href="includes/load_coupon.php?lightbox[width]=500&lightbox[height]=440"><?= _BUTTON_COUPON;?></button>
            
            <br /><br />
                        
        	<a href="karte.php"><input class="foodmenu_top_button" type="button" value="<?= _BUTTON_BACK_TO_CARD;?>" /></a>
        	<input type="submit" name="pay" value="<?= _BUTTON_PAY_NOW;?>" />
        </div>
        
    	</form>
    </div>
</div>