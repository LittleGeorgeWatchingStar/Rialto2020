<?php

namespace Rialto\Stock\Move\Web;

use Rialto\Database\Orm\EntityList;
use Rialto\Stock\Move\StockMove;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class StockMoveController extends RialtoController
{
    /**
     * List stock moves.
     *
     * @Route("/stock/move/", name="stock_move_list")
     * @Template("stock/move/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $form = $this->createForm(StockMoveListFilterType::class);
        $form->submit($request->query->all());
        $repo = $this->getRepository(StockMove::class);
        $results = new EntityList($repo, $form->getData());
        if ($request->get('csv')) {
            $csv = StockMoveCsv::create($results);
            return FileResponse::fromData($csv->toString(), 'stock moves.csv', 'text/csv');
        }
        return [
            'form' => $form->createView(),
            'list' => $results,
            'stockItem' => $form->get('item')->getData(),
        ];
    }
}
