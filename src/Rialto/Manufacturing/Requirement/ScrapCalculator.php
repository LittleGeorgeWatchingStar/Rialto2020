<?php

namespace Rialto\Manufacturing\Requirement;

use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\WorkOrder\WorkOrder;

/**
 * Determines how many additional units a work order requirement needs
 * to account for scrap that is wasted during the manufacturing process.
 */
class ScrapCalculator
{
    /** @var DbManager */
    private $dbm;

    private $entries = null;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
    }

    public function updateScrapCounts(WorkOrder $wo)
    {
        foreach ($wo->getRequirements() as $woReq) {
            $count = $this->getScrapCount($woReq);
            $woReq->setScrapCount($count);
        }
    }

    private function getScrapCount(Requirement $woReq)
    {
        $package = $woReq->getPackage();
        return $this->getPackageScrapCount($package);
    }

    public function getPackageScrapCount(string $package)
    {
        $this->loadIfNeeded();
        return isset($this->entries[$package]) ?
            $this->entries[$package] :
            0;
    }

    private function loadIfNeeded()
    {
        if (null === $this->entries) {
            $this->entries = [];
            $this->load();
        }
    }

    private function load()
    {
        /** @var $all ScrapCount[] */
        $all = $this->dbm->getRepository(ScrapCount::class)->findAll();
        foreach ($all as $sc) {
            $this->addEntry($sc->getPackage(), $sc->getScrapCount());
        }
    }

    private function addEntry($package, $scrapCount)
    {
        $this->entries[$package] = $scrapCount;
    }
}
