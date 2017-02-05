CREATE EVENT daily_survey_store_report_event
ON SCHEDULE EVERY 1 DAY
STARTS '2016-07-26 06:00:00' 
DO
CALL daily_survey_store_report();
