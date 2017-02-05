
SELECT p.sku,
       p.item_code,
       p.super8_name,
       p.super8_price,
       p.price
FROM `order` o
JOIN order_item oi ON oi.order_id = o.id
JOIN product p ON oi.product_id = p.id
WHERE is_locked=1 AND is_deleted=0
GROUP BY p.id