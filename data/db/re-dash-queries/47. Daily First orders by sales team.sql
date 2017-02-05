
SELECT d.date AS date,
       count(if(a.username='emilyn@growsari.com', 1, NULL)) AS emilyn,
       count(if(a.username='enrico@growsari.com', 1, NULL)) AS enrico,
       count(if(a.username='joshua@growsari.com', 1, NULL)) AS joshua,
       count(if(a.username='kenneth@growsari.com', 1, NULL)) AS kenneth,
       count(if(a.username='royce@growsari.com', 1, NULL)) AS jolo,
       count(if(a.username='salesperson@growsari.com', 1, NULL)) AS FB,
       count(if(a.username='salesperson_app@growsari.com', 1, NULL)) AS SELF
FROM `dates` d
LEFT JOIN `order` o ON date(o.created_at) = d.date
LEFT JOIN store_warehouse_shipper sws ON sws.id = o.associate_id
LEFT JOIN store_salesperson ss ON ss.store_id=sws.store_id
LEFT JOIN account a ON a.id = ss.salesperson_account_id
WHERE date BETWEEN '2016-06-30' AND NOW()
  AND (o.id IS NULL
       OR o.id =
         (SELECT min(id)
          FROM `order` o1
          WHERE o1.associate_id = o.associate_id))
GROUP BY date(d.date)
ORDER BY date(d.date) DESC