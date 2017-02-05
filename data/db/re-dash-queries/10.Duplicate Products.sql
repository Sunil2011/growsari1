
SELECT * 
FROM product
WHERE item_code
IN (
  SELECT item_code
  FROM product
  WHERE is_deleted =0
  GROUP BY item_code
  HAVING COUNT( item_code ) > 1
)
ORDER BY  `product`.`item_code` ASC 
