<?php

namespace Rialto\Stock\Transfer\Web;

use Exception;
use Rialto\Database\Orm\EntityList;
use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Security\Role\Role;
use Rialto\Shipping\Method\Orm\ShippingMethodRepository;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Transfer\MissingTransferItem;
use Rialto\Stock\Transfer\Orm\TransferItemRepository;
use Rialto\Stock\Transfer\Orm\TransferRepository;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Stock\Transfer\TransferItem;
use Rialto\Stock\Transfer\TransferReceiver;
use Rialto\Stock\Transfer\TransferService;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for managing stock transfers.
 */
class TransferController extends RialtoController
{
    /** @var TransferService */
    private $transferSvc;

    /** @var TransferReceiver */
    private $receiver;

    /** @var ShippingMethodRepository */
    private $shippingMethodRepo;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->transferSvc = $container->get(TransferService::class);
        $this->receiver = $container->get(TransferReceiver::class);
        $this->shippingMethodRepo = $this->getRepository(ShippingMethod::class);
    }

    /**
     * @Route("/stock/transfer/", name="stock_transfer_list")
     * @Method("GET")
     * @Template("stock/transfer/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $form = $this->createForm(TransferListFiltersType::class);
        $form->submit($request->query->all());
        $repo = $this->getRepository(Transfer::class);
        /* @var $repo TransferRepository */
        $list = new EntityList($repo, $form->getData());

        return [
            'transfers' => $list,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/stock/transfer/{transfer}/", name="stock_transfer_view")
     * @Method("GET")
     * @Template("stock/transfer/view.html.twig")
     */
    public function viewAction(Transfer $transfer)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $ctrl = self::class;
        return [
            'entity' => $transfer,
            'inputTracking' => "$ctrl::inputTrackingNumberAction",
            'markAsSent' => "$ctrl::sentAction",
        ];
    }

    /**
     * Allows the user to manually create a stock transfer.
     *
     * This action is warehouse-only because transfers are typically created
     * automatically during normal workflows.
     *
     * @Route("/stock/item/{item}/transfer-from/{source}",
     *   name="stock_transfer_create",
     *   options={"expose"=true})
     * @Template("stock/transfer/create.html.twig")
     */
    public function createAction(StockItem $item, Facility $source, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::WAREHOUSE);
        $this->validateItem($item);
        $transfer = new Transfer($source);
        $transfer->setShippingMethod($this->shippingMethodRepo->findTransferDefault());

        $form = $this->createForm(TransferType::class, $transfer, [
            'item' => $item, 'from' => $source,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->dbm->persist($transfer);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Location transfer created.");
            return $this->redirect($this->viewUrl($transfer));
        }

        /** @var $repo FacilityRepository */
        $repo = $this->getRepository(Facility::class);
        return [
            'item' => $item,
            'source' => $source,
            'options' => $repo->findValidDestinations(),
            'form' => $form->createView(),
        ];
    }

    private function validateItem(StockItem $item)
    {
        if (!$item->isControlled()) {
            throw $this->badRequest("$item is not controlled");
        }
        if (!$item->isPhysicalPart()) {
            throw $this->badRequest("$item is not a physical part");
        }
    }

    private function viewUrl(Transfer $transfer)
    {
        return $this->generateUrl('stock_transfer_view', [
            'transfer' => $transfer->getId(),
        ]);
    }

    /**
     * User indicates that a transfer has been kitted and is ready for pickup.
     *
     * @Route("/stock/transfer/{transfer}/kitted/", name="stock_transfer_kitted")
     * @Method("POST")
     */
    public function kittedAction(Transfer $transfer, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $this->dbm->beginTransaction();
        try {
            $this->transferSvc->kit($transfer);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
        $next = $request->get('next', $this->viewUrl($transfer));
        return $this->redirect($next);
    }

    /**
     * User indicates that a transfer has been kitted and send directly.
     *
     * @Route("/stock/transfer/{transfer}/inputTrackingNumber/", name="stock_transfer_input_tracking_num")
     * @Method("POST")
     * @Template("form/minimal.html.twig")
     */
    public function inputTrackingNumberAction(Transfer $transfer, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('stock_transfer_input_tracking_num', [
                'transfer' => $transfer->getId(),
                'next' => $request->get('next'),
            ]))
            ->add('trackingNumbers', TextType::class, [
                'attr' => ['placeholder' => 'tracking number...'],
                'label' => false,
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Add a Tracking Number'
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                /** @var string $trackingNumber */
                $trackingNumber = $form->get('trackingNumbers')->getData();
                $this->transferSvc->inputTrackingNumber($transfer, $trackingNumber);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice(ucfirst("$transfer has been inputted Tracking Number."));
            $next = $request->get('next', $this->viewUrl($transfer));
            return $this->redirect($next);
        }
        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * User indicates that a transfer has been picked up and is in transit.
     *
     * @Route("/stock/transfer/{transfer}/sent/", name="stock_transfer_sent")
     * @Method("POST")
     * @Template("form/minimal.html.twig")
     */
    public function sentAction(Transfer $transfer, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $form = $this->createFormBuilder($transfer)
            ->setAction($this->generateUrl('stock_transfer_sent', [
                'transfer' => $transfer->getId(),
                'next' => $request->get('next'),
            ]))
            ->add('pickedUpBy', TextType::class, [
                'attr' => ['placeholder' => 'picked up by...'],
                'label' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Picked up'
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->transferSvc->send($transfer);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice(ucfirst("$transfer has been marked as sent."));
            $next = $request->get('next', $this->viewUrl($transfer));
            return $this->redirect($next);
        }
        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/Stock/Transfer/missingItems", name="Stock_Transfer_missingItems")
     * @Template("stock/transfer/missing-items.html.twig")
     */
    public function missingItemsAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::WAREHOUSE]);
        $filterForm = $this->createForm(MissingItemsType::class);
        $filterForm->submit($request->query->all());
        $filters = $filterForm->getData();
        $items = $this->findMissingItems($filters);

        $updateForm = $this->createUpdateForm($items);

        $updateForm->handleRequest($request);
        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $this->dbm->beginTransaction();
            try {
                foreach ($items as $missingItem) {
                    $this->receiver->resolveMissingItem($missingItem);
                }
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            return $this->redirect($this->getCurrentUri());
        }

        return [
            'filterForm' => $filterForm->createView(),
            'items' => $items,
            'updateForm' => $updateForm->createView(),
        ];
    }

    private function findMissingItems(array $filters)
    {
        $filters['missing'] = 'yes';
        $filters['_limit'] = 100;

        /** @var $itemRepo TransferItemRepository */
        $itemRepo = $this->getRepository(TransferItem::class);
        $items = $itemRepo->findByFilters($filters);
        $missingItems = [];
        foreach ($items as $item) {
            $missingItems[] = new MissingTransferItem($item);
        }
        return $missingItems;
    }

    private function createUpdateForm(array $items)
    {
        $container = new \stdClass();
        $container->items = $items;
        return $this->createFormBuilder($container)
            ->add('items', CollectionType::class, [
                'entry_type' => MissingTransferItemType::class,
                'error_bubbling' => true,
            ])
            ->getForm();
    }

    /**
     * @Route("/Stock/Transfer/{id}/pdf", name="Stock_Transfer_pdf")
     */
    public function pdfAction(Transfer $transfer)
    {
        $this->denyAccessUnlessGranted([Role::EMPLOYEE, Role::SUPPLIER_SIMPLE]);
        $pdfData = $this->generatePdf($transfer);
        $filename = "LocationTransfer" . $transfer->getId() . '.pdf';
        return PdfResponse::create($pdfData, $filename);
    }

    private function generatePdf(Transfer $transfer)
    {
        $generator = $this->get(PdfGenerator::class);
        return $generator->render(
            'stock/transfer/pdf.tex.twig', [
            'transfer' => $transfer,
        ]);
    }

    /**
     * Indicate receipt of a stock transfer.
     *
     * @Route("/transfer/{id}/receive", name="stock_transfer_receive")
     * @Template("stock/transfer/receive.html.twig")
     */
    public function receiveAction(Transfer $transfer, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        if ($transfer->isReceived()) {
            throw $this->badRequest("$transfer has already been received");
        } elseif (!$transfer->isSent()) {
            throw $this->badRequest("$transfer has not been sent yet");
        }

        $receipt = new TransferReceipt($transfer);
        $form = $this->createForm(TransferReceiptType::class, $receipt);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->receiver->receive($receipt);
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            return $this->redirect($this->viewUrl($transfer));
        }

        return [
            'form' => $form->createView(),
            'transfer' => $transfer,
            'cancelUri' => $this->viewUrl($transfer),
        ];
    }

    /**
     * @Route("/transfer/{transfer}/", name="stock_transfer_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Transfer $transfer, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        if ($transfer->isKitted()) {
            throw $this->badRequest("$transfer has already been sent");
        }
        $msg = "Deleted $transfer.";
        $this->dbm->remove($transfer);
        $this->dbm->flush();
        $this->logNotice($msg);
        $next = $request->get('next', $this->generateUrl('index'));
        return $this->redirect($next);
    }
}
