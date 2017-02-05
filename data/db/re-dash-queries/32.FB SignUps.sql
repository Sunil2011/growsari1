SELECT a.username,
       s.name AS store_name,
       s.customer_name,
       s.contact_no,
       s.signup_time,
       s.first_loggedin_time,
       no_of_orders,
       o1.created_at AS last_order_time
FROM store s
JOIN account a ON a.id = s.account_id
JOIN store_salesperson ss ON ss.store_id = s.id
JOIN store_warehouse_shipper sws ON sws.store_id = s.id
LEFT JOIN
  (SELECT s1.id,
          count(o.id) AS no_of_orders
   FROM `store` s1
   JOIN store_warehouse_shipper sws1 ON sws1.store_id = s1.id
   JOIN `order` o ON o.associate_id = sws1.id
   GROUP BY s1.id) ox ON ox.id = s.id
LEFT JOIN `order` o1 ON o1.associate_id = sws.id
LEFT JOIN `order` o2 ON o2.associate_id = sws.id AND o1.id < o2.id
WHERE ss.salesperson_account_id = 1
  AND (o1.id IS NULL OR o2.id IS NULL)
GROUP BY s.id