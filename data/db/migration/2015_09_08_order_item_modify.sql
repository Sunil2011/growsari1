-- modified items
ALTER TABLE  `order_item` ADD  `is_modified` TINYINT( 4 ) NOT NULL AFTER  `is_added_by_cc` ,
ADD  `modified_quantity` INT NOT NULL AFTER  `is_modified` ;

ALTER TABLE  `order_item` CHANGE  `modified_quantity`  `quantity_by_cc` INT( 11 ) NOT NULL ;
ALTER TABLE  `order_item` ADD  `quantity_by_wh` INT( 11 ) NOT NULL AFTER  `quantity_by_cc`;

ALTER TABLE  `order_item` CHANGE  `quantity_by_cc`  `quantity_by_cc` INT( 11 ) NULL DEFAULT NULL ;
ALTER TABLE  `order_item` CHANGE  `quantity_by_wh`  `quantity_by_wh` INT( 11 ) NULL DEFAULT NULL ;