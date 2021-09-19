<?php

namespace Rialto\Panelization;

use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\PurchaseOrder\ManufacturingExpense;
use Rialto\Manufacturing\Requirement\RequirementFactory;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Security\User\UserManager;
use Rialto\Stock\Facility\Orm\FacilityRepository;

/**
 * Creates the panelized purchase order and PCB quote requests.
 */
class PanelizedOrderFactory
{
    /** @var DbManager */
    private $dbm;

    /** @var UserManager */
    private $userManager;

    /** @var FacilityRepository */
    private $facilityRepo;

    /** @var RequirementFactory */
    private $requirementFactory;

    public function __construct(DbManager $dbm,
                                UserManager $userManager,
                                FacilityRepository $facilityRepo,
                                RequirementFactory $requirementFactory)
    {
        $this->dbm = $dbm;
        $this->userManager = $userManager;
        $this->facilityRepo = $facilityRepo;
        $this->requirementFactory = $requirementFactory;
    }

    public function createOrder(Panelizer $panelizer): PurchaseOrder
    {
        $owner = $this->userManager->getUser();
        $deliverTo = $this->facilityRepo->getHeadquarters();
        $po = $panelizer->createOrder($owner, $deliverTo);
        $this->dbm->beginTransaction();
        try {
            $this->dbm->persist($po);
            $this->dbm->flush();
            $this->requirementFactory->forPurchaseOrder($po);
            $expenses = new ManufacturingExpense($this->dbm);
            $expenses->addManufacturingExpensesIfNeeded($po);
            $panelizer->createQuotationRequests($owner, $this->dbm);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        return $po;
    }
}
