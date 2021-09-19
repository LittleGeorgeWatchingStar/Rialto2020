<?php

namespace Rialto\Payment\Sweep\Orm;

use Doctrine\ORM\EntityManagerInterface;
use Gumstix\Time\DateRange;
use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Card\Orm\CardTransactionRepository;
use Rialto\Payment\PaymentMethod\PaymentMethodGroup;
use Rialto\Payment\Sweep\Email\SweepEmail;

/**
 * Database access for credit card sweeps.
 */
class SweepGateway
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return CardTransaction[]
     */
    public function findUnsweptTransactions(DateRange $range): array
    {
        /** @var CardTransactionRepository $repo */
        $repo = $this->em->getRepository(CardTransaction::class);
        return $repo->findUnswept($range);
    }

    /**
     * @return PaymentMethodGroup[]
     */
    public function getPaymentMethodGroups(): array
    {
        return $this->em->getRepository(PaymentMethodGroup::class)
            ->findAll();
    }

    public function transactional(callable $func)
    {
        return $this->em->transactional($func);
    }

    public function loadRecipients(SweepEmail $email)
    {
        $email->loadSubscribers($this->em);
    }
}
