####################################
##
## STEP 1
## create all columns
####################################

ALTER TABLE `additives` ADD `name_1` VARCHAR( 80 ) NOT NULL AFTER `name` ,
ADD `name_2` VARCHAR( 80 ) NOT NULL AFTER `name_1` ,
ADD `name_3` VARCHAR( 80 ) NOT NULL AFTER `name_2` ,
ADD `name_4` VARCHAR( 80 ) NOT NULL AFTER `name_3` ,
ADD `name_5` VARCHAR( 80 ) NOT NULL AFTER `name_4` ,
ADD `name_6` VARCHAR( 80 ) NOT NULL AFTER `name_5` ,
ADD `name_7` VARCHAR( 80 ) NOT NULL AFTER `name_6` ,
ADD `name_8` VARCHAR( 80 ) NOT NULL AFTER `name_7` ,
ADD `name_9` VARCHAR( 80 ) NOT NULL AFTER `name_8` ,
ADD `name_10` VARCHAR( 80 ) NOT NULL AFTER `name_9`;




ALTER TABLE `categories` ADD `name_1` VARCHAR( 80 ) NOT NULL AFTER `name` ,
ADD `name_2` VARCHAR( 80 ) NOT NULL AFTER `name_1` ,
ADD `name_3` VARCHAR( 80 ) NOT NULL AFTER `name_2` ,
ADD `name_4` VARCHAR( 80 ) NOT NULL AFTER `name_3` ,
ADD `name_5` VARCHAR( 80 ) NOT NULL AFTER `name_4` ,
ADD `name_6` VARCHAR( 80 ) NOT NULL AFTER `name_5` ,
ADD `name_7` VARCHAR( 80 ) NOT NULL AFTER `name_6` ,
ADD `name_8` VARCHAR( 80 ) NOT NULL AFTER `name_7` ,
ADD `name_9` VARCHAR( 80 ) NOT NULL AFTER `name_8` ,
ADD `name_10` VARCHAR( 80 ) NOT NULL AFTER `name_9`;




ALTER TABLE `ingredients` ADD `name_1` VARCHAR( 80 ) NOT NULL AFTER `name` ,
ADD `name_2` VARCHAR( 80 ) NOT NULL AFTER `name_1` ,
ADD `name_3` VARCHAR( 80 ) NOT NULL AFTER `name_2` ,
ADD `name_4` VARCHAR( 80 ) NOT NULL AFTER `name_3` ,
ADD `name_5` VARCHAR( 80 ) NOT NULL AFTER `name_4` ,
ADD `name_6` VARCHAR( 80 ) NOT NULL AFTER `name_5` ,
ADD `name_7` VARCHAR( 80 ) NOT NULL AFTER `name_6` ,
ADD `name_8` VARCHAR( 80 ) NOT NULL AFTER `name_7` ,
ADD `name_9` VARCHAR( 80 ) NOT NULL AFTER `name_8` ,
ADD `name_10` VARCHAR( 80 ) NOT NULL AFTER `name_9`;




ALTER TABLE `payment_method` ADD `name_1` VARCHAR( 80 ) NOT NULL AFTER `name` ,
ADD `name_2` VARCHAR( 80 ) NOT NULL AFTER `name_1` ,
ADD `name_3` VARCHAR( 80 ) NOT NULL AFTER `name_2` ,
ADD `name_4` VARCHAR( 80 ) NOT NULL AFTER `name_3` ,
ADD `name_5` VARCHAR( 80 ) NOT NULL AFTER `name_4` ,
ADD `name_6` VARCHAR( 80 ) NOT NULL AFTER `name_5` ,
ADD `name_7` VARCHAR( 80 ) NOT NULL AFTER `name_6` ,
ADD `name_8` VARCHAR( 80 ) NOT NULL AFTER `name_7` ,
ADD `name_9` VARCHAR( 80 ) NOT NULL AFTER `name_8` ,
ADD `name_10` VARCHAR( 80 ) NOT NULL AFTER `name_9`;




ALTER TABLE `products` ADD `name_1` VARCHAR( 80 ) NOT NULL AFTER `name` ,
ADD `name_2` VARCHAR( 80 ) NOT NULL AFTER `name_1` ,
ADD `name_3` VARCHAR( 80 ) NOT NULL AFTER `name_2` ,
ADD `name_4` VARCHAR( 80 ) NOT NULL AFTER `name_3` ,
ADD `name_5` VARCHAR( 80 ) NOT NULL AFTER `name_4` ,
ADD `name_6` VARCHAR( 80 ) NOT NULL AFTER `name_5` ,
ADD `name_7` VARCHAR( 80 ) NOT NULL AFTER `name_6` ,
ADD `name_8` VARCHAR( 80 ) NOT NULL AFTER `name_7` ,
ADD `name_9` VARCHAR( 80 ) NOT NULL AFTER `name_8` ,
ADD `name_10` VARCHAR( 80 ) NOT NULL AFTER `name_9`;


ALTER TABLE `products` ADD `subname_1` VARCHAR( 250 ) NOT NULL AFTER `subname` ,
ADD `subname_2` VARCHAR( 250 ) NOT NULL AFTER `subname_1` ,
ADD `subname_3` VARCHAR( 250 ) NOT NULL AFTER `subname_2` ,
ADD `subname_4` VARCHAR( 250 ) NOT NULL AFTER `subname_3` ,
ADD `subname_5` VARCHAR( 250 ) NOT NULL AFTER `subname_4` ,
ADD `subname_6` VARCHAR( 250 ) NOT NULL AFTER `subname_5` ,
ADD `subname_7` VARCHAR( 250 ) NOT NULL AFTER `subname_6` ,
ADD `subname_8` VARCHAR( 250 ) NOT NULL AFTER `subname_7` ,
ADD `subname_9` VARCHAR( 250 ) NOT NULL AFTER `subname_8` ,
ADD `subname_10` VARCHAR( 250 ) NOT NULL AFTER `subname_9`;


