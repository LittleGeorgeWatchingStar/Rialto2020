<?php

namespace Rialto\Sales\GLPosting;

use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Sales\Customer\SalesArea;
use Rialto\Sales\GLPosting\Orm\CogsGLPostingRepository;
use Rialto\Sales\GLPosting\Orm\SalesGLPostingRepository;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Category\StockCategory;


/**
 * Determines which GL accounts are used to do the accounting
 * for various types of sales orders.
 *
 * The GL accounts used can depend on the area and type of the sales
 * order and the categories of stock item sold.
 */
abstract class GLPosting implements RialtoEntity
{
    /**
     * @return CogsGLPosting
     */
    public static function fetchCogsPosting(
        SalesArea $area,
        StockCategory $cat,
        SalesType $type)
    {
        $dbm = ErpDbManager::getInstance();
        /** @var $repo CogsGLPostingRepository */
        $repo = $dbm->getRepository(CogsGLPosting::class);
        return $repo->findBestMatch($area, $cat, $type);
    }

    /**
     * @return SalesGLPosting
     */
    public static function fetchSalesPosting(
        SalesArea $area,
        StockCategory $cat,
        SalesType $type)
    {
        $dbm = ErpDbManager::getInstance();
        /** @var $repo SalesGLPostingRepository */
        $repo = $dbm->getRepository(SalesGLPosting::class);
        return $repo->findBestMatch($area, $cat, $type);
    }

    /** @var integer */
    protected $id;

    /** @var SalesArea|null */
    protected $salesArea;

    /** @var SalesType|null */
    protected $salesType;

    /** @var StockCategory|null */
    protected $stockCategory;

    public function __construct(
        SalesArea $salesArea = null,
        SalesType $salesType = null,
        StockCategory $stockCategory = null)
    {
        $this->salesArea = $salesArea;
        $this->salesType = $salesType;
        $this->stockCategory = $stockCategory;
    }

    public function getId()
    {
        return $this->id;
    }

    /** @return SalesArea|null */
    public function getSalesArea()
    {
        return $this->salesArea;
    }

    /** @return SalesType|null */
    public function getSalesType()
    {
        return $this->salesType;
    }

    /** @return StockCategory|null */
    public function getStockCategory()
    {
        return $this->stockCategory;
    }
}

