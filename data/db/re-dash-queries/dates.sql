CREATE VIEW digits AS
  SELECT 0 AS digit UNION ALL
  SELECT 1 UNION ALL
  SELECT 2 UNION ALL
  SELECT 3 UNION ALL
  SELECT 4 UNION ALL
  SELECT 5 UNION ALL
  SELECT 6 UNION ALL
  SELECT 7 UNION ALL
  SELECT 8 UNION ALL
  SELECT 9;

CREATE VIEW numbers AS
  SELECT
    ones.digit + tens.digit * 10 + hundreds.digit * 100 + thousands.digit * 1000 AS number
  FROM
    digits as ones,
    digits as tens,
    digits as hundreds,
    digits as thousands;

CREATE VIEW dates AS
  SELECT
    SUBDATE(CURRENT_DATE(), number) AS date
  FROM
    numbers
  UNION ALL
  SELECT
    ADDDATE(CURRENT_DATE(), number + 1) AS date
  FROM
    numbers;
