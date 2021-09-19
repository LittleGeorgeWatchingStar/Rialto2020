<?php

namespace Rialto\Sales\Customer\Web;

use FOS\RestBundle\View\View;
use Rialto\Accounting\Debtor\Orm\DebtorPaymentStatus;
use Rialto\Database\Orm\EntityList;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\Orm\CustomerRepository;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerController extends RialtoController
{
    /**
     * @var CustomerRepository
     */
    private $repo;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->getRepository(Customer::class);
    }

    /**
     * List and search for customers.
     *
     * @Route("/sales/customer/", name="customer_list")
     * @Route("/api/v2/sales/customer/")
     * @Method("GET")
     *
     * @api for OroCRM
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::CUSTOMER_SERVICE);
        $form = $this->createForm(ListFilterType::class);
        $form->submit($request->query->all());
        $results = new EntityList($this->repo, $form->getData());

        return View::create(CustomerSummary::fromList($results))
            ->setHeader('record-count', $results->total())
            ->setTemplate("sales/customer/list.html.twig")
            ->setTemplateData([
                'form' => $form->createView(),
                'customers' => $results,
            ]);
    }

    /**
     * @Route("/sales/customer/{customer}/", name="customer_view")
     * @Method("GET")
     * @Template("sales/customer/view.html.twig")
     */
    public function viewAction(Customer $customer)
    {
        $this->denyAccessUnlessGranted(Role::CUSTOMER_SERVICE);
        return ['entity' => $customer];
    }

    /**
     * Jump to a customer given the "customer_id" query string parameter.
     *
     * @Route("/sales/select-customer/", name="customer_select")
     * @Method("GET")
     * @Template("sales/customer/select.html.twig")
     */
    public function selectAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::CUSTOMER_SERVICE);
        $session = $request->getSession();
        $id = $request->get('customer_id', $session->get('current_customer'));
        if ($id) {
            /** @var Customer|null $customer */
            $customer = $this->repo->find($id);
            if ($customer) {
                $session->set('current_customer', $id);
                return $this->redirectToCustomer($customer);
            }
            $this->logError("No such customer $id.");
        }
        return [];
    }

    /**
     * @Route("/sales/create-customer/", name="customer_create")
     * @Route("/Sales/Customer", name="Sales_Customer_create")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        $customer = Customer::createWithDefaultValues($this->dbm);
        return $this->updateCustomer($customer, $request);
    }

    /**
     * @Route("/sales/customer/{customer}/edit/", name="customer_edit")
     * @Route("/Sales/Customer/{id}", name="Sales_Customer_edit")
     */
    public function editAction(Customer $customer, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        return $this->updateCustomer($customer, $request);
    }

    private function updateCustomer(Customer $customer, Request $request)
    {
        $form = $this->createForm(CustomerType::class, $customer);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($customer);
            $this->dbm->flush();
            $this->logNotice("$customer has been updated successfully.");
            return $this->redirectToCustomer($customer);
        }

        return $this->render('sales/customer/edit.html.twig', [
            'form' => $form->createView(),
            'customer' => $customer,
        ]);
    }

    private function redirectToCustomer(Customer $customer)
    {
        return $this->redirectToRoute('customer_view', [
            'customer' => $customer->getId(),
        ]);
    }

    /**
     * @Route("/Sales/Customer/{id}/paymentStatus",
     *   name="Sales_Customer_paymentStatus")
     * @Template("sales/customer/payment-status.html.twig")
     */
    public function paymentStatusAction(Customer $customer)
    {
        $this->denyAccessUnlessGranted([Role::SALES, Role::ACCOUNTING, Role::CUSTOMER_SERVICE]);
        /** @var $service DebtorPaymentStatus */
        $service = $this->get(DebtorPaymentStatus::class);
        $status = $service->getStatus($customer);
        return [
            'service' => $service,
            'customer' => $customer,
            'paymentStatus' => $status,
        ];
    }
}
