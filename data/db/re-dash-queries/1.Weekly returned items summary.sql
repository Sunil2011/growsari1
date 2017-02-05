
SELECT STR_TO_DATE(CONCAT(YEARWEEK(ori.created_at, 0), ' Sunday'), '%X%V %W') AS week_date,
       o1.final_order_value,
	   SUM(oi.price * ori.quantity) total_returned,
       SUM(IF(ori.reason="Item missing from package",oi.price * ori.quantity,null)) items_missing,
	   SUM(IF(ori.reason="Not enough money.",oi.price * ori.quantity,null)) not_enough_money,
	   SUM(IF(ori.reason="Item damaged",oi.price * ori.quantity,null)) item_damaged,
	   SUM(IF(ori.reason="Item close to/crossed expiry date.",oi.price * ori.quantity,null)) close_to_expiry
FROM `order` AS o
JOIN order_item oi ON oi.order_id = o.id
JOIN order_returned_item ori ON ori.item_id = oi.id
JOIN (
  SELECT STR_TO_DATE(CONCAT(YEARWEEK(os1.created_at, 0), ' Sunday'), '%X%V %W') AS week_date,
       SUM(o.initial_order_value) AS initial_order_value,  
       SUM(o.net_amount + o.loyalty_points_used) AS final_order_value         
  FROM `order` AS o
  JOIN order_status os1 ON os1.order_id = o.id
  LEFT JOIN order_status os2 ON os2.order_id = o.id
  AND os1.id < os2.id
  WHERE os2.id IS NULL
    AND os1.status = 'delivered'
  GROUP BY week_date
) o1 on o1.week_date = STR_TO_DATE(CONCAT(YEARWEEK(ori.created_at, 0), ' Sunday'), '%X%V %W')
GROUP BY week_date

