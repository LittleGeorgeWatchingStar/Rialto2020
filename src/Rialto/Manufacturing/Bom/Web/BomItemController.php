<?php

namespace Rialto\Manufacturing\Bom\Web;

use Rialto\Madison\Feature\FeatureInjector;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller for creating and managing BomItem records.
 *
 * @see BomItem
 */
class BomItemController extends RialtoController
{
    /** @var ValidatorInterface */
    private $validator;

    protected function init(ContainerInterface $container)
    {
        $this->validator = $container->get(ValidatorInterface::class);
    }


    /**
     * @Route("/Manufacturing/BomItem", name="Manufacturing_BomItem_create")
     * @Template("manufacturing/bomItem/bomItem-create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::STOCK]);
        $parentItem = $this->needEntityFromRequest(StockItem::class, 'item');
        /* @var $parentItem StockItem */
        $parent = $parentItem->getVersion($request->get('version'));

        $errors = [];
        $form = $this->createForm(CreateBomItemType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $bomItem = $form->getData();
            $parent->addBomItem($bomItem);
            $parent->resetWeightFromBom();

            /* It's not enough to validate just the item -- we also have to
             * validate the parent */
            $groups = ["bom"];
            $errors = $this->validator->validate($parent, null, $groups);
            if (count($errors) == 0) {
                $this->dbm->persist($bomItem);
                $this->dbm->flush();
                $uri = $this->versionUrl($parent);
                return JsonResponse::javascriptRedirect($uri);
            }
        }

        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
            'parent' => $parent,
            'errors' => $errors,
        ];
    }

    private function versionUrl(ItemVersion $parent)
    {
        return $this->generateUrl('item_version_edit', [
            'item' => $parent->getSku(),
            'version' => $parent->getVersionCode(),
        ]);
    }

    /**
     * @Route("/Manufacturing/BomItem/{id}", name="Manufacturing_BomItem_edit")
     * @Template("manufacturing/bomItem/bomItem-edit.html.twig")
     */
    public function editAction(BomItem $bomItem, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::STOCK]);
        $parent = $bomItem->getParent();
        $form = $this->createForm(BomItemType::class, $bomItem);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $parent->resetWeightFromBom();
                $this->dbm->flush();
                $uri = $this->versionUrl($parent);
                return JsonResponse::javascriptRedirect($uri);
            } else {
                return JsonResponse::fromInvalidForm($form);
            }
        }

        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
            'bomItem' => $bomItem,
        ];
    }

    /**
     * @Route("/record/Manufacturing/BomItem/{id}", name="Manufacturing_BomItem_delete")
     * @Method({"DELETE"})
     */
    public function deleteAction(BomItem $bomItem)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::STOCK]);
        $parent = $bomItem->getParent();
        $parent->removeBomItem($bomItem);
        $parent->resetWeightFromBom();
        $this->dbm->remove($bomItem);
        $this->dbm->flush();
        $this->logNotice("{$bomItem->getSku()} has been removed from the BOM.");
        $uri = $this->versionUrl($parent);
        return $this->redirect($uri);
    }

    /**
     * @Route("/record/Manufacturing/BomItem/{id}", name="Manufacturing_BomItem_setPrimary")
     * @Method({"POST"})
     */
    public function setPrimaryAction(BomItem $bomItem)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::STOCK]);
        $bomItem->setPrimary(true);
        $this->setComponentParentManufacturer($bomItem);
        $this->dbm->flush();
        return $this->redirectToRoute('stock_item_edit_features', [
            'stockCode' => $bomItem->getParent()->getSku(),
        ]);
    }

    private function setComponentParentManufacturer(BomItem $bomItem)
    {
        $injector = $this->get(FeatureInjector::class);
        $parent = $bomItem->getParent()->getStockItem();

        $manufacturer = $bomItem->getStockItem()
            ->getPreferredPurchasingData()
            ->getManufacturer();

        $injector->setManufacturer($parent, $manufacturer);
    }

}
