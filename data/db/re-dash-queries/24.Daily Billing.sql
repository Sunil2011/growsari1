
SELECT DATE(os1.created_at) AS date,
       count(o.id) AS no_of_orders,
       SUM(o.amount_collected) AS amount_collected
FROM `order` AS o
JOIN order_status os1 ON os1.order_id = o.id
LEFT JOIN order_status os2 ON os2.order_id = o.id
AND os1.id < os2.id
WHERE os2.id IS NULL
  AND os1.status = 'delivered'
GROUP BY date(os1.created_at)
ORDER BY os1.created_at DESC