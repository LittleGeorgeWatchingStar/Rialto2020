<?php

namespace Rialto\Supplier\Web;

use Rialto\Purchasing\Supplier\Orm\SupplierRepository;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;


/**
 * Controller for sub-requests common to all dashboard views.
 */
class DashboardController extends SupplierController
{
    /**
     * Privileged users can select which supplier's dashboard they want to
     * look at.
     *
     * @Route("/{id}/select/", name="supplier_dashboard_select")
     * @Method("GET")
     * @Template("supplier/dashboard/selectSupplier.html.twig")
     */
    public function selectSupplier(Supplier $supplier, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $stack = $this->get(RequestStack::class); /* @var $stack RequestStack */
        $router = $this->get(RouterInterface::class); /* @var $router RouterInterface */
        $master = $stack->getMasterRequest();
        $routeInfo = $router->match($master->getPathInfo());
        $action = $this->generateUrl('supplier_dashboard_select', [
            'id' => $supplier->getId(),
        ]);
        $form = $this->createNamedBuilder('select_supplier')
            ->setAction($action)
            ->setMethod('GET')
            ->setAttribute('class', 'standard inline')
            ->add('supplier', EntityType::class, [
                'class' => Supplier::class,
                'query_builder' => function(SupplierRepository $repo) {
                    return $repo->queryActiveManufacturers();
                },
                'label' => false,
                'data' => $supplier,
            ])
            ->add('routeName', HiddenType::class, [
                'data' => $routeInfo["_route"],
            ])
            ->getForm(); /* @var $form FormInterface */
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newSupplier = $form->get('supplier')->getData();
            $route = $form->get('routeName')->getData();
            $url = $this->generateUrl($route, ['id' => $newSupplier->getId()]);
            return $this->redirect($url);
        }
        return [
            'form' => $form->createView(),
        ];
    }

}
