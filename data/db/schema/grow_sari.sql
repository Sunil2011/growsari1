-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 01, 2016 at 05:56 PM
-- Server version: 5.5.50-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `grow_sari`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`%` PROCEDURE `daily_store_report_delivery`(IN days INT)
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
END$$

CREATE DEFINER=`root`@`%` PROCEDURE `daily_store_report_request`(IN days INT)
BEGIN
      SET @@group_concat_max_len=15000;
      SET @SQL = NULL;

	SELECT 
	  GROUP_CONCAT(DISTINCT
	    CONCAT(
	      'sum(case when Date_format(o.created_at, ''%d-%M'') = ''',
	      dt,
	      ''' then o.initial_order_value else NULL end) AS `',
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
END$$

CREATE DEFINER=`root`@`%` PROCEDURE `daily_survey_store_report`()
    NO SQL
BEGIN
  DECLARE RptDate DATE;
  DECLARE duplicate_key INT DEFAULT 0;

  SELECT max(date_of_report) INTO RptDate FROM survey_store_report;
  IF (RptDate IS NULL) THEN
      SET RptDate = '2016-07-01';
  ELSE
      SET RptDate = DATE_ADD(RptDate, INTERVAL 1 DAY); 
  END IF;
 
  WHILE (RptDate < CURDATE()) DO

    BEGIN
      DECLARE EXIT HANDLER FOR 1062 /* Duplicate key*/ SET duplicate_key=1;
    
      INSERT INTO survey_store_report 
                   (date_of_report, 
                    total_surveys, 
                    total_signups, 
                    stores_who_have_ever_ordered, 
                    stores_who_have_ordered_last_2weeks, 
                    stores_who_have_ordered_last_week, 
                    created_at)
      SELECT
        RptDate,
        (SELECT count(*)
         FROM survey
         WHERE date(created_at) <= RptDate) AS total_surveys,

        (SELECT count(*)
         FROM store
         WHERE signup_time IS NOT NULL
           AND signup_time != '0000-00-00 00:00:00'
           AND date(signup_time) <= RptDate) AS total_signups,

        (SELECT count(DISTINCT s.id)
         FROM `store` s
         JOIN store_warehouse_shipper sws ON sws.store_id = s.id
         JOIN `order` o ON o.associate_id = sws.id
         WHERE date(o.created_at) <= RptDate) AS stores_who_have_ever_ordered,

        (SELECT count(DISTINCT s.id)
         FROM `store` s
         JOIN store_warehouse_shipper sws ON sws.store_id = s.id
         JOIN `order` o ON o.associate_id = sws.id
         WHERE o.created_at BETWEEN DATE_SUB(RptDate, INTERVAL 14 DAY) AND RptDate) AS stores_who_have_ordered_last_2weeks,

        (SELECT count(DISTINCT s.id)
         FROM `store` s
         JOIN store_warehouse_shipper sws ON sws.store_id = s.id
         JOIN `order` o ON o.associate_id = sws.id
         WHERE o.created_at BETWEEN DATE_SUB(RptDate, INTERVAL 7 DAY) AND RptDate) AS stores_who_have_ordered_last_week,

         now();

      SET RptDate = DATE_ADD(RptDate, INTERVAL 1 DAY); 
    END;

    IF (duplicate_key=1) THEN
      SET RptDate = CURDATE(); 
    END IF;

  END WHILE;
 
  SELECT * FROM survey_store_report;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `display_name` varchar(63) NOT NULL,
  `email` varchar(63) NOT NULL,
  `password` varchar(127) NOT NULL,
  `state` smallint(5) unsigned NOT NULL,
  `phone` varchar(15) NOT NULL,
  `type` enum('GROWSARI','WAREHOUSE','SHIPPER','STORE','SALESPERSON','CALLCENTER') NOT NULL,
  `role` enum('USER','ADMIN','SUPER_ADMIN') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=491 ;

-- --------------------------------------------------------

--
-- Table structure for table `account_device`
--

CREATE TABLE IF NOT EXISTS `account_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `device_token` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `app_version` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=104 ;

-- --------------------------------------------------------

--
-- Table structure for table `brand`
--

CREATE TABLE IF NOT EXISTS `brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(63) NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=545 ;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mega_category_id` int(11) NOT NULL,
  `name` varchar(63) NOT NULL,
  `thumb_url` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mega_category_id` (`mega_category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `dates`
--
CREATE TABLE IF NOT EXISTS `dates` (
`date` date
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `digits`
--
CREATE TABLE IF NOT EXISTS `digits` (
`digit` bigint(20)
);
-- --------------------------------------------------------

--
-- Table structure for table `globe_sms`
--

CREATE TABLE IF NOT EXISTS `globe_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sms_uid` varchar(127) NOT NULL,
  `sms_body` text NOT NULL,
  `sms_body_with_header` text NOT NULL,
  `status` varchar(50) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `sms_uid` (`sms_uid`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Table structure for table `globe_store_token`
--

CREATE TABLE IF NOT EXISTS `globe_store_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `subscriber_number` varchar(20) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriber_number` (`subscriber_number`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `loan`
--

CREATE TABLE IF NOT EXISTS `loan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `interest_rate` decimal(11,2) NOT NULL,
  `status` varchar(255) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `loan_payment`
--

CREATE TABLE IF NOT EXISTS `loan_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_point`
--

CREATE TABLE IF NOT EXISTS `loyalty_point` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `store_refer_id` int(11) DEFAULT NULL,
  `debit` int(11) unsigned NOT NULL,
  `credit` int(11) unsigned NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_refer_id` (`store_refer_id`),
  KEY `account_id` (`account_id`,`order_id`),
  KEY `order_id` (`order_id`),
  KEY `account_id_2` (`account_id`),
  KEY `remarks` (`remarks`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=614 ;

-- --------------------------------------------------------

--
-- Table structure for table `mega_category`
--

CREATE TABLE IF NOT EXISTS `mega_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(63) NOT NULL,
  `thumb_url` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `numbers`
--
CREATE TABLE IF NOT EXISTS `numbers` (
`number` bigint(25)
);
-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE IF NOT EXISTS `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `shipper_team_id` int(11) DEFAULT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `discount` decimal(11,2) unsigned NOT NULL,
  `delivery_charges` decimal(11,2) unsigned NOT NULL,
  `initial_order_value` decimal(11,2) NOT NULL COMMENT 'initial_items_cost + delivery_charges',
  `net_amount` decimal(11,2) unsigned NOT NULL COMMENT 'items_cost + delivery_charges - loyalty_points',
  `amount_collected` decimal(11,2) unsigned NOT NULL COMMENT 'net_amount - returned_items_cost',
  `returned_item_amount` decimal(11,2) unsigned NOT NULL,
  `loyalty_points_used` int(11) unsigned NOT NULL,
  `loyalty_points_earn` int(11) unsigned NOT NULL,
  `delivered_by` datetime NOT NULL,
  `no_of_boxes` int(11) unsigned NOT NULL,
  `promo_id` int(11) NOT NULL,
  `is_saved` tinyint(2) NOT NULL,
  `feedback_given` tinyint(4) NOT NULL,
  `is_returned` tinyint(2) NOT NULL,
  `sms_sender` varchar(20) DEFAULT NULL,
  `is_added_by_cc` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `associate_id` (`associate_id`),
  KEY `shipper_team_id` (`shipper_team_id`),
  KEY `delivered_by` (`delivered_by`),
  KEY `feedback_given` (`feedback_given`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=443 ;

-- --------------------------------------------------------

--
-- Table structure for table `order_feedback`
--

CREATE TABLE IF NOT EXISTS `order_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `experience` varchar(255) NOT NULL,
  `rating` int(11) NOT NULL,
  `remarks` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=94 ;

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE IF NOT EXISTS `order_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `requested_quantity` int(11) unsigned NOT NULL,
  `quantity` int(11) unsigned NOT NULL,
  `super8_price` decimal(9,2) NOT NULL,
  `price` decimal(9,2) unsigned NOT NULL,
  `srp` decimal(9,2) unsigned NOT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `discount` decimal(11,2) unsigned NOT NULL,
  `net_amount` decimal(11,2) unsigned NOT NULL,
  `promo` varchar(63) NOT NULL,
  `is_available` tinyint(1) NOT NULL,
  `is_added_by_cc` tinyint(4) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_order_items_1_idx` (`order_id`),
  KEY `fk_order_items_2_idx` (`product_id`),
  KEY `is_available` (`is_available`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3324 ;

-- --------------------------------------------------------

--
-- Table structure for table `order_returned_item`
--

CREATE TABLE IF NOT EXISTS `order_returned_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) unsigned NOT NULL,
  `reason` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=75 ;

-- --------------------------------------------------------

--
-- Table structure for table `order_status`
--

CREATE TABLE IF NOT EXISTS `order_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_order_status_1_idx` (`order_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1298 ;

-- --------------------------------------------------------

--
-- Table structure for table `order_task`
--

CREATE TABLE IF NOT EXISTS `order_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `is_finished` int(11) NOT NULL,
  `remarks` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `is_finished` (`is_finished`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE IF NOT EXISTS `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `sku_id` varchar(255) NOT NULL,
  `item_code` varchar(255) DEFAULT NULL,
  `super8_name` varchar(255) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `volume` varchar(31) NOT NULL,
  `sku` varchar(127) NOT NULL,
  `variant_color` varchar(15) NOT NULL,
  `image` varchar(127) NOT NULL,
  `display` varchar(31) NOT NULL,
  `format` varchar(63) NOT NULL,
  `quantity` int(11) NOT NULL,
  `promo` varchar(15) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL,
  `super8_price` decimal(9,2) NOT NULL,
  `price` decimal(9,2) NOT NULL,
  `srp` decimal(9,2) NOT NULL,
  `status` varchar(15) NOT NULL,
  `is_promotional` tinyint(4) NOT NULL,
  `is_recommended` tinyint(4) NOT NULL,
  `is_new` tinyint(4) NOT NULL,
  `is_locked` int(11) NOT NULL,
  `is_available` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_code` (`item_code`),
  KEY `fk_product_1_idx` (`category_id`),
  KEY `fk_product_2_idx` (`brand_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2634 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_super8_price_history`
--

CREATE TABLE IF NOT EXISTS `product_super8_price_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `price` decimal(9,2) NOT NULL,
  `date` date NOT NULL,
  `is_available` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`,`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2388 ;

-- --------------------------------------------------------

--
-- Table structure for table `salesperson_track`
--

CREATE TABLE IF NOT EXISTS `salesperson_track` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `salesperson_account_id` int(11) NOT NULL,
  `point_x` decimal(9,4) NOT NULL,
  `point_y` decimal(9,4) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `salesperson_account_id` (`salesperson_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id` char(32) NOT NULL DEFAULT '',
  `name` char(32) NOT NULL DEFAULT '',
  `modified` int(11) DEFAULT NULL,
  `lifetime` int(11) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shipper`
--

CREATE TABLE IF NOT EXISTS `shipper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `name` varchar(31) NOT NULL,
  `status` varchar(15) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `locality` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `pincode` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `shipper_team`
--

CREATE TABLE IF NOT EXISTS `shipper_team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `shipper_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_shipper_account_1_idx` (`account_id`),
  KEY `fk_shipper_account_2_idx` (`shipper_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `store`
--

CREATE TABLE IF NOT EXISTS `store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `name` varchar(31) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `store_uid` varchar(255) NOT NULL,
  `status` varchar(15) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `point_x` decimal(9,4) NOT NULL,
  `point_y` decimal(9,4) NOT NULL,
  `is_storeowner` tinyint(4) NOT NULL,
  `spend_per_week` varchar(255) NOT NULL,
  `has_smartphone` tinyint(4) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `contact_no` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `locality` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `pincode` varchar(255) DEFAULT NULL,
  `is_covered` tinyint(4) NOT NULL,
  `funnel_status` varchar(255) NOT NULL,
  `signup_time` datetime NOT NULL,
  `first_loggedin_time` datetime NOT NULL,
  `revisit_date` date NOT NULL,
  `revisit_time` time NOT NULL,
  `remarks` text NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`),
  KEY `is_deleted` (`is_deleted`),
  KEY `contact_no` (`contact_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=475 ;

-- --------------------------------------------------------

--
-- Table structure for table `store_refer`
--

CREATE TABLE IF NOT EXISTS `store_refer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refered_by` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `refered_by` (`refered_by`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `store_salesperson`
--

CREATE TABLE IF NOT EXISTS `store_salesperson` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `salesperson_account_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_id` (`store_id`),
  KEY `salesperson_account_id` (`salesperson_account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=432 ;

-- --------------------------------------------------------

--
-- Table structure for table `store_visit`
--

CREATE TABLE IF NOT EXISTS `store_visit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `contacted_by` int(11) NOT NULL,
  `order_barrier` varchar(255) NOT NULL,
  `comments` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `contacted_by` (`contacted_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `store_warehouse_shipper`
--

CREATE TABLE IF NOT EXISTS `store_warehouse_shipper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `warehouse_shipper_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_store_warehouse_shipper_1_idx` (`store_id`),
  KEY `fk_store_warehouse_shipper_2_idx` (`warehouse_shipper_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=473 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey`
--

CREATE TABLE IF NOT EXISTS `survey` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `store_id` int(11) DEFAULT NULL,
  `point_x` decimal(9,4) NOT NULL,
  `point_y` decimal(9,4) NOT NULL,
  `is_storeowner` tinyint(4) NOT NULL,
  `spend_per_week` varchar(255) NOT NULL,
  `has_smartphone` tinyint(4) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `address` text,
  `contact_no` varchar(255) NOT NULL,
  `is_covered` tinyint(4) NOT NULL,
  `funnel_status` varchar(255) NOT NULL,
  `revisit_date` date NOT NULL,
  `revisit_time` time NOT NULL,
  `remarks` text NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `store_id` (`store_id`),
  KEY `is_deleted` (`is_deleted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=302 ;

-- --------------------------------------------------------

--
-- Table structure for table `survey_store_report`
--

CREATE TABLE IF NOT EXISTS `survey_store_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_of_report` date NOT NULL,
  `total_surveys` int(11) NOT NULL,
  `total_signups` int(11) NOT NULL,
  `stores_who_have_ever_ordered` int(11) NOT NULL,
  `stores_who_have_ordered_last_2weeks` int(11) NOT NULL,
  `stores_who_have_ordered_last_week` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_of_report` (`date_of_report`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

--
-- Table structure for table `warehouse`
--

CREATE TABLE IF NOT EXISTS `warehouse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `name` varchar(31) NOT NULL,
  `status` varchar(15) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `locality` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `pincode` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_shipper`
--

CREATE TABLE IF NOT EXISTS `warehouse_shipper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(11) NOT NULL,
  `shipper_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_warehouse_shipper_1_idx` (`warehouse_id`),
  KEY `fk_warehouse_shipper_2_idx` (`shipper_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Structure for view `dates`
--
DROP TABLE IF EXISTS `dates`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `dates` AS select (curdate() - interval `numbers`.`number` day) AS `date` from `numbers` union all select (curdate() + interval (`numbers`.`number` + 1) day) AS `date` from `numbers`;

-- --------------------------------------------------------

--
-- Structure for view `digits`
--
DROP TABLE IF EXISTS `digits`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `digits` AS select 0 AS `digit` union all select 1 AS `1` union all select 2 AS `2` union all select 3 AS `3` union all select 4 AS `4` union all select 5 AS `5` union all select 6 AS `6` union all select 7 AS `7` union all select 8 AS `8` union all select 9 AS `9`;

-- --------------------------------------------------------

--
-- Structure for view `numbers`
--
DROP TABLE IF EXISTS `numbers`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `numbers` AS select (((`ones`.`digit` + (`tens`.`digit` * 10)) + (`hundreds`.`digit` * 100)) + (`thousands`.`digit` * 1000)) AS `number` from (((`digits` `ones` join `digits` `tens`) join `digits` `hundreds`) join `digits` `thousands`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_device`
--
ALTER TABLE `account_device`
  ADD CONSTRAINT `account_device_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON UPDATE NO ACTION;

--
-- Constraints for table `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`mega_category_id`) REFERENCES `mega_category` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `loyalty_point`
--
ALTER TABLE `loyalty_point`
  ADD CONSTRAINT `loyalty_point_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `loyalty_point_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `loyalty_point_ibfk_3` FOREIGN KEY (`store_refer_id`) REFERENCES `store_refer` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`associate_id`) REFERENCES `store_warehouse_shipper` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `order_ibfk_2` FOREIGN KEY (`shipper_team_id`) REFERENCES `shipper_team` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `order_feedback`
--
ALTER TABLE `order_feedback`
  ADD CONSTRAINT `order_feedback_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `order_feedback_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `fk_order_items_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_order_items_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON UPDATE NO ACTION;

--
-- Constraints for table `order_returned_item`
--
ALTER TABLE `order_returned_item`
  ADD CONSTRAINT `order_returned_item_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `order_item` (`id`) ON UPDATE NO ACTION;

--
-- Constraints for table `order_status`
--
ALTER TABLE `order_status`
  ADD CONSTRAINT `fk_order_status_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `order_task`
--
ALTER TABLE `order_task`
  ADD CONSTRAINT `order_task_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_product_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_product_2` FOREIGN KEY (`brand_id`) REFERENCES `brand` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `product_super8_price_history`
--
ALTER TABLE `product_super8_price_history`
  ADD CONSTRAINT `product_super8_price_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `salesperson_track`
--
ALTER TABLE `salesperson_track`
  ADD CONSTRAINT `salesperson_track_ibfk_1` FOREIGN KEY (`salesperson_account_id`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `shipper`
--
ALTER TABLE `shipper`
  ADD CONSTRAINT `shipper_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `shipper_team`
--
ALTER TABLE `shipper_team`
  ADD CONSTRAINT `fk_shipper_account_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_shipper_account_2` FOREIGN KEY (`shipper_id`) REFERENCES `shipper` (`id`) ON UPDATE NO ACTION;

--
-- Constraints for table `store`
--
ALTER TABLE `store`
  ADD CONSTRAINT `store_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `store_refer`
--
ALTER TABLE `store_refer`
  ADD CONSTRAINT `store_refer_ibfk_1` FOREIGN KEY (`refered_by`) REFERENCES `store` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `store_refer_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `store_salesperson`
--
ALTER TABLE `store_salesperson`
  ADD CONSTRAINT `store_salesperson_ibfk_1` FOREIGN KEY (`salesperson_account_id`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `store_salesperson_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `store_visit`
--
ALTER TABLE `store_visit`
  ADD CONSTRAINT `store_visit_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `store_visit_ibfk_2` FOREIGN KEY (`contacted_by`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `store_warehouse_shipper`
--
ALTER TABLE `store_warehouse_shipper`
  ADD CONSTRAINT `fk_store_warehouse_shipper_1` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_warehouse_shipper` FOREIGN KEY (`warehouse_shipper_id`) REFERENCES `warehouse_shipper` (`id`) ON UPDATE NO ACTION;

--
-- Constraints for table `warehouse`
--
ALTER TABLE `warehouse`
  ADD CONSTRAINT `warehouse_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `warehouse_shipper`
--
ALTER TABLE `warehouse_shipper`
  ADD CONSTRAINT `fk_warehouse_shipper_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_warehouse_shipper_2` FOREIGN KEY (`shipper_id`) REFERENCES `shipper` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`%` EVENT `daily_survey_store_report_event` ON SCHEDULE EVERY 1 DAY STARTS '2016-07-26 06:00:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL daily_survey_store_report()$$

DELIMITER ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
