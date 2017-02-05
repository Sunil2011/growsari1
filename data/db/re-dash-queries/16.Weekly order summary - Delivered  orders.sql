
SELECT STR_TO_DATE(CONCAT(YEARWEEK(os1.created_at, 0), ' Sunday'), '%X%V %W') AS week_date,
       count(o.id) AS no_of_orders,
       SUM(o.initial_order_value) AS initial_order_value,  
       SUM(o.net_amount + o.loyalty_points_used) AS final_order_value,
       SUM(o.amount_collected) AS amount_collected,
	   SUM(o.loyalty_points_used) AS loyalty_points_used,
       SUM(o.returned_item_amount) AS returned_item_amount,
       SUM(o.initial_order_value - (o.net_amount + o.loyalty_points_used)) AS out_of_stock           
FROM `order` AS o
JOIN order_status os1 ON os1.order_id = o.id
LEFT JOIN order_status os2 ON os2.order_id = o.id
AND os1.id < os2.id
WHERE os2.id IS NULL
  AND os1.status = 'delivered'
GROUP BY week_date