SELECT date(os1.created_at),
       SUM((super8_price - price)*quantity) AS investment
FROM `order` o
JOIN order_item oi ON oi.order_id = o.id
JOIN order_status os1 ON os1.order_id = o.id
LEFT JOIN order_status os2 ON os2.order_id = o.id
AND os1.id < os2.id
WHERE os2.id IS NULL
  AND os1.status = 'delivered'
  AND oi.is_available=1
GROUP BY date(os1.created_at)
ORDER BY os1.created_at DESC