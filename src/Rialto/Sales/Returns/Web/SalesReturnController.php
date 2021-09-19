<?php

namespace Rialto\Sales\Returns\Web;

use Exception;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Database\Orm\EntityList;
use Rialto\Printing\Printer\PrinterException;
use Rialto\Sales\Returns\Disposition\SalesReturnDisposition;
use Rialto\Sales\Returns\Disposition\SalesReturnProcessing;
use Rialto\Sales\Returns\Disposition\SalesReturnResults;
use Rialto\Sales\Returns\Disposition\Web\SalesReturnResultsType;
use Rialto\Sales\Returns\Receipt\SalesReturnReceipt;
use Rialto\Sales\Returns\Receipt\SalesReturnReceiver;
use Rialto\Sales\Returns\Receipt\Web\SalesReturnReceiptType;
use Rialto\Sales\Returns\SalesReturn;
use Rialto\Sales\Returns\SalesReturnRepository;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * For managing sales returns
 */
class SalesReturnController extends RialtoController
{
    /** @var SalesReturnRepository */
    private $repo;

    /** @var SalesReturnReceiver */
    private $receiver;

    /** @var SalesReturnDisposition */
    private $disposition;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->getRepository(SalesReturn::class);
        $this->receiver = $this->get(SalesReturnReceiver::class);
        $this->disposition = $this->get(SalesReturnDisposition::class);
    }

    /**
     * @Route("/sales/return/", name="sales_return_list")
     * @Method("GET")
     * @Template("sales/return/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::CUSTOMER_SERVICE);
        $form = $this->createForm(ListFilterType::class);
        $form->submit($request->query->all());
        $results = new EntityList($this->repo, $form->getData());

        return [
            'form' => $form->createView(),
            'returns' => $results,
        ];
    }

    /**
     * @Route("/sales/return/{id}/", name="sales_return_view")
     * @Method("GET")
     * @Template("sales/return/view.html.twig")
     */
    public function viewAction(SalesReturn $rma)
    {
        $this->denyAccessUnlessGranted(Role::CUSTOMER_SERVICE);
        return ['entity' => $rma];
    }

    /**
     * @Route("/Debtor/Transaction/{id}/salesReturn/",
     *   name="Sales_SalesReturn_create")
     *
     * @param $invoice DebtorInvoice
     */
    public function createAction(DebtorTransaction $invoice, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::ACCOUNTING, Role::CUSTOMER_SERVICE]);

        if (! $invoice->isInvoice()) {
            throw $this->badRequest("$invoice is not an invoice");
        }
        $rma = $this->repo->findExisting($invoice);
        if ($rma) {
            return $this->redirectToRoute('Sales_SalesReturn_edit', [
                'id' => $rma->getId(),
            ]);
        }
        $user = $this->getCurrentUser();
        $rma = new SalesReturn($invoice, $user);
        $rma->createReplacementOrder = true;

        return $this->processForm($rma, $request);
    }

    private function processForm(SalesReturn $rma, Request $request, $cancelUri = null)
    {
        $rma->populateMissingItemsForEditing();
        $rma->loadOriginalProducers($this->dbm);
        $form = $this->createForm(SalesReturnType::class, $rma);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $rma->pruneUnauthorizedItems();
            $this->dbm->beginTransaction();
            try {
                $this->dbm->persist($rma);
                $this->dbm->flush();
                $rma->syncReplacementOrder($this->dbm);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            return $this->redirectToView($rma);
        }

        return $this->render('sales/return/edit.html.twig', [
            'form' => $form->createView(),
            'rma' => $rma,
            'cancelUri' => $cancelUri,
        ]);
    }

    private function redirectToView(SalesReturn $rma)
    {
        $url = $this->viewUrl($rma);
        return $this->redirect($url);
    }

    private function viewUrl(SalesReturn $rma)
    {
        return $this->generateUrl('sales_return_view', [
            'id' => $rma->getId(),
        ]);
    }

    /**
     * @Route("/Sales/SalesReturn/{id}", name="Sales_SalesReturn_edit")
     */
    public function editAction(SalesReturn $rma, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::ACCOUNTING, Role::CUSTOMER_SERVICE]);
        $cancelUri = $this->viewUrl($rma);
        return $this->processForm($rma, $request, $cancelUri);
    }


    /**
     * @Route("/Sales/SalesReturn/{id}/receive", name="Sales_SalesReturn_receive")
     * @Template("sales/return/receive.html.twig")
     */
    public function receiveAction(SalesReturn $rma, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::WAREHOUSE);

        $receivingLocation = Facility::fetchProductTesting($this->dbm);
        $receipt = new SalesReturnReceipt($rma, $receivingLocation);

        foreach ($rma->getLineItems() as $lineItem) {
            $receipt->addLineItem($lineItem);
        }
        $form = $this->createForm(SalesReturnReceiptType::class, $receipt);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->receiver->receive($receipt);
                $this->dbm->flushAndCommit();
                return $this->instructionLabelsForm($receipt);
            } catch (PrinterException $ex) {
                $this->dbm->rollBack();
                $this->logException($ex);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'salesReturn' => $rma,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/Sales/SalesReturn/{id}/disposition", name="Sales_SalesReturn_disposition")
     * @Template("sales/return/disposition.html.twig")
     */
    public function dispositionAction(SalesReturn $rma, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::WAREHOUSE);
        $testingLoc = Facility::fetchProductTesting($this->dbm);
        $workingLoc = Facility::fetchHeadquarters($this->dbm);
        $results = new SalesReturnResults($rma, $testingLoc, $workingLoc);

        foreach ($rma->getLineItems() as $rmaItem) {
            $itemDisp = $results->createItem($rmaItem);
            $results->addItem($itemDisp);
        }
        $form = $this->createForm(SalesReturnResultsType::class, $results);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->disposition->dispose($results);
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            return $this->instructionLabelsForm($results);
        }

        return [
            'salesReturn' => $rma,
            'form' => $form->createView(),
        ];
    }

    private function instructionLabelsForm(SalesReturnProcessing $results)
    {
        return $this->render('sales/return/instructions.html.twig', [
            'instructions' => $results->getInstructions(),
            'selected' => $results->getLabels(),
            'salesReturn' => $results->getSalesReturn(),
        ]);
    }
}
