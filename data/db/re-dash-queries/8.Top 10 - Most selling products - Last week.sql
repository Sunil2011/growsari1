
SELECT COUNT(p.id) AS no_of_orders_used,
       p.sku,
       p.item_code,
       p.super8_name,
       p.price
FROM `order` o
JOIN order_item oi ON oi.order_id = o.id
JOIN product p ON oi.product_id = p.id
WHERE o.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()
GROUP BY p.id
ORDER BY no_of_orders_used DESC LIMIT 10