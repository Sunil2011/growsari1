
SELECT a.username,
       s.name,
       l.amount,
       l.status,
       l.created_at AS loan_taken_on,
       sum(lp.amount) AS amount_paid,
       lp.created_at AS loan_cleared_on
FROM loan l
JOIN account a ON a.id = l.account_id
JOIN store s ON s.account_id = a.id
LEFT JOIN loan_payment lp ON lp.loan_id = l.id
GROUP BY l.id