
SELECT *
FROM `product`
WHERE created_at BETWEEN DATE_SUB(NOW(), INTERVAL 20 DAY) AND NOW()