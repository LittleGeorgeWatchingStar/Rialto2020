START TRANSACTION;
UPDATE Accounting_Transaction
    SET transactionDate='2021-01-01 00:00:00', period=215
    WHERE id IN
          (SELECT DISTINCT(transactionId)
            FROM GLTrans WHERE Posted = 0 AND TranDate < '2021-01-01');
UPDATE GLTrans
    SET TranDate='2021-01-01 00:00:00', PeriodNo=215
    WHERE Posted = 0 AND TranDate < '2021-01-01';
COMMIT;
