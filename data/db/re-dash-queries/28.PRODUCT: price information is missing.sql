
SELECT id,
       item_code,
       super8_name,
       sku,
       super8_price,
       price
FROM `product`
WHERE `is_deleted` = 0
  AND `super8_price` = 0
ORDER BY id