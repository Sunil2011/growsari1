
SELECT count(*) AS orders
FROM `order` o
JOIN order_status os1 ON os1.order_id = o.id
LEFT JOIN order_status os2 ON os2.order_id = o.id
AND os1.id < os2.id
WHERE os2.id IS NULL
  AND os1.status = 'delivered';

