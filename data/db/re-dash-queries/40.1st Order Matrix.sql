SELECT s.id AS store_id,
       a.id AS account_id,
       `a`.`username` AS `username`,
       s.name AS store_name,
       s.customer_name,
       s.address,
       IF((`s`.`first_loggedin_time` IS NULL
           OR `s`.`first_loggedin_time` = '0000-00-00 00:00:00'), 'Sign Up', 'Download
') AS segnment,
	   CONCAT(s.point_y, ', ', s.point_x) AS GPS_coordinates,
       `s`.`contact_no` AS `contact_no`,
       ad.app_version,
       ad.updated_at AS `last_login`,
	   IF(`gst`.`id` IS NULL,'N', 'Y') AS SMS_registered,
       CASE SUBSTRING(a.username,1, 4)
           WHEN 'sm1u' THEN 'Facebook'
           WHEN 'sm45' THEN ssa.username
           WHEN 'sm46' THEN ssa.username
           WHEN 'sm47' THEN ssa.username
           WHEN 'sm48' THEN ssa.username
           WHEN 'sm49' THEN ssa.username
           WHEN 'sm55' THEN 'Independent'
           ELSE 'Independent'
       END AS singup_mode,
	   s.signup_time AS date_of_signup,
       s.first_loggedin_time AS date_of_first_login,
	   sv1.created_at AS last_conacted_at,
       sv1.order_barrier,
       sv1.comments,
       sv1.contacted_by AS contacted_by
FROM `loyalty_point` AS `lp`
INNER JOIN `account` AS `a` ON `a`.`id` = `lp`.`account_id`
LEFT JOIN `account_device` AS `ad` ON `ad`.`account_id` = `a`.`id`
INNER JOIN `store` AS `s` ON `s`.`account_id` = `a`.`id`
INNER JOIN `store_warehouse_shipper` AS `sws` ON `sws`.`store_id` = `s`.`id`
INNER JOIN `store_salesperson` AS `ss` ON `ss`.`store_id` = `s`.`id`
INNER JOIN `account` AS `ssa` ON `ssa`.`id` = `ss`.`salesperson_account_id`
LEFT JOIN `order` AS `o` ON `o`.`associate_id` = `sws`.`id`
LEFT JOIN `globe_store_token` AS `gst` ON (gst.subscriber_number = s.contact_no
                                           OR CONCAT('0', gst.subscriber_number) = s.contact_no)
LEFT JOIN store_visit sv1 ON sv1.store_id = s.id
LEFT JOIN store_visit sv2 ON sv2.store_id = s.id
AND sv1.id < sv2.id
WHERE sv2.id IS NULL
  AND `s`.`signup_time` IS NOT NULL
  AND `s`.`signup_time` != '0000-00-00 00:00:00'
  AND `o`.`id` IS NULL
GROUP BY `a`.`id`
ORDER BY `a`.`id` DESC