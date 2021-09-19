<?php

namespace Rialto\Purchasing\Supplier\Web;

use FOS\RestBundle\View\View;
use Rialto\Database\Orm\EntityList;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Purchasing\Supplier\SupplierPaymentStatus;
use Rialto\Purchasing\Web\PurchasingRouter;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for maintaining Supplier records.
 */
class SupplierController extends RialtoController
{
    /**
     * @var PurchasingRouter
     */
    private $router;

    protected function init(ContainerInterface $container)
    {
        $this->router = $container->get(PurchasingRouter::class);
    }

    /**
     * @Route("/api/v2/purchasing/supplier/")
     * @Route("/purchasing/supplier/", name="supplier_list")
     * @Method("GET")
     *
     * @api for Geppetto Client
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $repo = $this->getRepository(Supplier::class);
        $list = new EntityList($repo, $request->query->all());
        return View::create(SupplierSummary::fromList($list))
            ->setTemplate("purchasing/supplier/list.html.twig")
            ->setTemplateData([
                'suppliers'=> $list,
            ]);
    }

    /**
     * Jump to a supplier given the "id" query string parameter.
     *
     * @Route("/purchasing/select-supplier/", name="supplier_select")
     * @Method("GET")
     * @Template("purchasing/supplier/select.html.twig")
     */
    public function selectAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $session = $request->getSession();
        $id = $request->get('id', $session->get('current_supplier'));
        if ($id) {
            $repo = $this->getRepository(Supplier::class);
            $supplier = $repo->find($id);
            if ($supplier) {
                $session->set('current_supplier', $id);
                return $this->redirectToSupplier($supplier);
            }
            $this->logError("No such supplier $id.");
        }
        return [];
    }

    private function redirectToSupplier(Supplier $supplier)
    {
        $url = $this->supplierUrl($supplier);
        return $this->redirect($url);
    }

    private function supplierUrl(Supplier $supplier)
    {
        return $this->router->supplierView($supplier);
    }

    /**
     * @Route("/purchasing/supplier/{supplier}/", name="supplier_view", options={"expose"=true})
     * @Method("GET")
     * @Template("purchasing/supplier/view.html.twig")
     */
    public function viewAction(Supplier $supplier)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $actionsCtrl = ActionsController::class;
        return [
            'entity' => $supplier,
            'links' => "$actionsCtrl::linksAction",
        ];
    }

    /**
     * @Route("/purchasing/create-supplier", name="supplier_create")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $supplier = new Supplier();
        $cancelUri = $this->generateUrl('index');
        return $this->renderForm($supplier, 'Create a new supplier', $cancelUri, $request);
    }

    /**
     * @Route("/purchasing/supplier/{id}/edit/", name="supplier_edit")
     */
    public function editAction(Supplier $supplier, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $heading = 'Edit ' . $supplier->getName();
        $cancelUri = $this->supplierUrl($supplier);
        return $this->renderForm($supplier, $heading, $cancelUri, $request);
    }

    private function renderForm(Supplier $supplier, $heading, $cancelUri, Request $request)
    {
        $form = $this->createForm(SupplierType::class, $supplier);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($supplier);
            $this->dbm->flush();
            $this->logNotice(sprintf('%s updated successfully.',
                $supplier->getName()
            ));
            return $this->redirectToSupplier($supplier);
        }

        return $this->render("purchasing/supplier/edit.html.twig", [
            'form' => $form->createView(),
            'heading' => $heading,
            'cancelUri' => $cancelUri,
        ]);
    }

    /**
     * @Route("/Purchasing/Supplier/{id}/paymentStatus",
     *   name="Purchasing_Supplier_paymentStatus")
     * @Template("purchasing/supplier/payment-status.html.twig")
     */
    public function paymentStatus(Supplier $supplier)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $service = $this->get(SupplierPaymentStatus::class);
        $status = $service->getStatus($supplier);
        return [
            'service' => $service,
            'status' => $status,
        ];
    }

}
