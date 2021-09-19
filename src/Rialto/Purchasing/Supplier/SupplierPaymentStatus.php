<?php

namespace Rialto\Purchasing\Supplier;

use Doctrine\ORM\EntityManager;

class SupplierPaymentStatus
{
    private $em;
    public $overdue1 = 30;
    public $overdue2 = 60;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /** @return string[] */
    public function getStatus(Supplier $supplier)
    {
        $conn = $this->em->getConnection();

        $sql = "SELECT Suppliers.SuppName, Currencies.Currency, PaymentTerms.Terms,

        Sum(SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc) AS balance,

        Sum(IF (PaymentTerms.DaysBeforeDue > 0,
            CASE WHEN (TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate)) >= PaymentTerms.DaysBeforeDue
            THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,

            CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate,
            INTERVAL 1 MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth -
            DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= 0 THEN SuppTrans.OvAmount + SuppTrans.OvGST
            - SuppTrans.Alloc ELSE 0 END

        )) AS due,


        Sum(IF (PaymentTerms.DaysBeforeDue > 0,
            CASE WHEN TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) > PaymentTerms.DaysBeforeDue
            AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + :overdue1)
            THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,

            CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate,
            INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth -
            DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= :overdue1)
            THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END

        )) AS overdue1,

        Sum(IF (PaymentTerms.DaysBeforeDue > 0,
            CASE WHEN TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) > PaymentTerms.DaysBeforeDue
            AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + :overdue2)
            THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,

            CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate,
            INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth
            - DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= :overdue2)
            THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END

        )) AS overdue2

        FROM Suppliers

        JOIN PaymentTerms
        JOIN Currencies
        LEFT JOIN SuppTrans ON Suppliers.SupplierID = SuppTrans.SupplierNo

        WHERE
             Suppliers.SupplierID = :supplierID

        GROUP BY
              Suppliers.SuppName,
              Currencies.Currency,
              PaymentTerms.Terms,
              PaymentTerms.DaysBeforeDue,
              PaymentTerms.DayInFollowingMonth";

        $stmt = $conn->executeQuery($sql, [
            'overdue1' => $this->overdue1,
            'overdue2' => $this->overdue2,
            'supplierID' => $supplier->getId(),
        ]);

        return $stmt->fetch();
    }
}
