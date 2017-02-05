
SELECT store_name,
       username,
       order_id,
       amount,
       cast(order_number AS UNSIGNED) AS order_number,
       days_after
FROM
  (SELECT *, @position := if(@prev_store_id=store_id, @position + 1, 1) AS order_number, DATEDIFF(created_at, if(@prev_store_id=store_id, if(@prev_day='2016-06-29', x.signup_time, @prev_day), signup_time)) AS days_after, @prev_store_id := store_id AS prev_store_id, @prev_day := created_at AS prev_day
   FROM
     (SELECT s.name AS store_name,
             a.username,
             s.id AS store_id,
             s.signup_time,
             o.id AS order_id,
             o.net_amount+o.loyalty_points_used AS amount,
             o.created_at
      FROM `store` s CROSS
      JOIN
        (SELECT @position := 0, @prev_store_id := 0,@prev_day:= '2016-06-29') i
      JOIN store_warehouse_shipper sws ON sws.store_id = s.id
      JOIN `order` o ON o.associate_id = sws.id
      JOIN `account` a ON a.id = s.account_id
      ORDER BY s.id ASC, o.id ASC) AS x) y