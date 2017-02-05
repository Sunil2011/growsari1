
SELECT a.username,
       o.*
FROM `order` AS o
JOIN `store_warehouse_shipper` AS `sws` ON `sws`.`id` = `o`.`associate_id`
JOIN `store` AS `s` ON `s`.`id` = `sws`.`store_id`
JOIN `account` AS `a` ON `a`.`id` = `s`.`account_id`
JOIN order_status os ON os.order_id = o.id
WHERE os.status = 'delivered'
  AND o.sms_sender IS NOT NULL
ORDER BY o.id DESC