<?php

namespace Rialto\Purchasing\Catalog\Remote;

use Psr\Container\ContainerInterface;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A supplier API record maps a supplier to a service which provides
 * an API to that supplier's online catalog.
 *
 * @UniqueEntity(fields={"supplier"}, message="purchasing.supplier_api.already_exists")
 */
class SupplierApi implements RialtoEntity
{
    const OCTOPART = 'octopart';

    /**
     * @var Supplier
     * @Assert\NotNull(message="Please select a supplier.")
     */
    private $supplier;

    /**
     * @var string
     * @Assert\NotBlank(message="Please enter the name of a service.")
     * @Assert\Choice(callback="getServiceOptions")
     */
    private $serviceName = '';

    public function getId()
    {
        return $this->supplier->getId();
    }

    public function getSupplier()
    {
        return $this->supplier;
    }

    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * @return string
     *
     * @Assert\NotBlank
     * @Assert\Url
     */
    public function getWebsite()
    {
        return $this->supplier->getWebsite();
    }

    public function setWebsite($website)
    {
        $this->supplier->setWebsite($website);
    }

    /**
     * @param ContainerInterface $container
     * @return SupplierCatalog The service class which provides access to
     *   the supplier's online API.
     */
    public function getService(ContainerInterface $container)
    {
        $serviceId = $this->getServiceId();
        return $container->get($serviceId);
    }

    private function getServiceId(): string
    {
        $mapping = self::getServiceMapping();
        $name = $this->serviceName;
        $serviceId = $mapping[$name] ?? null;
        if ($serviceId) {
            return $serviceId;
        }
        throw new \InvalidArgumentException("No such supplier API service '$name'");
    }

    private static function getServiceMapping(): array
    {
        return [
            self::OCTOPART => OctopartCatalog::class,
        ];
    }

    public static function getServiceOptions(): array
    {
        $names = array_keys(self::getServiceMapping());
        return array_combine($names, $names);
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function setServiceName($name)
    {
        $this->serviceName = trim($name);
    }
}
