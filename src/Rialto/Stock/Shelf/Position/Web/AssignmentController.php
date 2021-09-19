<?php

namespace Rialto\Stock\Shelf\Position\Web;

use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\Label\BinLabelPrintQueue;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Shelf\Position\PositionAssigner;
use Rialto\Stock\Shelf\Velocity;
use Rialto\Stock\Shelf\Velocity\VelocityQueryBuilder;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * For manually assigning shelf positions to bins.
 */
class AssignmentController extends RialtoController
{
    /**
     * @var StockBinRepository
     */
    private $binRepo;

    /**
     * @var PositionAssigner
     */
    private $assigner;

    /**
     * @var BinLabelPrintQueue
     */
    private $printQueue;

    protected function init(ContainerInterface $container)
    {
        $this->binRepo = $this->dbm->getRepository(StockBin::class);
        $this->assigner = $container->get(PositionAssigner::class);
        $this->printQueue = $container->get(BinLabelPrintQueue::class);
    }

    /**
     * Batch-shelve or unshelve a bunch of bins at once.
     *
     * @Route("/stock/shelve/bins/", name="stock_shelve_bins")
     * @Template("stock/shelf/assign-bins.html.twig")
     */
    public function batchAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $filterForm = $this->createForm(AssignmentFilterForm::class);
        $filterForm->submit($request->query->all());
        $filters = $filterForm->getData();
        $filters['inTransit'] = 'no';
        $bins = $this->binRepo->queryByFilters($filters)->getResult();
        $velocities = $this->loadVelocities($filters);
        if ($request->get('csv')) {
            return $this->downloadCsv($bins, $velocities);
        }

        $nextUrl = $this->generateUrl('stock_shelve_bins', $request->query->all());
        $batch = new BatchAssigner();
        $batch->setSession($request->getSession());
        $options = ['bins' => $bins, 'action' => $nextUrl];
        $assignForm = $this->createForm(BatchAssignerForm::class, $batch, $options);

        $batch->setAction($request->get('action'));
        $assignForm->handleRequest($request);
        if ($assignForm->isSubmitted() && $assignForm->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $batch->updateSelected($this->assigner);
                $batch->printLabelsIfNeeded($this->printQueue);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $this->logNotice(sprintf('Updated %s of %s bins.',
                $batch->getNumUpdated(),
                $batch->getNumSelected()));
            return $this->redirect($nextUrl);
        }

        return [
            'filterForm' => $filterForm->createView(),
            'bins' => $bins,
            'velocities' => $velocities,
            'assignForm' => $assignForm->createView(),
        ];
    }

    private function loadVelocities(array $filters)
    {
        $qb = new VelocityQueryBuilder($this->dbm);
        if ($filters['facility']) {
            $qb->byFacility($filters['facility']);
        }
        if ($filters['sku']) {
            $qb->byItem($filters['sku']);
        }
        if ($filters['rack']) {
            $qb->byRack($filters['rack']);
        }
        if ($filters['_limit']) {
            $qb->setMaxResults($filters['_limit']);
        }
        return $qb->getIndexedResult();
    }

    /**
     * @param StockBin[] $bins
     * @param array $velocities
     */
    private function downloadCsv(array $bins, array $velocities)
    {
        $data = [];
        foreach ($bins as $bin) {
            $facilityId = $bin->getFacility()->getId();
            $sku = $bin->getSku();
            $velocity = isset($velocities[$sku][$facilityId])
                ? $velocities[$sku][$facilityId]
                : Velocity::LOW;
            $data[] = [
                'bin' => $bin->getId(),
                'sku' => $sku,
                'version' => $bin->getVersion(),
                'customization' => $bin->getCustomization(),
                'quantity' => $bin->getQtyRemaining(),
                'style' => $bin->getBinStyle(),
                'velocity' => $velocity,
                'shelf' => $bin->getShelfPosition(),
            ];
        }

        $csv = new CsvFileWithHeadings();
        $csv->parseArray($data);

        return FileResponse::fromData($csv->toString(), 'bin_shelf.csv', 'text/csv');
    }
}
