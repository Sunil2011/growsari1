SELECT a.username,
  (
    SELECT COUNT( * ) AS count
    FROM survey s
    WHERE s.is_deleted =0 AND a.id = s.account_id
   ) as survey, 

  (
     SELECT COUNT( * ) AS count
     FROM store s 
     JOIN store_salesperson ss1 ON ss1.store_id = s.id
     WHERE a.id = ss1.salesperson_account_id AND signup_time IS NOT NULL  AND signup_time != '0000-00-00 00:00:00' AND s.is_deleted =0
   ) as ids_issued,

   (
     SELECT COUNT( * ) AS count
     FROM store s 
     JOIN store_salesperson ss1 ON ss1.store_id = s.id
     WHERE a.id = ss1.salesperson_account_id AND first_loggedin_time IS NOT NULL  AND first_loggedin_time != '0000-00-00 00:00:00'
   ) as login_done,

   (
      SELECT count(DISTINCT s.id) AS count
      FROM store s 
      JOIN store_warehouse_shipper sws ON sws.store_id = s.id
      JOIN `order` o ON o.associate_id = sws.id 
      JOIN store_salesperson ss1 ON ss1.store_id = s.id
      WHERE a.id = ss1.salesperson_account_id 
   ) as first_order,

   (
      SELECT count(DISTINCT s.id) AS count
      FROM  store s 
      JOIN store_salesperson ss1 ON ss1.store_id = s.id
      WHERE s.id IN
      (
        SELECT s1.id      
        FROM `store` s1
        JOIN store_warehouse_shipper sws ON sws.store_id = s1.id
        JOIN `order` o ON o.associate_id = sws.id
        JOIN `account` a ON a.id = s1.account_id
        GROUP BY s1.id
        Having count(o.id) > 2
      )
      AND a.id = ss1.salesperson_account_id 
   ) as two_plus_order
       
        
FROM  `account` a
JOIN store_salesperson ss ON ss.salesperson_account_id = a.id
GROUP BY a.id