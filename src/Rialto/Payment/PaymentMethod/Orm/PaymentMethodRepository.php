<?php

namespace Rialto\Payment\PaymentMethod\Orm;

use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Payment\PaymentMethod\PaymentMethod;
use Zend\Validator\CreditCard;

class PaymentMethodRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('p');
        return $builder->buildQuery($params);
    }

    /** @return PaymentMethod[] */
    public function findAll()
    {
        return $this->findBy([]);
    }

    /**
     * @param string $ccNumber
     *  The credit card number.
     * @return PaymentMethod|null
     */
    public function findByCardNumber($ccNumber)
    {
        static $tests = [
            CreditCard::AMERICAN_EXPRESS => PaymentMethod::ID_AMEX,
            CreditCard::VISA             => PaymentMethod::ID_VISA,
            CreditCard::MASTERCARD       => PaymentMethod::ID_MASTERCARD,
            CreditCard::DISCOVER         => PaymentMethod::ID_DISCOVER,
            CreditCard::ALL              => PaymentMethod::ID_UNKNOWN,
        ];

        foreach ( $tests as $test => $id) {
            $validator = new CreditCard($test);
            if ( $validator->isValid($ccNumber) ) {
                return $this->find($id);
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @return PaymentMethod|null
     */
    public function findByName($name)
    {
        $name = trim(strtolower($name));
        return $this->findOneBy([
            'name' => $name,
        ]);
    }

    /** @return PaymentMethod */
    public function findVisa()
    {
        return $this->find(PaymentMethod::ID_VISA);
    }

    /** @return PaymentMethod */
    public function findUnknownCreditCard()
    {
        return $this->find(PaymentMethod::ID_UNKNOWN);
    }

    /**
     * @param string $abbrev
     * @return PaymentMethod
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByAbbreviation($abbrev)
    {
        $pattern = join('%', str_split($abbrev)) . '%';
        $qb = $this->createQueryBuilder('pm');
        $qb->andWhere('pm.name like :pattern or pm.id like :pattern')
            ->setParameter('pattern', $pattern);
        return $qb->getQuery()->getSingleResult();
    }
}
