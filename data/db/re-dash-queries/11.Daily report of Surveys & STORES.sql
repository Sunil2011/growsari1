DROP PROCEDURE IF EXISTS daily_survey_store_report;
DELIMITER //
CREATE PROCEDURE daily_survey_store_report() 
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

END // 
DELIMITER ;