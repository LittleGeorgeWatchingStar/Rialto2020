<?php

namespace Rialto\Accounting\Bank\Account;


use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Bank\Transaction\Orm\BankTransactionRepository;
use Rialto\Accounting\Transaction\SystemType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Ensures that a cheque number has not been used before.
 */
class AvailableChequeNumberValidator extends ConstraintValidator
{
    /** @var BankTransactionRepository */
    private $repo;

    public function __construct(ObjectManager $om)
    {
        $this->repo = $om->getRepository(BankTransaction::class);
    }

    /**
     * Inject a test double in unit tests.
     */
    public function setRepo(BankTransactionRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param Cheque $cheque
     * @param AvailableChequeNumber $constraint
     */
    public function validate($cheque, Constraint $constraint)
    {
        $chequeNo = $cheque->getChequeNumber();
        if (!$chequeNo) {
            return;
        }
        $account = $cheque->getBankAccount();
        $results = $this->repo->findBy([
            'chequeNumber' => $chequeNo,
            'bankAccount' => $account,
            'systemType' => [
                SystemType::CREDITOR_PAYMENT,
                SystemType::CREDITOR_REFUND
            ],
        ]);
        if (count($results) > 0) {
            $this->context->addViolation($constraint->message, [
                '_chequeNo' => $chequeNo,
                '_account' => $account,
            ]);
        }
    }

}
