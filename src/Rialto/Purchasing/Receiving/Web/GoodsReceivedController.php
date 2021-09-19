<?php

namespace Rialto\Purchasing\Receiving\Web;

use Rialto\Allocation\Dispatch\Web\DispatchInstructionsController;
use Rialto\Database\Orm\EntityList;
use Rialto\Exception\InvalidDataException;
use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Receiving\GoodsReceivedItem;
use Rialto\Purchasing\Receiving\GoodsReceivedNotice;
use Rialto\Purchasing\Receiving\GoodsReceivedNoticeRepository;
use Rialto\Purchasing\Receiving\Receiver;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Controller for receiving purchase and work orders.
 */
class GoodsReceivedController extends RialtoController
{
    /** @var Receiver */
    private $receiver;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->receiver = $container->get(Receiver::class);
    }

    /**
     * @Route("/receiving/grn/", name="grn_list")
     * @Method("GET")
     * @Template("purchasing/receiving/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        /** @var GoodsReceivedNoticeRepository $repo */
        $repo = $this->getRepository(GoodsReceivedNotice::class);
        $form = $this->createForm(GrnFilter::class);
        $form->submit($request->query->all());
        $list = new EntityList($repo, $form->getData());

        return [
            'grns' => $list,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/receiving/grn/{id}/", name="grn_view")
     * @Method("GET")
     * @Template("purchasing/receiving/view.html.twig")
     */
    public function viewAction(GoodsReceivedNotice $grn)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $dispatchCtrl = DispatchInstructionsController::class;
        return [
            'entity' => $grn,
            'dispatchInstructions' => "$dispatchCtrl::formAction",
        ];
    }

    /**
     * Choose and delete Stock Allocation on large scale
     *
     * @Route("/receiving/grn/{id}/reverseItems",
     *   name="receiving_grn_reverse_items")
     * @Template("purchasing/receiving/choose-grn-items-to-reverse.html.twig")
     */
    public function chooseStockAllocationAction(GoodsReceivedNotice $grn, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);

        /** @var GoodsReceivedItem[] $items */
        $items = $grn->getItems();
        $form = $this->createForm(GoodsReceivedItemsSelectAndDeleteType::class, $grn);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                foreach ($items as $item) {
                    if ($item->getQtyToReverse() <= $item->getQtyReceived()) {
                        $transaction = $this->receiver->reverseReceipt($item, $item->getQtyToReverse());
                        $this->dbm->persist($transaction);
                    }
                }
                $this->dbm->flushAndCommit();
                $this->logNotice(sprintf('Reversed for GRN %s.',
                    $grn->getId()));
            } catch (InvalidDataException $ex) {
                $this->dbm->rollBack();
                return JsonResponse::fromException($ex);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $uri = $this->viewUrl($grn);
            return JsonResponse::javascriptRedirect($uri);
        }

        return [
            'grn' => $grn,
            'items'=> $items,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/supplier/grn/receiving/{id}/", name="supplier_grn_view")
     * @Method("GET")
     * @Template("purchasing/receiving/supplierView.html.twig")
     */
    public function supplierViewAction(GoodsReceivedNotice $grn)
    {
        $this->denyAccessUnlessGranted(Role::SUPPLIER_SIMPLE);
        return [
            'entity' => $grn,
        ];
    }

    /**
     * @Route("/supplier/grn/{id}/pdf", name="supplier_grn_pdf")
     */
    public function pdfAction(GoodsReceivedNotice $grn)
    {
        $this->denyAccessUnlessGranted([Role::EMPLOYEE, Role::SUPPLIER_SIMPLE]);
        $pdfData = $this->generatePdf($grn);
        $filename = "Receipt" . $grn->getId() . '.pdf';
        return PdfResponse::create($pdfData, $filename);
    }

    private function generatePdf(GoodsReceivedNotice $grn)
    {
        $generator = $this->get(PdfGenerator::class);
        return $generator->render(
            'purchasing/receiving/receiptpdf.tex.twig', [
            'grn' => $grn,
        ]);
    }

    /**
     * @Route("/receiving/order/{id}/receive/", name="receive_po")
     * @Route("/Purchasing/PurchaseOrder/{id}/receive/")
     * @Template("purchasing/receiving/fromPurchaseOrder.html.twig")
     */
    public function fromPurchaseOrderAction(PurchaseOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $template = new GoodsReceived($order);
        $returnUri = $this->getReturnUri($this->poUrl($order));

        $deliveryLocation = $order->getDeliveryLocation();
        $userLocation = $this->getCurrentUser()->getDefaultLocation();

        $displayMessage = "";
        if ($deliveryLocation == null || $userLocation == null) {
            $displayMessage = "Cannot compare address, be careful.";
            $template->setAllowReceive(false);
        } else {
            $deliveryLocationId = $deliveryLocation->getId();
            $userLocationId = $userLocation->getId();
            if ($deliveryLocationId == $userLocationId) {
                $displayMessage = "Delivery address matches with your location.";
                $template->setAllowReceive(true);
            } else {
                $displayMessage = "Delivery address does not match with your location, be careful.";
                $template->setAllowReceive(false);
            }
        }

        $form = $this->createForm(GoodsReceivedType::class, $template);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $grn = $template->create($this->getCurrentUser());
                $this->dbm->persist($grn);
                $this->dbm->flush();

                $this->receiver->receive($grn);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $this->logNotice(ucfirst(sprintf("%s received successfully.",
                $grn->getDescription()
            )));
            return $this->redirectToView($grn);
        }

        return [
            'order' => $order,
            'form' => $form->createView(),
            'cancelUri' => $returnUri,
            'displayMessage' => $displayMessage,
        ];
    }

    private function poUrl(PurchaseOrder $order)
    {
        return $this->generateUrl('purchase_order_view', [
            'order' => $order->getId(),
        ]);
    }

    private function redirectToView(GoodsReceivedNotice $grn)
    {
        $url = $this->viewUrl($grn);
        return $this->redirect($url);
    }

    private function viewUrl(GoodsReceivedNotice $grn)
    {
        return $this->generateUrl('grn_view', ['id' => $grn->getId()]);
    }

    /**
     * Used to reverse the receipt of an item.
     *
     * @Route("/Receiving/GoodsReceivedItem/{id}/reverse/",
     *   name="reverse_grn_item")
     */
    public function reverseAction(GoodsReceivedItem $grnItem, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::RECEIVING);
        if ($grnItem->getQtyPreviouslyReceived() <= 0) {
            throw $this->badRequest();
        }
        $form = $this->createFormBuilder()
            ->add('quantity', IntegerType::class, [
                'label' => 'Qty to reverse',
                'constraints' => [
                    new Assert\Range([
                        'min' => 1,
                        'minMessage' => 'Quantity to reverse must be positive.',
                        'max' => $grnItem->getQtyReceived(),
                    ]),
                    new Assert\Type(['type' => 'integer']),
                ],
            ])
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $quantity = $data['quantity'];
                $this->dbm->beginTransaction();
                try {
                    $transaction = $this->receiver->reverseReceipt($grnItem, $quantity);
                    $this->dbm->persist($transaction);
                    $this->dbm->flushAndCommit();
                } catch (InvalidDataException $ex) {
                    $this->dbm->rollBack();
                    return JsonResponse::fromException($ex);
                } catch (\Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }

                $this->logNotice(sprintf('Reversed %s units of GRN item %s.',
                    number_format($quantity),
                    $grnItem->getId()));
                $uri = $this->viewUrl($grnItem->getGoodsReceivedNotice());
                return JsonResponse::javascriptRedirect($uri);
            } else {
                return JsonResponse::fromInvalidForm($form);
            }
        }

        return $this->render(
            'core/form/dialogForm.html.twig', [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
        ]);
    }

    /**
     * Waste any in-process units and close the work order.
     *
     * @Route("/Manufacturing/WorkOrder/{id}/waste/",
     *   name="Receiving_WorkOrder_waste")
     * @Template("purchasing/receiving/waste.html.twig")
     */
    public function wasteAction(WorkOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        if (!$order->hasWorkInProcess()) {
            throw $this->badRequest();
        }
        $receiver = $this->getCurrentUser();
        $grn = new GoodsReceivedNotice($order->getPurchaseOrder(), $receiver);
        $grnItem = $grn->addItem($order, $order->getQtyInProcess());
        $grnItem->setDiscarded();

        $form = $this->createForm(GrnWasteType::class, $grn);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->dbm->persist($grn);
                $this->dbm->flush();

                $this->receiver->receive($grn);

                $order->cancel();
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $this->logNotice(ucfirst(sprintf("%s wasted successfully.",
                $order
            )));
            $uri = $this->poUrl($order->getPurchaseOrder());
            return JsonResponse::javascriptRedirect($uri);
        }

        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
        ];
    }
}
