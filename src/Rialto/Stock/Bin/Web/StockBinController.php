<?php

namespace Rialto\Stock\Bin\Web;

use FOS\RestBundle\View\View;
use Rialto\Database\Orm\EntityList;
use Rialto\Printing\Printer\PrinterException;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Bin\StockBinEvent;
use Rialto\Stock\Bin\StockBinSplit;
use Rialto\Stock\Bin\StockBinSplitter;
use Rialto\Stock\Count\StockAdjustment;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Move\Orm\StockMoveRepository;
use Rialto\Stock\Move\StockMove;
use Rialto\Stock\StockEvents;
use Rialto\Supplier\SupplierVoter;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for editing stock bins
 */
class StockBinController extends RialtoController
{
    const ACTIVE_TAB = 'stockbin';

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    /**
     * Allow the user to search for a bin and jump directly to it if it is
     * found.
     *
     * @Route("/stock/select-bin/", name="stock_bin_select")
     * @Method("GET")
     */
    public function selectAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $binID = $request->get('id');
        if (!$binID) {
            throw $this->badRequest("No bin ID given.");
        } elseif (!$this->dbm->find(StockBin::class, $binID)) {
            $this->logError("No such bin $binID");
            return $this->redirectToRoute('stock_bin_list');
        }
        return $this->redirectToRoute('stock_bin_view', [
            'bin' => $binID,
        ]);
    }

    /**
     * API lookup bins available at any facility given a sku.
     *
     * @api for Rialto
     *
     * @Route("/api/v2/stock/bin/")
     * @Method("GET")
     */
    public function lookupBySkuAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $stockItemSku = $request->get('sku');
        $stockBinRepo = $this->dbm->getRepository(StockBin::class);
        if ($request->get('version') !== null) {
            $version = $request->get('version');
            $list = $stockBinRepo->findBySku($stockItemSku, $version);
            return View::create(StockBinBySkuFacade::fromList($list));
        }
        $list = $stockBinRepo->findBySku($stockItemSku);
        return View::create(StockBinBySkuFacade::fromList($list));
    }

    /**
     * @Route("/stock/bin/", name="stock_bin_list")
     * @Method("GET")
     * @Template("stock/bin/bin-list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $form = $this->createForm(BinFilterForm::class);
        $form->submit($request->query->all());
        $repo = $this->getRepository(StockBin::class);
        $results = new EntityList($repo, $form->getData());
        return [
            'form' => $form->createView(),
            'bins' => $results,
        ];
    }

    /**
     * Show bins at the supplier's facility and bins' information
     *
     * @Route("/supplier/{id}/stock-bin/", name="supplier_stock_bin")
     */
    public function list(Supplier $supplier, Request $request)
    {
        $this->denyAccessUnlessGranted(SupplierVoter::DASHBOARD, $supplier);
        /** @var StockBinRepository $repo */
        $repo = $this->getRepository(StockBin::class);

        $form = $this->createForm(StockBinFilterType::class);
        $form->submit($request->query->all());
        $filters = $form->getData();

        $facility = $supplier->getFacility();
        assertion($facility !== null);
        $builder = $repo->createBuilder()
            ->byFacility($facility)
            ->available()
            ->orderBySku()
            ->orderById();
        if ($filters['sku'] ?? null) {
            $builder->bysku($filters['sku']);
        }
        if ($filters['bin'] ?? null) {
            $builder->byId($filters['bin']);
        }
        $bins = $builder->getQuery()->getResult();

        return $this->render('stock/bin/supplier-bin-list.twig', [
            'supplier' => $supplier,
            'facility' => $supplier->getFacility(),
            'form' => $form->createView(),
            'bins' => $bins,
            'activeTab' => self::ACTIVE_TAB,
        ]);
    }

    /**
     * @Route("/stock/bin/{bin}/", name="stock_bin_view", options={"expose": true})
     * @Method("GET")
     * @Template("stock/bin/bin-view.html.twig")
     */
    public function viewAction(StockBin $bin)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        /** @var StockMoveRepository $repo */
        $repo = $this->getRepository(StockMove::class);
        $moveFilters = [
            'bin' => $bin->getId(),
            'showTransit' => 'yes',
            '_limit' => 0, // fetch all stock moves for this bin
        ];
        $stockMoves = new EntityList($repo, $moveFilters);
        return [
            'entity' => $bin,
            'moves' => $stockMoves,
        ];
    }

    /**
     * @Route("/Stock/StockBin/{id}/", name="stock_bin_edit")
     * @Template("stock/bin/bin-edit.html.twig")
     */
    public function editAction(StockBin $bin, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_CREATE);
        $adjustment = new StockAdjustment();
        $adjustment->setEventDispatcher($this->dispatcher());
        $bin->setNewQty($bin->getQuantity()); // default is no change
        $adjustment->addBin($bin);
        $form = $this->createForm(StockAdjustmentType::class, $adjustment);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $returnUrl = $this->getReturnUri($this->generateUrl('stock_bin_list', [
                'stockItem' => $bin->getSku(),
            ]));
            $this->dbm->beginTransaction();
            try {
                if ($adjustment->hasChanges()) {
                    $adjustment->adjust($this->dbm);
                }
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $event = new StockBinEvent($bin);
            $this->dispatcher->dispatch(StockEvents::STOCK_BIN_CHANGE, $event);
            $this->logNotice("$bin updated successfully.");

            return JsonResponse::javascriptRedirect($returnUrl);
        }

        return [
            'formAction' => $this->getCurrentUri(),
            'form' => $form->createView(),
            'bin' => $bin,
        ];
    }

    /**
     * @Route("/Stock/StockBin/{id}/updateAlloc", name="stock_bin_update_alloc")
     * @Template("stock/bin/bin-update-alloc.html.twig")
     */
    public function updateAlocatableAction(StockBin $bin, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_CREATE);
        $options = $this->createOptions($bin);
        $form = $this->createForm(BinUpdateAllocType::class, null, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $returnUrl = $this->generateUrl('stock_bin_list', [
                'stockItem' => $bin->getSku(),
            ]);
            $this->dbm->beginTransaction();
            try {
                $changeAllocatable = $form->get('allocatable')->getData();
                if ($changeAllocatable !== $bin->getAllocatable()){
                    $userName = $this->getCurrentUser()->getName();
                    $reason = $form->get('reason')->getData();
                    $bin->setAllocatableManual($changeAllocatable, $userName, $reason);
                }
                $this->dbm->flushAndCommit();
                $this->logNotice("$bin updated allocatable successfully.");
                return JsonResponse::javascriptRedirect($returnUrl);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'formAction' => $this->getCurrentUri(),
            'form' => $form->createView(),
            'bin' => $bin,
        ];

    }

    /** @return array */
    private function createOptions(StockBin $bin)
    {
        $options = [
            'stockBin' => $bin,
        ];
        return $options;
    }

    /**
     * Only administrators can manually create bins, because the unit cost
     * of the bin has to be set for accounting purposes.
     *
     * @Route("/stock/item/{item}/create-bin/{location}/", name="stock_bin_create")
     * @Template("stock/bin/bin-create.html.twig")
     */
    public function createAction(StockItem $item, Facility $location, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_CREATE);
        $version = $item->getShippingVersion();
        $bin = new StockBin($item, $location, $version);

        $adjustment = new StockAdjustment("Create new reel of $item");
        $adjustment->setEventDispatcher($this->dispatcher());
        $adjustment->addBin($bin);

        $options = ['action' => $this->getCurrentUri()];
        $form = $this->createForm(StockAdjustmentType::class, $adjustment, $options);
        $url = $this->generateUrl('stock_bin_list', [
            'stockItem' => $item->getSku(),
        ]);

        $message = '';
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($adjustment->hasChanges()) {
                $this->dbm->beginTransaction();
                try {
                    $this->dbm->persist($bin);
                    $this->dbm->flush(); // So the bin gets an ID

                    $adjustment->adjust($this->dbm);
                    $this->dbm->flushAndCommit();
                    $event = new StockBinEvent($bin);
                    $this->dispatcher->dispatch(StockEvents::STOCK_BIN_CHANGE, $event);

                    $this->logNotice(ucfirst("$bin created successfully."));
                    return JsonResponse::javascriptRedirect($url);
                } catch (PrinterException $ex) {
                    $this->dbm->rollBack();
                    $this->logException($ex);
                    return JsonResponse::fromException($ex);
                } catch (\Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }
            } else {
                $message = 'No changes selected.';
            }
        }

        return [
            'form' => $form->createView(),
            'message' => $message,
        ];
    }

    /**
     * @Route("/stock/bin/{id}/split", name="stock_bin_split")
     * @Template("stock/bin/bin-split.html.twig")
     */
    public function splitAction(StockBin $oldBin, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        if (!$oldBin->canBeSplit()) {
            throw $this->badRequest();
        }
        $style = $oldBin->getBinStyle() ?: 'bin';
        $split = new StockBinSplit($oldBin);
        /** @var FormInterface $form */
        $form = $this->createFormBuilder($split)
            ->setAction($this->getCurrentUri())
            ->add('qtyToSplit', IntegerType::class, [
                'label' => "Quantity on the new $style",
            ])
            ->add('split', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                /** @var $splitter StockBinSplitter */
                $splitter = $this->get(StockBinSplitter::class);
                $newBin = $splitter->split($split);
                $this->dbm->flushAndCommit();
                $oldBinEvent = new StockBinEvent($oldBin);
                $newBinEvent = new StockBinEvent($newBin);
                $this->dispatcher->dispatch(StockEvents::STOCK_BIN_CHANGE, $oldBinEvent);
                $this->dispatcher->dispatch(StockEvents::STOCK_BIN_CHANGE, $newBinEvent);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Created $newBin successfully.");
            $returnTo = $request->get('returnTo',
                $this->generateUrl('stock_bin_list', [
                    'stockItem' => $oldBin->getSku(),
                ]));
            return JsonResponse::javascriptRedirect($returnTo);
        }

        return [
            'oldBin' => $oldBin,
            'form' => $form->createView(),
        ];
    }

}
