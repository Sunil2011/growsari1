DROP PROCEDURE IF EXISTS daily_store_report_delivery;
DELIMITER //
CREATE PROCEDURE daily_store_report_delivery(IN days INT)
BEGIN
      SET @@group_concat_max_len=15000;
      SET @SQL = NULL;

	SELECT 
	  GROUP_CONCAT(DISTINCT
	    CONCAT(
	      'sum(case when Date_format(o.delivered_by, ''%d-%M'') = ''',
	      dt,
	      ''' then o.net_amount+o.loyalty_points_used else NULL end) AS `',
	      dt, '`'
	    )
	  ) INTO @SQL
	FROM
	(
	  SELECT Date_format(date, '%d-%M') AS dt
	  FROM `dates`
	  where date BETWEEN DATE_SUB(NOW(), INTERVAL days DAY) AND NOW()
	  ORDER BY date asc
	) d;

	SET @SQL 
	  = CONCAT('SELECT a.username,
		           s.name AS store_name,
			   count(o.id) AS no_of_orders,
			   DATEDIFF(now(), max(o.created_at)) as days_since_last_order,
			   s.spend_per_week,
			   s.address,
			   s.contact_no as contact_number,
		           ', @SQL, ' 
		    FROM `store` s
		    JOIN store_warehouse_shipper sws ON sws.store_id = s.id
		    JOIN `order` o ON o.associate_id = sws.id
		    JOIN `account` a ON a.id = s.account_id  
		    GROUP BY s.id
		    ORDER BY no_of_orders DESC, days_since_last_order DESC, CAST(s.spend_per_week AS UNSIGNED) DESC');

 	PREPARE stmt FROM @SQL;
 	EXECUTE stmt;
 	DEALLOCATE PREPARE stmt;
END //
DELIMITER ;
