<?php

namespace Rialto\Manufacturing\Web;

use FOS\RestBundle\View\View;
use Rialto\Allocation\Requirement\RequirementTask\RequirementTaskFactory;
use Rialto\Manufacturing\ClearToBuild\ClearToBuildFactory;
use Rialto\Manufacturing\PurchaseOrder\OrderStatusIndex;
use Rialto\Manufacturing\Web\Facades\PurchaseOrderFacade;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Rialto\Manufacturing\Web\Facades\OrderStatusIndexFacade;
use Twig\Environment;
use Twig_Environment;

class DashboardController extends RialtoController
{
    /** @var PurchaseOrderRepository */
    private $poRepo;

    /** @var Environment */
    private $twig;

    /** @var RequirementTaskFactory */
    private $factory;

    /** @var ClearToBuildFactory */
    private $clearToBuild;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->poRepo = $this->dbm->getRepository(PurchaseOrder::class);

        $this->twig = $this->get(Twig_Environment::class);
        $this->factory = $this->get(RequirementTaskFactory::class);
        $this->clearToBuild = $this->get(ClearToBuildFactory::class);
    }

    /**
     * A single source for us to keep track of what's being built, what's
     * on order, etc.
     *
     * @Route("/manufacturing/dashboard/", name="manufacturing_dashboard")
     * @Method("GET")
     * @Template("manufacturing/dashboard/dashboard-index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::MANUFACTURING, Role::STOCK]);
        $session = $request->getSession();
        $rework = (bool) $request->get('rework', $session->get('dashboard_rework'));
        $session->set('dashboard_rework', $rework);
        $this->setReturnUri();

        $orders = $this->poRepo->findOpenOrdersForProduction($rework);
        $index = new OrderStatusIndex($orders);

        $encoders = [new JsonEncoder()];
        $normalizer = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizer, $encoders);
        $orderStatusIndexFacade = new OrderStatusIndexFacade($index, $this->factory, $this->clearToBuild, $this->twig);
        $json = $serializer->serialize($orderStatusIndexFacade, 'json');

        return [
            'index' => $index,
            'activeTab' => $rework ? 'rework' : 'build',
            'json'=> $json,
        ];
    }

    /**
     * @Route("/manufacturing/dashboard/{id}/facade", name="manufacture_facade", options={"expose": true})
     */
    public function facadeAction($id, Request $request)
    {
        $session = $request->getSession();
        $rework = (bool) $request->get('rework', $session->get('dashboard_rework'));
        $session->set('dashboard_rework', $rework);

        $orders = $this->poRepo->findOpenOrdersForProduction($rework);
        $index = new OrderStatusIndex($orders);
        $orders = iterator_to_array($index->getIterator())[$id];
        $facades = array_map(function (PurchaseOrder $po) {
            return new PurchaseOrderFacade($po, $this->factory, $this->clearToBuild, $this->twig);
        }, $orders);
        return View::create($facades);
    }
}
