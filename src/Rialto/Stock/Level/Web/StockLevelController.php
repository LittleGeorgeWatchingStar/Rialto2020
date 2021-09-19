<?php

namespace Rialto\Stock\Level\Web;

use Doctrine\ORM\EntityManagerInterface;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Web\ActiveFacilityType;
use Rialto\Stock\Item\AssemblyStockItem;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Level\CompleteStockLevel;
use Rialto\Stock\Level\Orm\StockLevelStatusRepository;
use Rialto\Stock\Level\StockLevelBindingTransfer;
use Rialto\Stock\Level\StockLevelService;
use Rialto\Stock\Level\StockLevelStatus;
use Rialto\Stock\Level\StockLevelSynchronizer;
use Rialto\Stock\Level\StockLevelTransferItemCombineBins;
use Rialto\Stock\Transfer\Orm\TransferItemRepository;
use Rialto\Stock\Transfer\TransferItem;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class StockLevelController extends RialtoController
{
    /**
     * @var StockLevelStatusRepository
     */
    private $repo;

    /**
     * @var StockLevelSynchronizer
     */
    private $sync;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->dbm->getRepository(StockLevelStatus::class);
        $this->sync = $container->get(StockLevelSynchronizer::class);
        $this->em = $container->get(EntityManagerInterface::class);
    }

    /**
     * @Route("/Stock/StockLevelStatus/{stockCode}", name="Stock_StockLevel_list")
     * @Template("stock/level/list.html.twig")
     */
    public function listAction(StockItem $item)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        if ($item instanceof AssemblyStockItem) {
            return $this->redirectToRoute('stock_level_assembly', [
                'item' => $item->getSku(),
                'facility' => $this->getHeadquarters()->getId(),
            ]);
        } elseif (! $item->isPhysicalPart() ) {
            throw $this->badRequest("$item is not a physical part");
        }
        $builder = CompleteStockLevel::createBuilder($this->manager());
        $levels = $builder
            ->bySku($item->getSku())
            ->isActiveLocation()
            ->getStockLevels();

        /** @var $service StockLevelService */
        $service = $this->get(StockLevelService::class);

        $bindingTotalArray = [];
        foreach ($levels as $level) {
            $stockLevelLocation = $level->getLocation();
            $bindingTotal = new StockLevelTransferItemCombineBins($item, $level, $stockLevelLocation, $this->em);
            $bindingTotalArray[] = $bindingTotal;
        }

        return [
            'item' => $item,
            'bindings' => $bindingTotalArray,
            'service' => $service,
        ];
    }

    /**
     * @Route("/stock/assembly-level/{item}/",
     *     name="stock_level_assembly")
     * @Template("stock/level/assembly-level.html.twig")
     */
    public function viewAssemblyAction(AssemblyStockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        $options = ['method' => 'get', 'csrf_protection' => false];
        $form = $this->createNamedBuilder(null, null, $options)
            ->add('facility', ActiveFacilityType::class)
            ->getForm();
        $form->handleRequest($request);
        $facility = $form->get('facility')->getData();
        $level = $this->repo->getAssemblyStockLevel($item, $facility);

        return [
            'item' => $item,
            'level' => $level,
            'form' => $form->createView(),
        ];
    }

    /**
     * Ensure that stock level status records exist for all active locations.
     *
     * @Route("/stock/level/{item}/initialize", name="stock_level_init")
     * @Method("POST")
     */
    public function initializeAction(StockItem $item)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        if (!$item instanceof PhysicalStockItem) {
            throw $this->badRequest();
        }
        $this->repo->initializeStockLevels($item);
        $this->dbm->flush();
        return $this->redirectToRoute('Stock_StockLevel_list', [
            'stockCode' => $item->getSku(),
        ]);
    }

    /**
     * Notify remote systems (eg storefronts) of the current stock level.
     *
     * @Route("/Stock/StockLevelStatus/{item}/{location}",
     *   name="StockLevelStatus_notify")
     * @Method("POST")
     *
     * @param $item PhysicalStockItem
     */
    public function notifyAction(StockItem $item, Facility $location, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        if (! $item->isPhysicalPart() ) {
            throw $this->badRequest("$item is not a physical part");
        }

        $this->dbm->beginTransaction();
        try {
            $this->sync->ensureExists($item, $location);
            $this->dbm->flush();
            $update = $this->sync->loadUpdate($item, $location);
            $warnings = $this->sync->synchronize($update);
            $this->logWarnings($warnings);
            $warnings = $this->sync->syncAssemblies([$update], $location);
            $this->logWarnings($warnings);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
        $this->logNotice("Stock level for $item synchronized successfully.");
        $next = $request->get('next', $this->generateUrl('Stock_StockLevel_list', [
            'stockCode' => $item->getSku(),
        ]));
        return $this->redirect($next);
    }
}
