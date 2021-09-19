<?php

namespace Rialto\Purchasing\Catalog\Remote;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Database\Orm\DbManager;

class SupplierApiList implements \IteratorAggregate
{
    /** @var SupplierApi[] */
    private $apis;

    private $remove;

    public function __construct(array $apis)
    {
        $this->apis = new ArrayCollection($apis);
        $this->remove = new ArrayCollection();
    }

    public function getApis()
    {
        return $this->apis->getValues();
    }

    public function addApi(SupplierApi $api)
    {
        $this->apis[] = $api;
        $this->remove->removeElement($api);
    }

    public function removeApi(SupplierApi $api)
    {
        $this->apis->removeElement($api);
        $this->remove[] = $api;
    }

    public function getIterator()
    {
        return $this->apis->getIterator();
    }

    public function persistAll(DbManager $dbm)
    {
        foreach ($this->apis as $api) {
            $dbm->persist($api);
        }
        foreach ($this->remove as $api) {
            $dbm->remove($api);
        }
    }
}
