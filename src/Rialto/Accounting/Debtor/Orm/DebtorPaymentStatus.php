<?php

namespace Rialto\Accounting\Debtor\Orm;

use Doctrine\ORM\EntityManager;
use Rialto\Sales\Customer\Customer;

class DebtorPaymentStatus
{
    private $em;
    public $overdue1 = 30;
    public $overdue2 = 60;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /** @return string[] */
    public function getStatus(Customer $customer)
    {
        $conn = $this->em->getConnection();

        $transTotal = "trans.OvAmount + trans.OvGST + trans.OvFreight + trans.OvDiscount";
        $transUnallocated = "$transTotal - trans.Alloc";

        $sql = "SELECT customer.Name
            , currency.Currency
            , terms.Terms as paymentTerms
            , customer.CreditLimit
            , hold.DissallowInvoices
            , hold.ReasonDescription as creditStatus
            , sum($transUnallocated) AS balance

            , sum(IF (terms.DaysBeforeDue > 0,
                    CASE WHEN (TO_DAYS(Now()) - TO_DAYS(trans.TranDate)) >= terms.DaysBeforeDue
                    THEN $transUnallocated ELSE 0 END,

                    CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(trans.TranDate,
                    INTERVAL 1 MONTH), INTERVAL (terms.DayInFollowingMonth -
                    DAYOFMONTH(trans.TranDate)) DAY)) >= 0
                    THEN $transUnallocated ELSE 0 END

            )) AS due

            , sum(IF (terms.DaysBeforeDue > 0,
                    CASE WHEN TO_DAYS(Now()) - TO_DAYS(trans.TranDate) > terms.DaysBeforeDue
                    AND TO_DAYS(Now()) - TO_DAYS(trans.TranDate) >= (terms.DaysBeforeDue +
                    :overdue1 )
                    THEN $transUnallocated ELSE 0 END,

                    CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(trans.TranDate,
                    INTERVAL 1  MONTH), INTERVAL (terms.DayInFollowingMonth -
                    DAYOFMONTH(trans.TranDate)) DAY)) >= :overdue1)
                    THEN $transUnallocated ELSE 0 END

            )) AS overdue1

            , sum(IF (terms.DaysBeforeDue > 0,
                    CASE WHEN TO_DAYS(Now()) - TO_DAYS(trans.TranDate) > terms.DaysBeforeDue
                    AND TO_DAYS(Now()) - TO_DAYS(trans.TranDate) >= (terms.DaysBeforeDue +
                    :overdue2 )
                    THEN $transUnallocated ELSE 0 END,

                    CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(trans.TranDate,
                    INTERVAL 1  MONTH), INTERVAL (terms.DayInFollowingMonth
                    - DAYOFMONTH(trans.TranDate)) DAY)) >= :overdue2 )
                    THEN $transUnallocated ELSE 0 END

            )) AS overdue2

            FROM DebtorsMaster as customer
            JOIN PaymentTerms as terms on customer.PaymentTerms = terms.TermsIndicator
            JOIN HoldReasons as hold
            JOIN Currencies as currency
            LEFT JOIN DebtorTrans as trans ON customer.DebtorNo = trans.customerID

            WHERE customer.DebtorNo = :debtorID

            GROUP BY
                customer.Name,
                currency.Currency,
                terms.Terms,
                terms.DaysBeforeDue,
                terms.DayInFollowingMonth,
                customer.CreditLimit,
                hold.DissallowInvoices,
                hold.ReasonDescription
        ";

        $stmt = $conn->executeQuery($sql, [
            'overdue1' => $this->overdue1,
            'overdue2' => $this->overdue2,
            'debtorID' => $customer->getId(),
        ]);

        return $stmt->fetch();
    }
}
