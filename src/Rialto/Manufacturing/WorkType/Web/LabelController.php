<?php

namespace Rialto\Manufacturing\WorkType\Web;

use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkType\ProductLabelPrinter;
use Rialto\Printing\Printer\PrinterException;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\Document\ProductLabel;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for printing labels for work orders.
 */
class LabelController extends RialtoController
{
    /**
     * @var ProductLabelPrinter
     */
    private $printer;

    protected function init(ContainerInterface $container)
    {
        $this->printer = $this->get(ProductLabelPrinter::class);
    }

    /**
     * Prints product labels for the given work order.
     *
     * @Route("/Manufacturing/WorkOrder/{id}/productLabel/",
     *  name="Manufacturing_Label_workOrder")
     * @Template("manufacturing/work-type/print.html.twig")
     */
    public function workOrderAction(WorkOrder $productOrder, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::WAREHOUSE]);
        $message = $error = '';

        $label = new ProductLabel($productOrder, 1);
        $options = ['action' => $this->getCurrentUri()];
        $form = $this->createForm(PrintProductLabelType::class, $label, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->printer->printLabels($label);
                $message = sprintf("Printing %s label(s)...", $label->getNumCopies());
                $this->dbm->flushAndCommit();
            } catch (PrinterException $ex) {
                $this->dbm->rollBack();
                $error = $ex->getMessage();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $url = $request->get('next');
            if ($url && (!$error)) {
                $this->logNotice($message);
                return $this->redirect($url);
            }
        }

        return [
            'form' => $form->createView(),
            'error' => $error,
            'message' => $message,
        ];
    }

    public function redirect($url, $status = 302)
    {
        $request = $this->getCurrentRequest();
        if ($request->isXmlHttpRequest()) {
            return JsonResponse::javascriptRedirect($url);
        } else {
            return parent::redirect($url, $status);
        }
    }
}
