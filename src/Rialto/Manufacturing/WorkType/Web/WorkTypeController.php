<?php

namespace Rialto\Manufacturing\WorkType\Web;

use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkType\ProductLabelPrinter;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Printing\Printer\PrinterException;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\Document\ProductLabel;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * For managing and executing various types of manufacturing work.
 *
 * @see WorkType
 */
class WorkTypeController extends RialtoController
{
    /**
     * Do the first type of work needed by $workOrder.
     *
     * @Route("/manufacturing/workorder/{id}/worktype/",
     *   name="manufacturing_worktype_build")
     */
    public function buildAction(WorkOrder $workOrder, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::WAREHOUSE, Role::STOCK]);
        $workTypes = $workOrder->getWorkTypesNeeded();
        /* @var WorkType $workType */
        $workType = reset($workTypes);

        switch ($workType->getId()) {
            case WorkType::PRINTING:
                return $this->printing($workOrder, $request);
            default:
                return $this->link($workOrder, $workType);
        }
    }

    private function printing(WorkOrder $labelOrder, Request $request)
    {
        $error = '';
        $productOrder = $this->getProductOrderFromLabelOrder($labelOrder);
        $label = new ProductLabel($productOrder, $labelOrder->getQtyUnissued());
        $options = ['action' => $this->getCurrentUri()];
        $form = $this->createForm(PrintProductLabelType::class, $label, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->labelPrinter()->printLabels($label);
                $this->dbm->flushAndCommit();
                return $this->redirectToRoute('manufacturing_labels_issue', [
                    'id' => $labelOrder->getId(),
                    'next' => $request->get('next'),
                ]);
            } catch (PrinterException $ex) {
                $this->dbm->rollBack();
                $error = $ex->getMessage();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return $this->render('manufacturing/work-type/print.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    /* @return WorkOrder|object The work order for the product that needs labels. */
    private function getProductOrderFromLabelOrder(WorkOrder $labelOrder)
    {
        return $this->dbm->need(WorkOrder::class,
            (string) $labelOrder->getVersion());
    }

    /* @return ProductLabelPrinter|object */
    private function labelPrinter()
    {
        return $this->get(ProductLabelPrinter::class);
    }

    /**
     * Just render a link to the work order.
     */
    private function link(WorkOrder $order, WorkType $workType)
    {
        $url = $this->generateUrl('work_order_view', [
            'order' => $order->getId(),
        ]);
        $text = "$workType for $order";
        return new Response(sprintf('<a href="%s">%s</a>',
            htmlentities($url),
            htmlentities($text)
        ));
    }

    /**
     * @Route("/manufacturing/labels/{id}/issue/",
     *  name="manufacturing_labels_issue")
     */
    public function issueLabelsAction(WorkOrder $labelOrder, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::WAREHOUSE, Role::STOCK]);
        $message = $error = '';
        $productOrder = $this->getProductOrderFromLabelOrder($labelOrder);

        $options = ['action' => $this->getCurrentUri()];
        $form = $this->createForm(IssueProductLabelType::class, null, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $qty = $form->get('quantity')->getData();
                $this->labelPrinter()->issueLabels($labelOrder, $productOrder, $qty);
                $message = sprintf("Confirmed %s labels printed.", $qty);
                $this->dbm->flushAndCommit();
            } catch (PrinterException $ex) {
                $this->dbm->rollBack();
                $error = $ex->getMessage();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $url = $request->get('next');
            if ($error) {
                $this->logError($error);
            } elseif ($url) {
                $this->logNotice($message);
                return JsonResponse::javascriptRedirect($url);
            } elseif ($request->isXmlHttpRequest()) {
                /* User won't be able to see flash messages anyway. */
                $request->getSession()->getFlashBag()->clear();
            }
        }

        return $this->render('form/minimal.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'message' => $message,
        ]);
    }

}