ALTER TABLE `products` ADD `allergy_hint_1` TEXT NOT NULL AFTER `allergy_hint` ,
ADD `allergy_hint_2` TEXT NOT NULL AFTER `allergy_hint_1` ,
ADD `allergy_hint_3` TEXT NOT NULL AFTER `allergy_hint_2` ,
ADD `allergy_hint_4` TEXT NOT NULL AFTER `allergy_hint_3` ,
ADD `allergy_hint_5` TEXT NOT NULL AFTER `allergy_hint_4` ,
ADD `allergy_hint_6` TEXT NOT NULL AFTER `allergy_hint_5` ,
ADD `allergy_hint_7` TEXT NOT NULL AFTER `allergy_hint_6` ,
ADD `allergy_hint_8` TEXT NOT NULL AFTER `allergy_hint_7` ,
ADD `allergy_hint_9` TEXT NOT NULL AFTER `allergy_hint_8` ,
ADD `allergy_hint_10` TEXT NOT NULL AFTER `allergy_hint_9`;

####################################
##
## STEP 2
## copy name to name_1
####################################

UPDATE additives SET name_1 = name;
UPDATE categories SET name_1 = name;
UPDATE ingredients SET name_1 = name;
UPDATE payment_method SET name_1 = name;
UPDATE products SET name_1 = name;
UPDATE products SET subname_1 = subname;
UPDATE products SET allergy_hint_1 = allergy_hint;

####################################
##
## STEP 3
## Dummy Infos (OR NOT!!)
####################################


UPDATE additives SET name_2 = concat(name, " en");
UPDATE categories SET name_2 = concat(name, " en");
UPDATE ingredients SET name_2 = concat(name, " en");
UPDATE payment_method SET name_2 = concat(name, " en");
UPDATE products SET name_2 = concat(name, " en");
UPDATE products SET subname_2 = concat(subname, " en");
UPDATE products SET allergy_hint_2 = concat(allergy_hint, " en");


####################################
##
## STEP 4
## Replace dummy infos with real infos -> händisch oder automatisch?
####################################




####################################
##
## STEP 5
## Delete duplicated entries
####################################

DELETE FROM categories WHERE languages_id = 2;
DELETE FROM additives WHERE languages_id = 2;
DELETE FROM ingredients WHERE languages_id = 2;
DELETE FROM payment_method WHERE languages_id = 2;
DELETE FROM products WHERE languages_id = 2;


####################################
##
## STEP 6
## Don't forgot to update the view ;-)
####################################

CREATE OR REPLACE VIEW view_orders AS
SELECT orders.id `orders_id` , orders.pay_intention, orders.paid, orders.seat_id, seat.name `seat_name` , desk.id `table_id` , desk.name `table_name` , ohp.status_id `status` , ohp.time `time` , ohp.products_id, products.name_1 `products_name` , pm.name_1 `payment_method` , categories.products_type_id, date_format( date_sub( ohp.time, INTERVAL( extract(
MINUTE FROM ohp.time ) %1 )
MINUTE ) , "%Y-%m-%d %H:%i" ) AS bar_time
FROM orders
JOIN orders_has_products `ohp` ON ohp.orders_id = orders.id
JOIN seat ON seat.id = orders.seat_id
JOIN desk ON desk.id = seat.table_id
JOIN products ON ohp.products_id = products.id
JOIN categories ON categories.id = products.categories_id
LEFT JOIN payment_method `pm` ON pm.id = orders.payment_method
WHERE orders.paid =0
AND ohp.time < SYSDATE( )
ORDER BY bar_time


CREATE OR REPLACE VIEW view_paid_orders AS
SELECT orders.id `orders_id`, orders.pay_intention, orders.paid, orders.seat_id, seat.name `seat_name`, desk.id `table_id`, desk.name `table_name`,  ohp.status_id `status`, ohp.time `time`, ohp.products_id, products.name_1 `products_name`, pm.name_1 `payment_method`, categories.products_type_id , date_format( 
        date_sub(
            ohp.time, 
            INTERVAL (extract(MINUTE from ohp.time) % 2) MINUTE
        ), 
        "%Y-%m-%d %H:%i"
    ) as  bar_time
FROM orders
JOIN orders_has_products `ohp` ON ohp.orders_id = orders.id
JOIN seat ON seat.id = orders.seat_id
JOIN desk ON desk.id = seat.table_id
JOIN products ON ohp.products_id = products.id
JOIN categories ON categories.id = products.categories_id
LEFT JOIN payment_method `pm` ON pm.id = orders.payment_method
WHERE orders.paid = 1
ORDER BY bar_time

####################################
##
## STEP 7
## delete old name columns
####################################

ALTER TABLE `additives`
  DROP `name`;

ALTER TABLE `categories`
  DROP `name`;

ALTER TABLE `ingredients`
  DROP `name`;

ALTER TABLE `payment_method`
  DROP `name`;

ALTER TABLE `products`
  DROP `name`,
  DROP `subname`,
  DROP `allergy_hint`;
  

