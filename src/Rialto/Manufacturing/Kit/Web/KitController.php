<?php

namespace Rialto\Manufacturing\Kit\Web;

use Rialto\Alert\LinkResolution;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Allocation\InvalidAllocationException;
use Rialto\Allocation\Dispatch\Web\DispatchInstructionsController;
use Rialto\Allocation\Web\InvalidAllocationAlert;
use Rialto\Email\MailerInterface;
use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Manufacturing\Kit\Email\WorkOrderKitEmail;
use Rialto\Manufacturing\Kit\KitRequirement;
use Rialto\Manufacturing\Kit\KitRequirementAllocation;
use Rialto\Manufacturing\Kit\WorkOrderKit;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Security\Role\Role;
use Rialto\Shipping\Shipment\Web\ShipmentOptionsType;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Bin\StockBinSplit;
use Rialto\Stock\Bin\StockBinSplitter;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Transfer\Orm\TransferRepository;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Stock\Transfer\TransferService;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * For preparing transfers of stock needed for work orders ("kits").
 */
class KitController extends RialtoController
{
    /** @var TransferRepository */
    private $trRepo;

    /** @var PurchaseOrderRepository */
    private $poRepo;

    /** @var TransferService */
    private $transferSvc;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->trRepo = $this->dbm->getRepository(Transfer::class);
        $this->poRepo = $this->dbm->getRepository(PurchaseOrder::class);
        $this->transferSvc = $container->get(TransferService::class);
    }

    /**
     * Create a transfer from HQ to $dest.
     *
     * @Route("/manufacturing/kit/{id}/", name="manufacturing_kit_create")
     * @Template("manufacturing/kit/create.html.twig")
     */
    public function createAction(Facility $dest, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::WAREHOUSE);
        if ($dest->isHeadquarters()) {
            throw $this->badRequest("$dest is not a valid kit destination.");
        }
        $origin = Facility::fetchHeadquarters($this->dbm);

        $transfer = $this->trRepo->findEmptyOrCreate($origin, $dest);

        $orders = $this->poRepo->queryOrdersToKitByDestination($dest)
            ->getQuery()->getResult();
        $this->preSelectOrders($transfer, $orders, $request->get('po'));

        $form = $this->createForm(BeginKitType::class, $transfer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($transfer);
            $this->dbm->flush();
            return $this->redirectToRoute('manufacturing_kit_items', [
                'id' => $transfer->getId()
            ]);
        }

        return [
            'dest' => $dest,
            'orders' => $orders,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param PurchaseOrder[] $orders
     * @param string $orderID
     */
    private function preSelectOrders(Transfer $transfer, array $orders, $orderID)
    {
        foreach ($orders as $order) {
            if ($order->getId() == $orderID) {
                $transfer->resetPurchaseOrders($order);
                break;
            }
        }
    }

    /**
     * Add items to an empty transfer.
     *
     * @Route("/manufacturing/transfer/{id}/items/", name="manufacturing_kit_items")
     * @Template("manufacturing/kit/items.html.twig")
     */
    public function itemsAction(Transfer $transfer, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::WAREHOUSE);
        if ($transfer->isSent()) {
            throw $this->badRequest("$transfer is already sent");
        }
        $kit = new WorkOrderKit($transfer);
        $form = $this->createNamedBuilder('sendkit', $kit)
            ->add('shippingMethod', ShipmentOptionsType::class, [
                'salesOrder' => $transfer,
                'required' => true,
                'placeholder' => '-- Select Shipping Method'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Kit this transfer',
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $kit->populateTransfer();
                $this->transferSvc->kit($transfer);
                $this->dbm->flush();
                $this->sendEmail($kit);
                $this->dbm->flushAndCommit();

                $this->logNotice(ucfirst("$transfer has been created successfully."));
                return $this->redirectToRoute('stock_transfer_view', [
                    'transfer' => $transfer->getId(),
                ]);
            } catch (InvalidAllocationException $ex) {
                $this->dbm->rollBack();
                $alert = InvalidAllocationAlert::fromInvalidAllocationException($ex);
                $resolution = new LinkResolution(
                    $this->generateUrl('allocation_list', [
                        'stockItem' => $ex->getAllocation()->getSku(),
                    ]),
                    'Click here to delete the invalid allocation.'
                );
                $alert->setResolution($resolution);
                $this->logAlert($alert);
                return $this->redirect($this->getCurrentUri());
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        $dispatchCtrl = DispatchInstructionsController::class;
        $kitCtrl = self::class;
        return [
            'transfer' => $transfer,
            'kit' => $kit,
            'form' => $form->createView(),
            'dispatchInstructions' => "$dispatchCtrl::formAction",
            'kitItem' => "$kitCtrl::itemAction",
        ];
    }

    private function sendEmail(WorkOrderKit $kit)
    {
        $email = new WorkOrderKitEmail($kit, $this->getDefaultCompany());
        if ($email->hasRecipients()) {
            $mailer = $this->get(MailerInterface::class);
            $mailer->send($email);
        } else {
            $this->logWarning(sprintf('%s has no kit contacts.',
                $kit->getSupplier()
            ));
        }
    }

    /**
     * Print a picklist of the items to transfer.
     *
     * @Route("/manufacturing/transfer/{id}/picklist/", name="manufacturing_kit_picklist")
     */
    public function picklistAction(Transfer $transfer)
    {
        $this->denyAccessUnlessGranted(Role::WAREHOUSE);
        $kit = new WorkOrderKit($transfer);
        $generator = $this->get(PdfGenerator::class);
        $pdfData = $generator->render(
            'manufacturing/kit/picklist.tex.twig', [
            'kit' => $kit,
        ]);
        $filename = "TransferPicklist" . $transfer->getId();
        return PdfResponse::create($pdfData, $filename);
    }


    /**
     * Prepare an item in a kit.
     *
     * @Route("/manufacturing/transfer/{id}/items/{fullSku}/",
     *   name="manufacturing_kit_item")
     * @Template("manufacturing/kit/item.html.twig")
     */
    public function itemAction(Transfer $transfer, $fullSku, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::WAREHOUSE);

        $kit = new WorkOrderKit($transfer);
        $kitReq = $kit->getRequirement($fullSku);

        $allocator = new KitRequirementAllocation($this->dbm, $kitReq);

        $action = $this->generateUrl('manufacturing_kit_item', [
            'id' => $transfer->getId(),
            'fullSku' => $fullSku,
        ]);
        $bins = $allocator->getBinOptions();

        /** @var StockBin $closestBinAccordingToNeed */
        $closestBinAccordingToNeed = $this->getClosestBinAccordingToNeed($bins, $kitReq);


        /** @var $form FormInterface */
        $form = $this->createFormBuilder($allocator)
            ->setAction($action)
            ->setAttribute('class', 'standard')
            ->add('bins', EntityType::class, [
                'class' => StockBin::class,
                'choices' => $bins,
                'expanded' => true,
                'multiple' => true,
                'label' => false,
                'choice_label' => 'labelWithLocation',
            ])
            ->add('manual', SubmitType::class, [
                'label' => 'Use selected bins',
            ])
            ->add('auto', SubmitType::class, [
                'label' => 'Auto-allocate',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $factory = $this->getAllocationFactory();
            $this->dbm->beginTransaction();
            try {
                if ($form->get('auto')->isClicked()) {
                    $allocator->autoAllocate($factory);
                } else {
                    $allocator->allocateFromSelected($factory);
                }
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            return $this->redirect($action);
        }

        return [
            'transfer' => $transfer,
            'kitReq' => $kitReq,
            'form' => $form->createView(),
            'bins' => $bins,
            'next' => $this->generateUrl('manufacturing_kit_items', [
                'id' => $transfer->getId(),
            ]),
        ];
    }

    /**
     * @param StockBin[] $bins
     * @param KitRequirement $kitRequirement
     * @return StockBin
     */
    private function getClosestBinAccordingToNeed(array $bins, KitRequirement $kitRequirement)
    {
        $qtyNeeded = intval($kitRequirement->getGrossQtyNeeded());

        if (empty($bins)) {
            return null;
        }

        $closestIndex = 0;

        $smallestGap = abs($bins[$closestIndex]->getQtyRemaining() - $qtyNeeded);

        for ($i = 0; $i < sizeof($bins); $i++) {
            if (abs($bins[$i]->getQtyRemaining() - $qtyNeeded) < $smallestGap) {
                $closestIndex = $i;
                $smallestGap = abs($bins[$i]->getQtyRemaining() - $qtyNeeded);
            }
        }

        return $bins[$closestIndex];
    }


    /** @return AllocationFactory */
    private function getAllocationFactory()
    {
        return $this->get(AllocationFactory::class);
    }

    /**
     * Split a bin before transferring it so that only the required amount
     * is sent.
     *
     * @Route("/manufacturing/kit/{id}/{fullSku}/split/{binId}/",
     *   name="manufacturing_kit_split")
     * @Template("manufacturing/kit/split.html.twig")
     */
    public function splitAction(Transfer $transfer, string $fullSku, string $binId, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::WAREHOUSE);
        $kit = new WorkOrderKit($transfer);
        $kitReq = $kit->getRequirement($fullSku);

        $repo = $this->dbm->getRepository(StockBin::class);
        /* @var $bin StockBin */
        $bin = $repo->findById($binId);

        if (!$bin->canBeSplit()) {
            throw $this->badRequest();
        } elseif ($bin->getQtyRemaining() > $kitReq->getQtyUnallocated()){
            $hasSplitDialog = true;
            $style = $bin->getBinStyle() ?: 'bin';
            $split = new StockBinSplit($bin);
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
                } catch (\Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }
                $this->logNotice("Created $newBin successfully.");
                $original = $this->generateUrl('manufacturing_kit_items', [
                    'id' => $transfer->getId()
                ]);
                return $this->redirect($original);
            }
        } else {
            $form = $this->createFormBuilder()
                ->getForm();
            $hasSplitDialog = false;
        }

        return [
            'bin' => $bin,
            'hasSplitDialog' => $hasSplitDialog,
            'form' => $form->createView(),
        ];
    }
}
