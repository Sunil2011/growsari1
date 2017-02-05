
SELECT s.name AS store_name,
       a.username,
       count(o.id) AS no_of_orders
FROM `store` s
JOIN store_warehouse_shipper sws ON sws.store_id = s.id
JOIN `order` o ON o.associate_id = sws.id
JOIN `account` a ON a.id = s.account_id
GROUP BY s.id
ORDER BY no_of_orders DESC LIMIT 10