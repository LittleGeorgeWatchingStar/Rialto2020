<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Entity\Web\AttributeBag;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\Orm\StockItemAttributeRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\StockItemAttribute;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for viewing and manipulating stock item attributes.
 */
class StockItemAttributeController extends RialtoController
{
    /**
     * @var StockItemAttributeRepository
     */
    private $repo;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->getRepository(StockItemAttribute::class);
    }

    /**
     * User interface for batch-editing all of the attributes of an entity.
     *
     * @Route("/attribute/stock/item/{id}/", name="stock_item_attribute_edit")
     */
    public function editAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $returnUri = $this->generateUrl('stock_item_view', [
            'item' => $item->getSku(),
        ]);
        $attributes = new AttributeBag($item, $this->repo->findByEntity($item));

        $form = $this->createFormBuilder($attributes)
            ->add('attributes', CollectionType::class, [
                'entry_type' => StockItemAttributeType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'label' => false,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $attributes->persist($this->dbm);
            $this->dbm->flush();
            $this->logNotice("Attributes updated successfully.");
            return $this->redirect($returnUri);
        }

        return $this->render('core/entity-attribute/edit.html.twig', [
            'form' => $form->createView(),
            'heading' => "Edit attributes for $item",
            'cancelUri' => $returnUri,
        ]);
    }
}
