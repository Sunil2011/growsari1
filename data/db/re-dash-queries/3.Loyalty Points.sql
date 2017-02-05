
SELECT a.id,
       a.username,
       s.name AS store_name,
       (SUM(credit) - SUM(debit)) AS points
FROM loyalty_point lp
JOIN account a ON a.id = lp.account_id
JOIN store s ON s.account_id = a.id
GROUP BY a.id
ORDER BY points DESC