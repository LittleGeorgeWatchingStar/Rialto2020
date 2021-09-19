<?php

namespace Rialto\Purchasing\Parts\Web;

use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\RialtoController;

class PartsController extends RialtoController
{
    /**
     * List engineering data about all active parts in the system.
     *
     * @Route("/api/v2/purchasing/parts/")
     *
     * @api for Geppetto Engineer
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::ENGINEER]);
        /** @var $repo StockItemRepository */
        $repo = $this->getRepository(StockItem::class);
        $data = $repo->findPartsData();
        return View::create($data);
    }

    /**
     * Engineering data about an active part in the system.
     *
     * @Route("/api/v2/purchasing/parts/{sku}/")
     *
     * @api for Geppetto Engineer
     */
    public function viewAction(string $sku)
    {
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::ENGINEER]);
        /** @var $repo StockItemRepository */
        $repo = $this->getRepository(StockItem::class);
        $data = $repo->findPartData($sku);
        if (!$data) {
            throw $this->notFound();
        }
        return View::create($data);
    }
}
