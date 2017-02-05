
SELECT *
FROM `product`
WHERE item_code IS NULL
  OR item_code = '' 
