################### view_orders

CREATE OR REPLACE VIEW view_orders AS
SELECT orders.id `orders_id`, orders.pay_intention, orders.paid, orders.seat_id, seat.name `seat_name`, desk.id `table_id`, desk.name `table_name`,  ohp.status_id `status`, ohp.time `time`, ohp.products_id, products.name `products_name`, pm.name `payment_method`, categories.products_type_id , date_format( 
        date_sub(
            ohp.time, 
            INTERVAL (extract(MINUTE from ohp.time) % 1) MINUTE
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
WHERE orders.paid = 0 AND ohp.time < SYSDATE()
ORDER BY bar_time


################### view_paid_orders

CREATE OR REPLACE VIEW view_paid_orders AS
SELECT orders.id `orders_id`, orders.pay_intention, orders.paid, orders.seat_id, seat.name `seat_name`, desk.id `table_id`, desk.name `table_name`,  ohp.status_id `status`, ohp.time `time`, ohp.products_id, products.name `products_name`, pm.name `payment_method`, categories.products_type_id , date_format( 
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

############## Deleting variable tables  => CONTENT

DELETE FROM invoices_has_items;
DELETE FROM invoices;

DELETE FROM storno;

DELETE FROM orders_has_products_has_additives;
DELETE FROM orders_has_products;
DELETE FROM orders;



############## Deleting static tables	=> CONTENT

DELETE FROM products_has_additives;
DELETE FROM products_has_ingredients;

DELETE FROM storno_reason;
DELETE FROM coupons;

DELETE FROM meals;
DELETE FROM user;

DELETE FROM seat;
DELETE FROM desk;

DELETE FROM additives;
DELETE FROM ingredients;
DELETE FROM products;
DELETE FROM categories;


###############


DROP TABLE 
`products_has_additives` ,
`products_has_ingredients` ,
`orders_has_products_has_additives` ,
`orders_has_products` ,
`additives` ,
`ingredients` ,
`invoices_has_items` ,
`invoices` ,
`orders` ,
`meals` ,
`desk` ,
`seat` ,
`products` ,
`status` ,
`storno` ,
`storno_reason` ,
`coupons` ,
`categories` ,
`orders_log` ,
`payment_method` ,
`products_type` ,
`reservations` ,
`languages` ,
`user` ,
`waiters_call` ;
DROP VIEW
`view_orders`,
`view_paid_orders`;





*/

?>