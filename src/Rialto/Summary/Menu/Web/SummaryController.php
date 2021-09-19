<?php

namespace Rialto\Summary\Menu\Web;

use Doctrine\DBAL\Connection;
use Rialto\Summary\Menu\Main;
use Rialto\Summary\Menu\Summary;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SummaryController extends RialtoController
{
    /** @var AuthorizationCheckerInterface */
    private $auth;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->auth = $this->get(AuthorizationCheckerInterface::class);
    }

    /**
     * @Route("/summary/", name="summary_index")
     * @Method("GET")
     * @Template("summary/menu/index.html.twig")
     */
    public function indexAction()
    {
        $summaries = $this->loadSummaries();

        return [
            'summaries' => $summaries,
        ];
    }

    /** @return Summary[] */
    private function loadSummaries()
    {
        /** @var $conn Connection */
        $conn = $this->get(Connection::class);

        /** @var $router RouterInterface */
        $router = $this->get(RouterInterface::class);
        return [
            new Main\AdminSummary($this->dbm, $router),
            new Main\SalesSummary($this->dbm, $router),
            new Main\DashboardSummary($this->dbm, $router),
            new Main\PurchasingAllocateSummary($this->dbm, $router),
            new Main\PurchasingSendSummary($conn, $router),
            new Main\NeedDatesSummary($conn, $router),
            new Main\OrdersToKitSummary($this->dbm, $router),
            new Main\PleaseAssembleSummary($this->dbm, $router),
            new Main\SalesReturnSummary($this->dbm, $router),
            new Main\ReceivePurchaseOrderSummary($router),
        ];
    }
}
