
SELECT o.id,
       p.id as productid,
       oi.id as item_id,
       p.sku,
       p.item_code,
       p.is_deleted
FROM `order` o
JOIN order_item oi ON oi.order_id = o.id
JOIN product p ON oi.product_id = p.id
WHERE p.is_deleted=1
ORDER BY p.id asc