<?php


namespace Rialto\Accounting\Bank\Account\Repository\DQL;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Account\Repository\BankAccountRepository;
use Rialto\Accounting\Ledger\Account\GLAccount;

/**
 * A DQL implementation of a BankAccountRepository with a Doctrine EntityManager
 * backend.
 */
final class DqlBankAccountRepository implements BankAccountRepository
{
    private $repo;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repo = $entityManager->getRepository(BankAccount::class);
    }

    public function getDefaultChecking(): BankAccount
    {
        $account = $this->repo->find(GLAccount::REGULAR_CHECKING_ACCOUNT);
        assertion($account != null,
            "Unable to find default checking account.");

        return $account;
    }
}
