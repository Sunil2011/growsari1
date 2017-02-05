
SELECT p.sku,
       p.item_code,
       p.super8_name,
       p.price,
       COUNT(p.id) AS no_of_orders_used,
       CAST(SUM(oi.requested_quantity) as unsigned) AS total_quantity_missing,
       CAST(SUM(IF (oi.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 28 DAY) AND NOW(), oi.requested_quantity, NULL)) as unsigned) AS qunatity_missing_last_four_week,
	   CAST(SUM(IF (oi.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW(), oi.requested_quantity, NULL)) as unsigned) AS qunatity_missing_last_week
FROM `order` o
JOIN order_item oi ON oi.order_id = o.id
JOIN product p ON oi.product_id = p.id
WHERE oi.is_available = 0
GROUP BY p.id
ORDER BY total_quantity_missing DESC, no_of_orders_used DESC  LIMIT 50