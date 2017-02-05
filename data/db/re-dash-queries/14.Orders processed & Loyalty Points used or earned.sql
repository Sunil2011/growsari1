
SELECT o.id as order_id, a.username, o.net_amount, o.amount_collected, os1.created_at as delivered_at, o.loyalty_points_used, o.loyalty_points_earn
FROM `order` o
JOIN store_warehouse_shipper sws ON sws.id = o.associate_id
JOIN store s ON s.id = sws.store_id
JOIN account a ON a.id = s.account_id
JOIN order_status os1 ON os1.order_id = o.id
LEFT JOIN order_status os2 ON os2.order_id = o.id
AND os1.id < os2.id
WHERE os2.id IS NULL
  AND os1.status = 'delivered'  
order by  o.id, os1.created_at