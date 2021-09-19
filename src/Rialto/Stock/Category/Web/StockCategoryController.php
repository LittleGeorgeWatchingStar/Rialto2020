<?php

namespace Rialto\Stock\Category\Web;

use Rialto\Security\Role\Role;
use Rialto\Stock\Category\StockCategory;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for managing the categories to which a stock item can belong.
 */
class StockCategoryController extends RialtoController
{
    /**
     * @Route("/stock/category/", name="stock_category_list")
     * @Method("GET")
     * @Template("stock/category/list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $categories = $this->getRepository(StockCategory::class)
            ->findAll();
        return ['categories' => $categories];
    }

    /**
     * @Route("/Stock/StockCategory/{id}", name="Stock_StockCategory_edit")
     * @Template("stock/category/edit.html.twig")
     */
    public function editAction(StockCategory $category, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->createForm(StockCategoryType::class, $category);
        $cancelUri = $this->viewUrl($category);
        return $this->handleForm($form, $request, $cancelUri, $category);
    }

    private function viewUrl(StockCategory $category)
    {
        return $this->generateUrl('stock_category_list', [
            'id' => $category->getId(),
        ]);
    }

    /**
     * @Route("/Stock/StockCategory", name="Stock_StockCategory_create")
     * @Template("stock/category/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->createForm(CreateStockCategoryType::class);
        $cancelUri = $this->generateUrl('stock_category_list');
        return $this->handleForm($form, $request, $cancelUri);
    }

    private function handleForm(FormInterface $form, Request $request, $cancelUri, StockCategory $category = null)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $this->dbm->persist($category);
            $this->dbm->flush();
            $this->logNotice("Stock category $category saved successfully.");
            $uri = $this->viewUrl($category);
            return $this->redirect($uri);
        }

        return [
            'form' => $form->createView(),
            'cancelUri' => $cancelUri,
            'category' => $category,
        ];
    }
}
