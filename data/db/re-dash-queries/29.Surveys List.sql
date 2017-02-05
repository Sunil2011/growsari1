SELECT a.username,
       sta.username AS strore_username,
       no_of_orders,
       s.*
FROM survey s
JOIN account a ON a.id = s.account_id
LEFT JOIN store st ON s.store_id = st.id
LEFT JOIN
  ( SELECT s1.id,
           count(o.id) AS no_of_orders
   FROM `store` s1
   JOIN store_warehouse_shipper sws ON sws.store_id = s1.id
   JOIN `order` o ON o.associate_id = sws.id
   GROUP BY s1.id) ox ON ox.id = st.id
LEFT JOIN account sta ON sta.id = st.account_id
WHERE s.is_deleted=0
ORDER BY id DESC