<?php

namespace Rialto\Stock\Level\Web;

use Doctrine\ORM\EntityRepository;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Web\OrderQuantityType;
use Rialto\Stock\Level\Orm\StockLevelStatusRepository;
use Rialto\Stock\Level\StockLevelStatus;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for managing stock order points.
 */
class OrderPointController extends RialtoController
{
    /**
     * Edit the order points for the given item.
     *
     * @Route("/Stock/OrderPoint/",
     *   name="Stock_OrderPoint_edit")
     * @Method("GET")
     * @Template("stock/order-point/edit.html.twig")
     */
    public function editAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::PURCHASING]);
        $options = ['method' => 'get', 'csrf_protection' => false];
        $filterForm = $this->createNamedBuilder(null, null, $options)
            ->add('location', EntityType::class, [
                'class' => Facility::class,
                'query_builder' => function (FacilityRepository $repo) {
                    return $repo->queryValidDestinations();
                },
                'required' => false,
                'placeholder' => '-- all --',
            ])
            ->add('sku', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'matching...'],
            ])
            ->getForm();

        $filterForm->submit($request->query->all());

        $filters = array_filter($filterForm->getData());
        $data = [];
        if (count($filters) > 0) {
            $repo = $this->getStatusRepo();
            $data = $repo->getOrderPointSummary($filters);
        }

        $ctrl = self::class;
        return [
            'form' => $filterForm->createView(),
            'data' => $data,
            'updateOrderPoint' => "$ctrl::putOrderPointAction",
            'updateEoq' => "$ctrl::putEoqAction",
        ];
    }

    /** @return StockLevelStatusRepository|EntityRepository */
    private function getStatusRepo()
    {
        return $this->getRepository(StockLevelStatus::class);
    }

    /**
     * @Route("/Stock/OrderPoint/{location},{item}/",
     *   name="Stock_OrderPoint_put")
     * @Method("POST")
     * @Template("form/minimal.html.twig")
     *
     * @param PhysicalStockItem $item
     */
    public function putOrderPointAction(Facility $location, StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::PURCHASING]);
        assertion($item instanceof PhysicalStockItem);
        $repo = $this->getStatusRepo();
        $level = $repo->findOrCreate($item, $location);

        $options = [
            'action' => $this->generateUrl('Stock_OrderPoint_put', [
                'location' => $location->getId(),
                'item' => $item->getSku(),
            ]),
        ];
        $form = $this->createFormBuilder($level, $options)
            ->add('orderPoint', NumberType::class, [
                'label' => false,
                'attr' => ['title' => 'Press Enter to save'],
            ])
            ->getForm();

        $message = '';
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            $message = 'OK';
        }

        return [
            'form' => $form->createView(),
            'message' => $message,
        ];
    }

    /**
     * @Route("/Stock/Eoq/{location},{item}/",
     *   name="Stock_Eoq_put")
     * @Method("POST")
     * @Template("form/minimal.html.twig")
     *
     * @param PhysicalStockItem $item
     */
    public function putEoqAction(Facility $location, StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::PURCHASING]);
        assertion($item instanceof PhysicalStockItem);

        $options = [
            'action' => $this->generateUrl('Stock_Eoq_put', [
                'location' => $location->getId(),
                'item' => $item->getSku(),
            ]),
        ];
        $form = $this->createFormBuilder($item, $options)
            ->add('orderQuantity', NumberType::class, [
                'label' => false,
                'attr' => ['title' => 'Press Enter to save'],
            ])
            ->getForm();

        $message = '';
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            $message = 'OK';
        }

        return [
            'form' => $form->createView(),
            'message' => $message,
        ];
    }


    /**
     * Update the EOQ and order point (OP) at each location for the given item.
     *
     * @Route("/Stock/StockItem/{stockCode}/orderPoint/",
     *   name="Stock_OrderPoint_byItem")
     */
    public function byItemAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::PURCHASING]);
        $repo = $this->getStatusRepo();
        $levels = $repo->findByItem($item);
        $container = (object) [
            'item' => $item,
            'levels' => $levels,
        ];
        $options = [
            'validation_groups' => ['purchasing']
        ];
        /* @var FormInterface $form */
        $form = $this->createFormBuilder($container, $options)
            ->setAction($this->getCurrentUri())
            ->setMethod("POST")
            ->add('item', OrderQuantityType::class)
            ->add('levels', CollectionType::class, [
                'entry_type' => OrderPointType::class,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            /* Return either the new EOQ or OP, as requested by the client. */
            $data = null;
            switch ($request->get('return')) {
                case 'eoq':
                    $data = $item->getEconomicOrderQty();
                    break;
                default:
                    $data = array_sum(array_map(function (StockLevelStatus $level) {
                        return $level->getOrderPoint();
                    }, $levels));
                    break;
            }
            return new Response(number_format($data));
        }

        return $this->createFormResponse($form,
            'stock/order-point/by-item.html.twig',
            [
                'item' => $item,
            ]);
    }

    private function createFormResponse(
        FormInterface $form,
        $template,
        array $context = [],
        $formAttr = 'form')
    {
        $context[$formAttr] = $form->createView();
        $response = $this->render($template, $context);
        if ($form->isSubmitted() && (!$form->isValid())) {
            $response->setStatusCode(400);
        }
        return $response;
    }
}
