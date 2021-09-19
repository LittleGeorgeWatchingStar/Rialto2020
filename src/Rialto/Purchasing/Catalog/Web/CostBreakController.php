<?php

namespace Rialto\Purchasing\Catalog\Web;

use FOS\RestBundle\View\View;
use Rialto\Purchasing\Catalog\CostBreak;
use Rialto\Purchasing\Catalog\Orm\CostBreakRepository;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class CostBreakController extends RialtoController
{
    /**
     * @var CostBreakRepository
     */
    private $repo;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->getRepository(CostBreak::class);
    }

    /**
     * @Route("/purchasing/cost-breaks/", name="cost_break_list", options={"expose": true})
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING_DATA);
        $filters = $request->query->all();
        $breaks = $this->repo->queryByFilters($filters)->getResult();
        return View::create(CostBreakSummary::fromList($breaks))
            ->setFormat('json');
    }
}
