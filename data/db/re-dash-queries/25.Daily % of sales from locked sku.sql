
SELECT *, (locked_sku_sales/total_sales)*100 AS percentage
FROM
  (SELECT date(o.created_at) as date,
          SUM(oi.price*oi.quantity) AS total_sales,
          SUM(IF(p.is_locked, oi.price*oi.quantity, NULL)) AS locked_sku_sales
   FROM `order` o
   JOIN order_item oi ON oi.order_id = o.id
   JOIN product p ON oi.product_id = p.id
   GROUP BY date(o.created_at)
   ORDER BY o.created_at DESC) a