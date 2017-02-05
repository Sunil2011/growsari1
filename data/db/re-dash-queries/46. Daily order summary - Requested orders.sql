
SELECT d.date as date,
       count(DISTINCT o.associate_id) AS no_of_orders,
       SUM(o.initial_order_value) AS initial_order_value,  
       SUM(o.net_amount + o.loyalty_points_used) AS final_order_value,
       SUM(o.amount_collected) AS amount_collected,
	   SUM(o.loyalty_points_used) AS loyalty_points_used,
       SUM(o.loyalty_points_earn) AS loyalty_points_earn,
       SUM(o.returned_item_amount) AS returned_item_amount,
       SUM(o.initial_order_value - (o.net_amount + o.loyalty_points_used)) AS out_of_stock       
FROM `dates` d
LEFT JOIN `order` o ON date(o.created_at) = d.date
WHERE date BETWEEN '2016-06-30' AND NOW()
GROUP BY d.date
order by d.date desc