<?php

namespace Rialto\Sales\Customer\Web;

use FOS\RestBundle\View\View;
use Rialto\Database\Orm\EntityList;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Customer\Orm\CustomerBranchRepository;
use Rialto\Sales\Salesman\Salesman;
use Rialto\Security\Role\Role;
use Rialto\Web\Form\JsEntityType;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomerBranchController extends RialtoController
{
    /** @var CustomerBranchRepository */
    private $repo;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->dbm->getRepository(CustomerBranch::class);
    }

    /**
     * For selecting a branch using the @see JsEntityType input.
     *
     * @Route("/sales/customer-branch/")
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        $filters = $request->query->all();
        $branches = new EntityList($this->repo, $filters);
        return View::create(CustomerBranchSummary::fromList($branches))
            ->setFormat('json');
    }

    /**
     * @Route("/Sales/Customer/{id}/branch", name="Sales_CustomerBranch_create")
     * @Template("sales/branch/branch-edit.html.twig")
     */
    public function createAction(Customer $customer, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        $branch = CustomerBranch::createWithDefaultValues($customer, $this->dbm);
        return $this->processForm($branch, $request, 'created');
    }

    /**
     * @Route("/Sales/CustomerBranch/{id}/",
     *   name="Sales_CustomerBranch_edit")
     * @Template("sales/branch/branch-edit.html.twig")
     */
    public function editAction(CustomerBranch $branch, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        return $this->processForm($branch, $request);
    }

    private function processForm(CustomerBranch $branch, Request $request, $updated = 'updated')
    {
        $form = $this->createForm(CustomerBranchType::class, $branch);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($branch);
            $this->dbm->flush();
            $this->logNotice("Branch $branch has been $updated successfully.");
            $customer = $branch->getCustomer();
            return $this->redirectToRoute('customer_view', [
                'customer' => $customer->getId(),
            ]);
        }

        return [
            'form' => $form->createView(),
            'custBranch' => $branch,
        ];
    }

    /**
     * @Route("/sales/batch-update/branch/",
     *   name="sales_batch_update_branch")
     * @Template("sales/branch/batch-update.html.twig")
     */
    public function batchUpdateAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        $defaults = [
            '_limit' => 100,
            '_start' => 0,
            'lastOrderSince' => date('Y-m-d', strtotime('first day of January last year')),
            'country' => 'US',
        ];
        $options = ['csrf_protection' => false, 'method' => 'get'];
        $filterForm = $this->createForm(BatchUpdateFilterType::class, null, $options);

        $filterForm->submit(array_merge($defaults, $request->query->all()));
        $filters = $filterForm->getData();
        $branches = new EntityList($this->repo, $filters);

        /** @var $updateForm FormInterface */
        $updateForm = $this->createFormBuilder()
            ->add('salesman', EntityType::class, [
                'class' => Salesman::class,
                'required' => false,
            ])
            ->add('update', SubmitType::class)
            ->getForm();

        $updateForm->handleRequest($request);
        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $data = array_filter($updateForm->getData());
            if (count($data) == 0) {
                $this->logWarning("No updates selected.");
            } else {
                $numUpdated = 0;
                foreach ($branches as $branch) {
                    /** @var $branch CustomerBranch */
                    if (isset($data['salesman'])) {
                        $branch->setSalesman($data['salesman']);
                    }
                    $numUpdated++;
                }
                $this->dbm->flush();
                $this->logNotice("Updated $numUpdated branches.");
            }
            return $this->redirect($this->getCurrentUri());
        }

        return [
            'filterForm' => $filterForm->createView(),
            'updateForm' => $updateForm->createView(),
            'branches' => $branches,
        ];
    }
}
