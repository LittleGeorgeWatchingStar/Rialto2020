<?php

namespace Rialto\Sales\Customer\Orm;

use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Order\SalesOrder;

class CustomerBranchRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('branch');
        $builder->joinAndSelect('branch.address', 'address');
        $builder->leftJoin(SalesOrder::class, 'so', 'WITH',
            'so.customerBranch = branch');

        $builder->add('matching', function($qb, $matching) {
            $qb->andWhere('(
                branch.branchName like :matching
                or branch.contactName like :matching
                or branch.email like :matching
            )')->setParameter('matching', "%$matching%");
        });

        $builder->add('exclude', function($qb, $exclude) {
            $qb->andWhere('branch.branchName not like :exclude')
                ->andWhere('branch.contactName not like :exclude')
                ->andWhere('branch.email not like :exclude')
                ->setParameter('exclude', "%$exclude%");
        });

        $builder->add('lastOrderSince', function ($qb, $since) {
            $qb->andWhere('so.dateOrdered >= :lastOrderSince')
                ->setParameter('lastOrderSince', $since);
        });
        $builder->add('lastOrderUntil', function ($qb, $until) {
            $qb->andWhere('so.dateOrdered <= :lastOrderUntil')
                ->setParameter('lastOrderUntil', $until);
        });

        $builder->add('email', function($qb, $email) {
            $email = trim($email);
            $email = "%$email%";
            $qb->andWhere('branch.email like :email')
                ->setParameter('email', $email);
        });

        $builder->add('country', function($qb, $countryCode) {
            $qb->andWhere('address.countryCode = :countryCode')
                ->setParameter('countryCode', $countryCode);
        });
        $builder->add('state', function($qb, $stateCode) {
            $qb->andWhere('address.stateCode in (:stateCode)')
                ->setParameter('stateCode', $stateCode);
        });

        $builder->add('salesman', function($qb, $salesman) {
            $qb->andWhere('branch.salesman = :salesman')
                ->setParameter('salesman', $salesman);
        });

        $builder->add('_order', function($qb, $orderBy) {
            $qb->orderBy('branch.branchName');
        });

        return $builder->buildQuery($params);
    }

    /**
     * @param Customer $cust
     * @return CustomerBranch[]
     */
    public function findByCustomer(Customer $cust)
    {
        return $this->findBy([
            'customer' => $cust->getId()
        ]);
    }

    /** @deprecated The branchCode field is deprecated */
    public function findByCustomerAndBranchCode(Customer $customer, $branchCode)
    {
        return $this->findOneBy([
            'customer' => $customer->getId(),
            'branchCode' => $branchCode
        ]);
    }

    public function findByCustomerAndAddress(Customer $customer, PostalAddress $address)
    {
        $qb = $this->createQueryBuilder('branch')
            ->join('branch.address', 'address')
            ->where('branch.customer = :cust')
            ->andWhere('address.street1 like :addr1')
            ->andWhere('address.postalCode like :zip')
            ->setParameters([
                'cust' => $customer->getId(),
                'addr1' => $address->getStreet1(),
                'zip' => $address->getPostalCode()

            ]);
        $query = $qb->getQuery();
        return $query->getResult();
    }

    /** @return CustomerBranch|null */
    public function findByCustomerAndEmail(Customer $customer, $email)
    {
        return $this->findOneBy([
            'customer' => $customer->getId(),
            'email' => $email,
        ]);
    }
}
