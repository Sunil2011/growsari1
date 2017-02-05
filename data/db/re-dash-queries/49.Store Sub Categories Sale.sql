SELECT s.name AS store_name,
       a.username AS username,
       s.customer_name,
       c.name,
       no_of_orders,
       SUM(oi.net_amount) AS sales
FROM `store` AS `s`
INNER JOIN `account` AS `a` ON `a`.`id` = `s`.`account_id`
LEFT JOIN
  (SELECT s1.id,
          count(oii.id) AS no_of_orders
   FROM `store` s1
   JOIN store_warehouse_shipper sws1 ON sws1.store_id = s1.id
   JOIN `order` oii ON oii.associate_id = sws1.id
   GROUP BY s1.id) ox ON ox.id = s.id
LEFT JOIN `store_warehouse_shipper` AS `sws` ON `sws`.`store_id` = `s`.`id`
LEFT JOIN `order` AS `o` ON `o`.`associate_id` = `sws`.`id`
LEFT JOIN `order_item` AS `oi` ON `oi`.`order_id` = `o`.`id`
LEFT JOIN `product` AS `p` ON `oi`.`product_id` = `p`.`id`
LEFT JOIN `category` AS `c` ON `p`.`category_id` = `c`.`id`
WHERE `o`.`id` IS NOT NULL
GROUP BY `p`.`category_id`,
         `s`.`id`
ORDER BY `s`.`id` DESC,
         sales DESC