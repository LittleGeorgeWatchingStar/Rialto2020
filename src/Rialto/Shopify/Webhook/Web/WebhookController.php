<?php

namespace Rialto\Shopify\Webhook\Web;

use JMS\Serializer\SerializerInterface as JmsSerializer;
use Rialto\Sales\Order\Import\OrderImporter;
use Rialto\Sales\Order\Orm\SalesOrderRepository;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Sales\SalesEvents;
use Rialto\Shopify\Order\Order;
use Rialto\Shopify\Storefront\Storefront;
use Rialto\Shopify\Storefront\StorefrontRepository;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles Shopify webhooks.
 *
 * @see http://docs.shopify.com/api/tutorials/using-webhooks
 */
class WebhookController extends RialtoController
{
    /** @var JmsSerializer */
    private $serializer;

    /** @var OrderImporter */
    private $importer;

    /** @var SalesOrderRepository */
    private $orderRepo;

    /** @var StorefrontRepository */
    private $storefrontRepo;

    protected function init(ContainerInterface $container)
    {
        $this->serializer = $this->get(JmsSerializer::class);
        $this->importer = $this->get(OrderImporter::class);
        $this->orderRepo = $this->getRepository(SalesOrder::class);
        $this->storefrontRepo = $this->getRepository(Storefront::class);
    }

    /**
     * @Route("/api/shopify/webhook/order/create/",
     *   name="shopify_webhook_order_create")
     * @Method("POST")
     */
    public function orderCreateAction(Request $request)
    {
        /* @var Order $shopifyOrder */
        $shopifyOrder = $this->serializer->deserialize(
            $request->getContent(),
            Order::class,
            $request->getRequestFormat());

        if ($this->orderAlreadyExists($shopifyOrder)) {
            return new Response('Already exists', Response::HTTP_OK);
        }

        $storefront = $this->getCurrentStorefront();
        $shopifyOrder->setStorefront($storefront);
        $this->dbm->beginTransaction();
        try {
            $rialtoOrder = $this->importer->createSalesOrder($shopifyOrder);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        /* See https://mantis.gumstix.com/view.php?id=4364#c3736 */
//        $this->dbm->beginTransaction();
//        try {
//            $this->notifyOfAuthorizedOrder($rialtoOrder);
//            $this->dbm->flushAndCommit();
//        } catch ( \Exception $ex ) {
//            $this->dbm->rollBack();
//            throw $ex;
//        }

        return new Response('OK', Response::HTTP_CREATED);
    }

    private function orderAlreadyExists(Order $order)
    {
        return $this->orderRepo->orderAlreadyExists($this->getCurrentUser(), $order->id);
    }

    /** @return Storefront */
    private function getCurrentStorefront()
    {
        $user = $this->getCurrentUser();
        return $this->storefrontRepo->findByUser($user);
    }

    private function notifyOfAuthorizedOrder(SalesOrder $order)
    {
        $event = new SalesOrderEvent($order);
        $this->dispatchEvent(SalesEvents::ORDER_AUTHORIZED, $event);
    }
}
