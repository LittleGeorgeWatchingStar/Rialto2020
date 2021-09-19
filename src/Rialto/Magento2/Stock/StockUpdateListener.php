<?php

namespace Rialto\Magento2\Stock;

use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\Exception\RequestException;
use Rialto\Magento2\Api\Rest\RestApiFactory;
use Rialto\Magento2\Storefront\Storefront;
use Rialto\Magento2\Storefront\StorefrontRepository;
use Rialto\Stock\Level\StockLevelEvent;
use Rialto\Stock\StockEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Responds to stock level changes in Rialto by updating the stock levels
 * in Magento.
 */
class StockUpdateListener implements EventSubscriberInterface
{
    /** @var StorefrontRepository */
    private $repo;

    /**
     * @var RestApiFactory
     */
    private $apiFactory;

    public function __construct(ObjectManager $om, RestApiFactory $apiFactory)
    {
        $this->repo = $om->getRepository(Storefront::class);
        $this->apiFactory = $apiFactory;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     */
    public static function getSubscribedEvents()
    {
        return [
            StockEvents::STOCK_LEVEL_UPDATE => 'updateStockLevel',
        ];
    }

    public function updateStockLevel(StockLevelEvent $event)
    {
        $status = $event->getLevel();
        $location = $status->getLocation();
        $stores = $this->repo->findByStockLocation($location);
        foreach ($stores as $store) {
            $api = $this->apiFactory->createInventoryApi($store);
            try {
                $api->updateStockLevel($status);
            } catch (RequestException $ex) {
                $this->handleError($ex, $store, $event);
            }
        }
    }

    private function handleError(RequestException $ex,
                                 Storefront $store,
                                 StockLevelEvent $event)
    {

        if ($ex->getCode() != Response::HTTP_NOT_FOUND) {
            $event->addWarning($this->formatError($store, $ex));
        }
    }

    /**
     * Because this is how Magento 2 structures error responses.
     */
    private function formatError(Storefront $store, RequestException $ex): string
    {
        $body = $ex->getResponse()->getBody()->getContents();
        try {
            $data = \GuzzleHttp\json_decode($body, $asArray = true);
        } catch (\InvalidArgumentException $ex) {
            $data = [];
        }
        $message = $data['message'] ?? $ex->getMessage();
        // Convert Magento's param placeholders ("%1") to sprintf format ("%1$s")
        $message = preg_replace('/(\%\d)/', '$1\$s', $message);
        $message = isset($data['parameters'])
            ? sprintf($message, ...$data['parameters'])
            : $message;
        return sprintf('%s: %s', $store->getStoreUrl(), $message);
    }
}
