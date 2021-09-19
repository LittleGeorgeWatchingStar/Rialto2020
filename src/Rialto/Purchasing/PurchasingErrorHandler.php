<?php

namespace Rialto\Purchasing;

use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Web\Form\FormErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Provides supplementary information for purchasing form errors.
 */
class PurchasingErrorHandler implements EventSubscriberInterface
{
    /** @var RouterInterface  */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return [
            FormErrorEvent::NAME => 'onFormError',
        ];
    }

    public function onFormError(FormErrorEvent $event)
    {
        switch ($event->getMessageTemplate()) {
            case 'purchasing.purch_data.unique':
                $this->uniquePurchasingData($event);
                break;
        }
    }

    private function uniquePurchasingData(FormErrorEvent $event)
    {
        /** @var $purchData PurchasingData */
        $purchData = $event->getFormData();
        if (! $purchData instanceof PurchasingData) {
            return;
        }
        $catalogNo = $purchData->getCatalogNumber();
        $msg = sprintf(
            ' This is usually caused by duplicate stock items.' .
            ' <a href="%s">Click here</a> to see possible duplicates.',
            $this->router->generate('purchasing_data_list', [
                'matching' => urlencode($catalogNo),
            ])
        );
        $event->setHtml($msg);
    }
}
