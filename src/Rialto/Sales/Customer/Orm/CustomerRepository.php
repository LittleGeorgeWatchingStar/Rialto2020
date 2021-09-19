<?php

namespace Rialto\Sales\Customer\Orm;

use Doctrine\ORM\QueryBuilder;
use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Sales\Customer\Customer;


class CustomerRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('customer');
        $builder->leftJoinAndSelect('customer.address', 'address');

        $builder->add('name', function(QueryBuilder $qb, $name) {
            $qb->andWhere($qb->expr()->orx(
                $qb->expr()->like('customer.name', ':name'),
                $qb->expr()->like('customer.companyName', ':name')
            ))
            ->setParameter('name', $name);
        });

        $builder->add('email', function(QueryBuilder $qb, $email) {
            $qb->andWhere('customer.email like :email')
                ->setParameter('email', "%$email%");
        });

        $builder->add('sourceID', function(QueryBuilder $qb, $sourceID) {
            $qb->andWhere('customer.EDIReference = :sourceID')
                ->setParameter('sourceID', $sourceID);
        });

        $builder->add('matching', function(QueryBuilder $qb, $pattern) {
            $pattern = trim($pattern);
            $pattern = str_replace('*', '%', $pattern);
            $pattern = str_replace(' ', '%', $pattern);
            $pattern = "%$pattern%";
            $qb->leftJoin('customer.branches', 'branch')
                ->andWhere($qb->expr()->orx(
                    $qb->expr()->like('customer.name', ':pattern'),
                    $qb->expr()->like('customer.companyName', ':pattern'),
                    $qb->expr()->like('branch.branchName', ':pattern'),
                    $qb->expr()->like('branch.contactName', ':pattern'),
                    $qb->expr()->like('branch.email', ':pattern')
                ))
                ->setParameter('pattern', $pattern);
        });

        $builder->add('internal', function (QueryBuilder $qb, $internal) {
            if ('yes' === $internal) {
                $qb->andWhere('customer.internalCustomer = 1');
            } elseif ('no' === $internal) {
                $qb->andWhere('customer.internalCustomer = 0');
            }
        });

        return $builder->buildQuery($params);
    }

    /**
     * Returns all customers who match the given search key.
     *
     * @param string $search_key
     * @return Customer[]
     */
    public function findMatchingCustomers($search_key, $limit=100)
    {
        /* Surround each word with database wildcards */
        $filtered_search = '%' . str_replace(' ', '%', $search_key) . '%';
        $qb = $this->createQueryBuilder('customer');
        $qb->leftJoin('customer.branches', 'branch')
            ->where($qb->expr()->orX(
                $qb->expr()->eq('customer.id', ':searchKey'),
                $qb->expr()->like('customer.name', ':filteredSearch'),
                $qb->expr()->like('customer.companyName', ':filteredSearch'),
                $qb->expr()->like('branch.email', ':emailSearch')
            ))
            ->setParameters([
                'searchKey' => $search_key,
                'filteredSearch' => $filtered_search,
                'emailSearch' => '%'.$search_key.'%'
            ]);
        if ( $limit > 0 ) {
            $qb->setMaxResults($limit);
        }
        return $qb->getQuery()->getResult();
    }

    /** @return Customer[] */
    public function findOverpaidCustomersForBankStatement(BankStatement $statement)
    {
        if (! $statement->isDeposit() ) {
            throw new \InvalidArgumentException(sprintf(
                'Bank statement %s is not a deposit', $statement->getId()
            ));
        }

        $transactions = $statement->getBankTransactions();
        if ( empty($transactions) ) {
            return [];
        }

        $ids = array_map(function(BankTransaction $bt) {
            return $bt->getId();
        }, $transactions);

        $qb = $this->createQueryBuilder('customer');
        $qb->join(DebtorTransaction::class, 'dt', 'WITH',
                'dt.customer = customer')
            ->join(BankTransaction::class, 'bt', 'WITH',
                'bt.systemType = dt.systemType and bt.systemTypeNumber = dt.systemTypeNumber')
            ->where('bt.id in (:ids)')
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    /** @return Customer|object|null Null if there is no matching customer. */
    public function findByEmail($email)
    {
        return $this->findOneBy(['email' => trim($email)]);
    }
}
