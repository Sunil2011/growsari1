SELECT s.id AS store_id,
       a.id AS account_id,
       `a`.`username` AS `username`,
       s.name AS store_name,
       s.customer_name,
       CASE
           WHEN no_of_orders <= 2
                AND DATEDIFF(NOW(), o1.created_at) <=3 THEN 'New'
           WHEN no_of_orders <= 2
                AND DATEDIFF(NOW(), o1.created_at) <=6 THEN 'Uncertain'
           WHEN no_of_orders <= 2
                AND DATEDIFF(NOW(), o1.created_at) >=7 THEN 'Uncomitted'
           WHEN no_of_orders >=3
                AND DATEDIFF(NOW(), o1.created_at) <=3 THEN 'Loyal'
           WHEN no_of_orders >=3
                AND DATEDIFF(NOW(), o1.created_at) <=6 THEN 'Unsecure'
           WHEN no_of_orders >=3
                AND DATEDIFF(NOW(), o1.created_at) >=7 THEN 'Critical'
       END AS Segment,
       CONCAT(s.point_y, ', ', s.point_x) AS GPS_coordinates,
       `s`.`contact_no` AS `contact_no`,
       ad.app_version,
       ad.updated_at AS `last_login`,
       IF(`gst`.`id` IS NULL,'N', 'Y') AS SMS_registered,
       no_of_orders,
      
       (SELECT days_after/thirty_days_orders AS order_interval
      FROM
        ( SELECT signup_time,
                 si.id AS store_id,
                 count(oi.id) AS thirty_days_orders,
                 DATEDIFF(NOW(), if(si.signup_time > DATE_SUB(NOW(), INTERVAL 30 DAY), si.signup_time, DATE_SUB(NOW(), INTERVAL 30 DAY))) AS days_after
          FROM store si
          JOIN store_warehouse_shipper swsi ON swsi.store_id = si.id
          LEFT JOIN `order` oi ON oi.associate_id = swsi.id
          JOIN `account` ai ON ai.id =si.account_id
          WHERE `si`.`signup_time` IS NOT NULL
            AND `si`.`signup_time` != '0000-00-00 00:00:00'
            AND (oi.id IS NULL
                 OR oi.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW())
          GROUP BY si.id ASC
          ORDER BY oi.id DESC) x  WHERE x.store_id=s.id) as order_interval,

       o1.created_at AS last_order_time,
       sv1.created_at AS last_conacted_at,
       sv1.order_barrier,
       CONCAT(s.locality, ', ', s.city) AS barangay,
       sv1.comments,
       sv1.contacted_by AS contacted_by
FROM `loyalty_point` AS `lp`
INNER JOIN `account` AS `a` ON `a`.`id` = `lp`.`account_id`
LEFT JOIN `account_device` AS `ad` ON `ad`.`account_id` = `a`.`id`
JOIN `store` AS `s` ON `s`.`account_id` = `a`.`id`
JOIN `store_warehouse_shipper` AS `sws` ON `sws`.`store_id` = `s`.`id`
JOIN `order` AS `o1` ON `o1`.`associate_id` = `sws`.`id`
LEFT JOIN `order` o2 ON o2.associate_id = sws.id
AND o1.id < o2.id
LEFT JOIN
  (SELECT s1.id,
          count(oi.id) AS no_of_orders
   FROM `store` s1
   JOIN store_warehouse_shipper sws1 ON sws1.store_id = s1.id
   JOIN `order` oi ON oi.associate_id = sws1.id
   GROUP BY s1.id) ox ON ox.id = s.id
LEFT JOIN `globe_store_token` AS `gst` ON (gst.subscriber_number = s.contact_no
                                           OR CONCAT('0', gst.subscriber_number) = s.contact_no)
LEFT JOIN store_visit sv1 ON sv1.store_id = s.id
LEFT JOIN store_visit sv2 ON sv2.store_id = s.id
AND sv1.id < sv2.id
WHERE sv2.id IS NULL
  AND o2.id IS NULL
GROUP BY `a`.`id`
ORDER BY `a`.`id` DESC