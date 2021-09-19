<?php

namespace Rialto\Sales\Price\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Sales\Price\Orm\ProductPriceRepository;
use Rialto\Sales\Price\ProductPrice;
use Rialto\Sales\Type\SalesType;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for setting product prices.
 */
class ProductPriceController extends RialtoController
{
    /**
     * @Route("/Sales/Product/{stockCode}/prices", name="Sales_ProductPrice_list")
     * @Method("GET")
     * @Template("sales/product-price/list.html.twig")
     */
    public function listAction(StockItem $item)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        /** @var ProductPriceRepository $repo */
        $repo = $this->getRepository(ProductPrice::class);
        $prices = $repo->findByStockItem($item);

        /* The form for adding a new price is on the same page as the list. */
        $price = $this->createForItem($item);
        $createForm = $this->createForm(ProductPriceType::class, $price);

        return [
            'item' => $item,
            'prices' => $prices,
            'createForm' => $createForm->createView(),
        ];
    }

    /** @return ProductPrice */
    private function createForItem(StockItem $item)
    {
        /** @var Currency $currency */
        $currency = $this->dbm->need(Currency::class, Currency::USD);
        /** @var SalesType $salesType */
        $salesType = $this->dbm->need(SalesType::class, SalesType::ONLINE);
        return new ProductPrice($item, $currency, $salesType);
    }

    /**
     * @Route("/Sales/Product/{stockCode}/prices", name="Sales_ProductPrice_create")
     * @Method("POST")
     */
    public function createAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        $price = $this->createForItem($item);
        $form = $this->createForm(ProductPriceType::class, $price);

        $form->handleRequest($request);
        /** @var ProductPriceRepository $repo */
        $repo = $this->getRepository(ProductPrice::class);
        if ($repo->findExistingPrice($price)) {
            $this->logError("That price already exists.");
        } elseif ($form->isValid()) {
            $this->dbm->persist($price);
            $this->dbm->flush();
            $this->logNotice("Price added successfully");
        } else {
            $this->logErrors($form->getErrors(true, true));
        }

        return $this->redirectToRoute('Sales_ProductPrice_list', [
            'stockCode' => $item->getSku(),
        ]);
    }

    /**
     * @Route("/Sales/Product/{item}/prices/{price}", name="Sales_ProductPrice_edit")
     * @Template("sales/product-price/edit.html.twig")
     */
    public function editAction(StockItem $item, ProductPrice $price, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        $form = $this->createFormBuilder($price)
            ->add('price', MoneyType::class, [
                'currency' => Currency::USD,
            ])
            ->getForm();
        $returnUri = $this->generateUrl('Sales_ProductPrice_list', [
            'stockCode' => $item->getSku(),
        ]);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->dbm->flush();
                $this->logNotice("Price updated successfully");
                return JsonResponse::javascriptRedirect($returnUri);
            } else {
                return JsonResponse::fromInvalidForm($form);
            }
        }
        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
        ];
    }


}
