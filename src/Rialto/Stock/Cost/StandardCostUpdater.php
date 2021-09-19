<?php

namespace Rialto\Stock\Cost;

use Psr\Log\LoggerInterface;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Company\Company;
use Rialto\Company\Orm\CompanyRepository;
use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Cost\Orm\StandardCostRepository;
use Rialto\Stock\Level\StockLevelService;

/**
 * Stores a new StandardCost record in the database and does any required
 * accounting.
 */
class StandardCostUpdater
{
    /** @var DbManager */
    private $dbm;

    /** @var StandardCostRepository */
    private $repo;

    /** @var StockLevelService */
    private $levels;

    /** @var Company */
    private $company;

    /** @var LoggerInterface */
    private $logger;

    function __construct(DbManager $dbm, StockLevelService $levels, LoggerInterface $logger)
    {
        $this->dbm = $dbm;
        $this->repo = $dbm->getRepository(StandardCost::class);
        $this->levels = $levels;
        /** @var $companyRepo CompanyRepository */
        $companyRepo = $this->dbm->getRepository(Company::class);
        $this->company = $companyRepo->findDefault();
        $this->logger = $logger;
    }

    public function update(StandardCost $new)
    {
        $item = $new->getStockItem();
        assert( $item );

        $old = $this->repo->findCurrentByItem($item);
        if (! $this->hasCostChanged($new, $old) ) {
            $this->logger->warning("No changes entered.");
            return;
        }

        $item->setStandardCost($new);
        $new->setPrevious($old);
        $this->dbm->persist($new);
        $this->logger->notice(sprintf('Standard cost of %s updated to $%s.',
            $item, number_format($new->getTotalCost(), 4)
        ));

        $this->updateTotalStockValueIfNeeded($new, $old);
    }

    private function hasCostChanged(StandardCost $new, StandardCost $old = null)
    {
        if (! $old ) return true;
        return ( $new->getMaterialCost() != $old->getMaterialCost() ) ||
            ( $new->getLabourCost() != $old->getLabourCost() ) ||
            ( $new->getOverheadCost() != $old->getOverheadCost() );
    }

    /**
     * Does the accounting transaction, if needed.
     */
    private function updateTotalStockValueIfNeeded(StandardCost $new, StandardCost $old = null)
    {
        $item = $new->getStockItem();

        $oldCost = $old ? $old->getTotalCost() : 0;
        $diff = $new->getTotalCost() - $oldCost;
        if ( $diff == 0 ) return;

        $grossQty = $this->levels->getTotalQtyInStock($item);
        if ( 0 == $grossQty ) return;

        $new->setQtyInStock($grossQty);
        $this->dbm->flush(); // So that $new gets an ID

        /* Create GL transaction */
        $glTrans = Transaction::fromEvent($new);

        /* Add entries */
        $grossValue = $grossQty * $diff;
        $category = $item->getCategory();
        $stockAct = $category->getStockAccount();
        $adjustmentAct = $category->getAdjustmentAccount();
        $glTrans->addEntry($stockAct, $grossValue, $new->getMemo());
        $glTrans->addEntry($adjustmentAct, -$grossValue, $new->getMemo());

        $this->dbm->persist($glTrans);
        $this->logger->notice(sprintf('Adjusted %s account by $%s.',
            $stockAct->getName(),
            number_format($grossValue, 2)
        ));
    }

}
