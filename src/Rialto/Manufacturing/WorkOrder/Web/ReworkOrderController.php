<?php

namespace Rialto\Manufacturing\WorkOrder\Web;

use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Manufacturing\WorkOrder\WorkOrderFactory;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * For creating rework orders.
 */
class ReworkOrderController extends RialtoController
{
    /**
     * @var WorkOrderFactory
     */
    private $woFactory;

    /**
     * @var AllocationFactory
     */
    private $allocator;

    protected function init(ContainerInterface $container)
    {
        $this->woFactory = $this->get(WorkOrderFactory::class);
        $this->allocator = $this->get(AllocationFactory::class);
    }

    /**
     * Create a rework order by choosing bins to rework.
     *
     * @Route("/manufacturing/rework/{stockCode}", name="rework_from_bins")
     * @Template("manufacturing/reworkOrder/fromBins.html.twig")
     */
    public function fromBinsAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING, Role::ENGINEER);
        if (! $item instanceof ManufacturedStockItem) {
            throw $this->badRequest("$item is not manufactured");
        }

        /** @var $binRepo StockBinRepository */
        $binRepo = $this->getRepository(StockBin::class);
        $bins = $binRepo->createBuilder()
            ->available()
            ->byItem($item)
            ->getResult();

        $template = new ReworkFromBins();
        /** @var FormInterface $form */
        $form = $this->createFormBuilder($template)
            ->add('purchData', EntityType::class, [
                'class' => PurchasingData::class,
                'label' => 'Do rework at',
                'query_builder' => function (PurchasingDataRepository $repo) use ($item) {
                    return $repo->queryActive($item);
                },
                'choice_label' => 'supplierSummary',
                'placeholder' => '-- choose --',
            ])
            ->add('zeroCost', CheckboxType::class, [
                'required' => false,
            ])
            ->add('bins', EntityType::class, [
                'class' => StockBin::class,
                'expanded' => true,
                'multiple' => true,
                'choices' => $bins,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $workOrder = $template->createOrder($this->woFactory);
                $this->dbm->persist($workOrder);
                $this->dbm->flush();

                $template->allocateToOrder($workOrder, $this->allocator);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $po = $workOrder->getPurchaseOrder();
            $this->logNotice("Created rework $po.");
            return $this->redirectToRoute('purchase_order_view', [
                'order' => $po->getId(),
            ]);
        }

        return [
            'item' => $item,
            'bins' => $bins,
            'form' => $form->createView(),
        ];
    }
}
