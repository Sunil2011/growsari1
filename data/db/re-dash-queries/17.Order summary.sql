SELECT os.created_at AS date_when_order_packed,
       o.id AS order_number,
       o.initial_order_value,
       (o.net_amount + o.loyalty_points_used) AS final_order_value,
       o.amount_collected,
       o.loyalty_points_used,
       o.returned_item_amount,
       (o.initial_order_value - (o.net_amount + o.loyalty_points_used)) AS out_of_stock
FROM `order` AS o
JOIN order_status os ON os.order_id = o.id
WHERE os.status = 'packed'