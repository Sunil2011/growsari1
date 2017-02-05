
SELECT o.id,
       p.id as productid,
       oi.id as item_id,
       p.sku,
       p.item_code,
       p.is_deleted
FROM `order` o
JOIN order_item oi ON oi.order_id = o.id
JOIN product p ON oi.product_id = p.id
WHERE p.id IN
    (SELECT id
     FROM product
     WHERE item_code like '%deleted%')
ORDER BY p.id asc