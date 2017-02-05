-- change the id as  you need

set foreign_key_checks=0;
DELETE FROM `order` WHERE `order`.`id` < 260;
DELETE FROM `order_avaibility` WHERE `order_id` < 260;
DELETE FROM `order_feedback` WHERE `order_id` < 260;
DELETE FROM `order_item` WHERE `order_id` < 260;
DELETE FROM `order_status` WHERE `order_id` < 260;
DELETE FROM `box` WHERE `order_id` < 260;
DELETE bi FROM `box_item` bi LEFT JOIN `box` b ON b.id = bi.box_id WHERE b.id IS NULL;
DELETE ori FROM `order_returned_item` ori LEFT JOIN `order_item` oi ON oi.id = ori.item_id WHERE oi.id IS NULL;
DELETE FROM `loyalty_point` WHERE order_id<260 AND order_id IS NOT NULL;
set foreign_key_checks=1;

--
-- Update login date
UPDATE store s 
JOIN account a ON a.id = s.account_id
JOIN account_session ac ON ac.account_id = a.id 
JOIN survey su ON s.id = su.store_id
SET s.first_loggedin_time = ac.created_at

--
-- Update signup date
update store s 
JOIN  `survey` su ON su.store_id = s.id
set signup_time = su.created_at

-- Delete specific order
DELETE FROM  `order_item` WHERE order_id  IN (383,467, 468);
DELETE FROM  `order_status` WHERE order_id IN (383,467, 468);
DELETE FROM  `loyalty_point` WHERE order_id IN (383,467, 468);
DELETE FROM  `order` WHERE id IN (383,467, 468);

-- update missing orders points
UPDATE  `order` o 
LEFT JOIN loyalty_point lp ON lp.order_id = o.id 
SET  `loyalty_points_earn` = lp.credit 
WHERE lp.remarks =  'Credit for an order' 
      AND o.loyalty_points_earn != lp.credit

-- delete stores which are not store accounts
DELETE FROM `store`  WHERE id in (
  SELECT * FROM 
  (
      SELECT s.id
      FROM  store s
      LEFT JOIN account a ON a.id = s.account_id
      WHERE a.type !=  'STORE'
  ) as x
) 

-- delete store_warehouse_shipper which are not store accounts
DELETE FROM `store_warehouse_shipper`  WHERE store_id in 
(
	SELECT s.id
	FROM  store s
	LEFT JOIN account a ON a.id = s.account_id
	WHERE a.type !=  'STORE'

) 