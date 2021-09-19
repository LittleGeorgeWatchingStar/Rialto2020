<?php

namespace Rialto\Panelization\Orm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rialto\Panelization\Layout\Layout;
use Rialto\Panelization\Layout\TopToBottomLayout;
use Rialto\Panelization\Panel;
use Rialto\Purchasing\Order\PurchaseOrder;

/**
 * Database gateway for @see Panel.
 */
class PanelGateway
{
    /** @var EntityManagerInterface*/
    private $em;

    /** @var Layout  */
    private $layout;

    public function __construct(EntityManagerInterface $om)
    {
        $this->em = $om;
        $this->layout = new TopToBottomLayout();
    }

    public function findOrCreate(PurchaseOrder $order): Panel
    {
        $panel = $this->findIfExists($order);
        return $panel ?? Panel::fromPurchaseOrder($order, $this->layout);
    }

    /**
     * @return Panel|null
     */
    private function findIfExists(PurchaseOrder $order)
    {
        /** @var EntityRepository $repo */
        $repo = $this->em->getRepository(Panel::class);
        return $repo->createQueryBuilder('panel')
            ->join('panel.boards', 'board')
            ->join('board.workOrder', 'wo')
            ->andWhere('wo.purchaseOrder = :order')
            ->setParameter('order', $order)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Panel $panel)
    {
        $this->em->persist($panel);
        $this->em->flush();
    }

    /**
     * @return Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }
}
