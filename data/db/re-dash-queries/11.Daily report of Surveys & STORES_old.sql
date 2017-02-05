

 -- OLD query for today data

SELECT
  (SELECT count(*)
   FROM survey) AS total_surveys,

  (SELECT count(*)
   FROM store
   WHERE signup_time IS NOT NULL
     AND signup_time != '0000-00-00 00:00:00') AS total_signups,

  (SELECT count(DISTINCT s.id)
   FROM `store` s
   JOIN store_warehouse_shipper sws ON sws.store_id = s.id
   JOIN `order` o ON o.associate_id = sws.id) AS stores_who_have_ever_ordered,

  (SELECT count(DISTINCT s.id)
   FROM `store` s
   JOIN store_warehouse_shipper sws ON sws.store_id = s.id
   JOIN `order` o ON o.associate_id = sws.id
   WHERE o.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 14 DAY) AND NOW()) AS stores_who_have_ordered_last_2weeks,

  (SELECT count(DISTINCT s.id)
   FROM `store` s
   JOIN store_warehouse_shipper sws ON sws.store_id = s.id
   JOIN `order` o ON o.associate_id = sws.id
   WHERE o.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()) AS stores_who_have_ordered_last_week