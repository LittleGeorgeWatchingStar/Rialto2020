<?php

namespace Rialto\Shipping\Method\Orm;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Shipper\Orm\ShipperRepository;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Stock\Transfer\Transfer;

class ShippingMethodRepository extends RialtoRepositoryAbstract
{
    /** @see Transfer::$shippingMethod */
    const TRANSFER_DEFAULT_SHIPPER = 'UPS';
    const TRANSFER_DEFAULT_SHIPPING_METHOD = '01';

    /**
     * @param string $name The name of the shipper, eg "UPS"
     * @param string $code The shipping method code, eg "03" for UPS Ground
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findByShipperNameAndCode($name, $code): ShippingMethod
    {
        $qb = $this->createQueryBuilder('sm');
        $qb->join('sm.shipper', 's')
            ->where('s.name like :name')
            ->setParameter('name', $name)
            ->andWhere('sm.code = :code')
            ->setParameter('code', $code);
        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param $string
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findOneMatching($string): ShippingMethod
    {
        $qb = $this->createQueryBuilder('sm');
        $qb->andWhere('sm.name like :pattern')
            ->setParameter('pattern', "%$string%");
        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findTransferDefault(): ShippingMethod
    {
        return $this->findByShipperNameAndCode($this::TRANSFER_DEFAULT_SHIPPER, $this::TRANSFER_DEFAULT_SHIPPING_METHOD);
    }

    public function findHandCarried(): ShippingMethod
    {
        /** @var ShipperRepository $shipperRepo */
        $shipperRepo = $this->_em->getRepository(Shipper::class);
        $shipper = $shipperRepo->findHandCarried();
        return $shipper->getShippingMethod('HAND');
    }
}
