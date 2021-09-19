<?php

namespace Rialto\Supplier\Stock\Web;


use Rialto\Database\Orm\EntityList;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Move\Orm\StockMoveRepository;
use Rialto\Stock\Move\StockMove;
use Rialto\Supplier\Web\SupplierController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows the CM to view recent stock moves at their facility.
 *
 * @Route("/supplier")
 */
class StockMoveController extends SupplierController
{
    /** @var StockMoveRepository */
    private $repo;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->getRepository(StockMove::class);
    }

    /**
     * @Route("/{id}/stock-move/", name="supplier_stock_move")
     * @Template("supplier/stock/moves.html.twig")
     */
    public function listAction(Supplier $supplier, Request $request)
    {
        $this->checkDashboardAccess($supplier);

        $form = $this->createForm(StockMoveFilterType::class);
        $form->submit($request->query->all());
        $filters = $form->getData();
        $filters['location'] = $supplier->getFacility();

        $results = new EntityList($this->repo, $filters);

        $backUrl = $request->get(
            'back',
            $this->generateUrl('supplier_stock_level', [
                'id' => $supplier->getId(),
            ]));

        return [
            'form' => $form->createView(),
            'moves' => $results,
            'supplier' => $supplier,
            'facility' => $supplier->getFacility(),
            'backUrl' => $backUrl,
        ];
    }
}
