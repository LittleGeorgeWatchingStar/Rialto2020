<?php

namespace Rialto\Manufacturing\Bom\Bag;

use Rialto\Manufacturing\Bom\BomEvent;
use Rialto\Manufacturing\ManufacturingEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * When a new BOM is created, this listener will add a plastic bag to the
 * BOM if needed.
 *
 * This is necessary because engineering tools create BOMs that only contain
 * the electrical components of the board, without regard for logistical
 * components like bags.
 */
class AddBagToBomListener implements EventSubscriberInterface
{
    /** @var BagFinder */
    private $adder;

    public function __construct(BagAdder $adder)
    {
        $this->adder = $adder;
    }

    /**
     * For injecting a test double.
     */
    public function setAdder(BagAdder $adder)
    {
        $this->adder = $adder;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     */
    public static function getSubscribedEvents()
    {
        return [
            ManufacturingEvents::NEW_BOM => 'addBagIfNeeded',
        ];
    }

    public function addBagIfNeeded(BomEvent $event)
    {
        $parent = $event->getItemVersion();
        $this->adder->addBagIfNeeded($parent);
    }
}

