<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Security\Role\Role;
use Rialto\Stock\Item\StockFlags;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\StockItemConnectionsService;
use Rialto\Web\Form\JsEntityType;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;

class StockItemConnectionsController extends RialtoController
{
    /**
     * @Route("/stock/item/{stockCode}/connections/",
     *     name="stock_item_connections")
     * @Template("stock/item-connections/edit.html.twig")
     */
    public function editAction(StockItem $stockItem, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);

        $connectionsService = new StockItemConnectionsService($stockItem);
        $connectionForm = $this->createConnectionsForm($connectionsService);

        $flags = $stockItem->getFlags();
        $flagsForm = $this->createFlagsForm($flags);

        if ($request->isMethod('post')) {
            if ($request->get("addConnection")) {
                $connectionForm->handleRequest($request);
                if ($connectionsService->getConnector() != null) {
                    $connectionsService->addConnector();
                    $this->dbm->flush();
                    $this->logNotice("The connection has been added successfully.");
                    return $this->redirect($this->getCurrentUri());
                }
            }
            elseif ($request->get("deleteConnection")) {
                $connectsToItemId = $request->get("deleteConnection");
                $connectsToItem = $this->dbm->need(StockItem::class, $connectsToItemId);
                $connectionsService->deleteConnector($connectsToItem);
                $this->dbm->flush();
                $this->logNotice("The connection has been deleted successfully.");
                return $this->redirect($this->getCurrentUri());
            }
            elseif ($request->get("updateFlags")) {
                $flagsForm->handleRequest($request);
                $this->dbm->flush();
                $this->logNotice("The flags have been updated successfully.");
                return $this->redirect($this->getCurrentUri());
            }
        }
        return [
            'stockItem' => $stockItem,
            'flagsForm' => $flagsForm->createView(),
            'connectionService'=> $connectionsService,
            'connectionForm' => $connectionForm->createView(),
        ];
    }

    private function createFlagsForm(StockFlags $flags)
    {
        $builder = $this->createFormBuilder($flags);
        $builder->add('componentOfInterest', CheckboxType::class, ['required' => false])
                ->add('matingConnector', CheckboxType::class, ['required' => false])
                ->add('standardConnector', CheckboxType::class, ['required' => false]);
        return $builder->getForm();
    }

    private function createConnectionsForm(StockItemConnectionsService $service)
    {
        $builder = $this->createFormBuilder($service);
        $builder->add('connector', JsEntityType::class, [
            'class' => StockItem::class,
            'required' => false,
        ]);
        return $builder->getForm();
    }
}
