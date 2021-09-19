<?php

namespace Rialto\Summary\Dashboard\Admin;

use Rialto\Security\Role\Role;
use Rialto\Summary\Menu\Main\SalesSummary;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

class AdminDashboardController extends RialtoController
{
    /** @var  RouterInterface */
    private $router;

    protected function init(ContainerInterface $container)
    {
        $this->router = $container->get(RouterInterface::class);
    }

    /**
     * @Route("/summary/admin-dashboard/", name="admin_dashboard")
     * @Method("GET")
     * @Template("summary/dashboard/admin.html.twig")
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $salesSummary = new SalesSummary($this->dbm, $this->router);

        return [
            'salesSummary' => $salesSummary->getChildren(),
        ];
    }

}
