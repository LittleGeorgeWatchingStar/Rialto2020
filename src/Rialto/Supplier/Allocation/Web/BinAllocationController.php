<?php


namespace Rialto\Supplier\Allocation\Web;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Supplier\SupplierVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Allows the CM to view bin allocations at their facility.
 *
 * @Route("/supplier")
 */
final class BinAllocationController extends AbstractController
{
    const ACTIVE_TAB = 'binallocation';

    /** @var StockBinRepository */
    private $binRepo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->binRepo = $em->getRepository(StockBin::class);
    }

    /**
     * Show bins at the supplier's facility and what stock from them is
     * allocated to.
     *
     * @Route("/{id}/bin-allocation/", name="supplier_bin_allocation")
     */
    public function list(Supplier $supplier)
    {
        $this->checkDashboardAccess($supplier);

        $bins = $this->binRepo->findNeededAtSupplier($supplier);

        $bins = array_filter($bins, [$this, 'filterBin']);

        $mapping = $this->constructMapping($bins);

        return $this->render('supplier/allocation/binallocation.html.twig', [
            'supplier' => $supplier,
            'facility' => $supplier->getFacility(),
            'bins' => $bins,
            'mapping' => $mapping,
            'activeTab' => self::ACTIVE_TAB,
        ]);
    }

    protected function checkDashboardAccess(Supplier $supplier)
    {
        $this->denyAccessUnlessGranted(SupplierVoter::DASHBOARD, $supplier);
    }

    private function filterBin(StockBin $bin): bool {
        $allocations = $this->filterAllocations($bin->getAllocations());

        $purchaseOrders = array_unique(array_map(function (StockAllocation $alloc) {
            return $alloc->getConsumer()->getOrderNumber();
        }, $allocations));

        return count($purchaseOrders) > 1;
    }

    /**
     * @param StockAllocation[] $allocations
     * @return array
     */
    private function filterAllocations(array $allocations): array {
        return array_filter($allocations, function (StockAllocation $alloc) {
            return !$alloc->isForMissingStock()
                && $alloc->getConsumer() instanceof WorkOrder
                && $alloc->getConsumer()->isSent()
                && !$alloc->getConsumer()->isClosed();
        });
    }

    /**
     * @param StockBin[] $bins
     * @return array
     */
    private function constructMapping(array $bins): array
    {
        $map = [];
        foreach ($bins as $bin) {
            $allocations = $this->filterAllocations($bin->getAllocations());
            $pos = array_map(function (StockAllocation $alloc) {
                return $alloc->getQtyAllocated() . ' to PO ' . $alloc->getConsumer()->getOrderNumber();
            }, $allocations);
            $map[$bin->getId()] = $pos;
        }

        return $map;
    }
}
