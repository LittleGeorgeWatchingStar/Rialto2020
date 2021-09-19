<?php

namespace Proxies\__CG__\Rialto\Purchasing\Order;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class PurchaseOrder extends \Rialto\Purchasing\Order\PurchaseOrder implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = [];



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return ['__isInitialized__', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'id', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'editNo', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'comments', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'productionNotes', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'orderDate', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'dateUpdated', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'datePrinted', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'initiator', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'exchangeRate', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'shippingMethod', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'approvalStatus', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'approvalReason', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'supplier', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'buildLocation', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'owner', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'shipper', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'deliveryLocation', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'deliveryAddress', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'items', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'newItem', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'receipts', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'supplierReference', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'priority', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'tasks', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'sendHistory', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'autoAddItems', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'autoAllocateTo', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'events'];
        }

        return ['__isInitialized__', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'id', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'editNo', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'comments', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'productionNotes', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'orderDate', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'dateUpdated', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'datePrinted', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'initiator', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'exchangeRate', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'shippingMethod', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'approvalStatus', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'approvalReason', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'supplier', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'buildLocation', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'owner', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'shipper', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'deliveryLocation', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'deliveryAddress', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'items', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'newItem', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'receipts', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'supplierReference', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'priority', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'tasks', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'sendHistory', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'autoAddItems', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'autoAllocateTo', '' . "\0" . 'Rialto\\Purchasing\\Order\\PurchaseOrder' . "\0" . 'events'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (PurchaseOrder $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', []);
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getEditNo()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEditNo', []);

        return parent::getEditNo();
    }

    /**
     * {@inheritDoc}
     */
    public function getItems()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getItems', []);

        return parent::getItems();
    }

    /**
     * {@inheritDoc}
     */
    public function addNonStockItem(\Rialto\Accounting\Ledger\Account\GLAccount $account)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addNonStockItem', [$account]);

        return parent::addNonStockItem($account);
    }

    /**
     * {@inheritDoc}
     */
    public function addItemFromPurchasingData(\Rialto\Purchasing\Catalog\PurchasingData $purchData, \Rialto\Stock\Item\Version\Version $version = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addItemFromPurchasingData', [$purchData, $version]);

        return parent::addItemFromPurchasingData($purchData, $version);
    }

    /**
     * {@inheritDoc}
     */
    public function addItem(\Rialto\Purchasing\Producer\StockProducer $poItem)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addItem', [$poItem]);

        return parent::addItem($poItem);
    }

    /**
     * {@inheritDoc}
     */
    public function removeLineItem(\Rialto\Purchasing\Producer\StockProducer $poItem)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removeLineItem', [$poItem]);

        return parent::removeLineItem($poItem);
    }

    /**
     * {@inheritDoc}
     */
    public function removeItemById($itemId)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removeItemById', [$itemId]);

        return parent::removeItemById($itemId);
    }

    /**
     * {@inheritDoc}
     */
    public function getLineItem(\Rialto\Stock\Item $item, \Rialto\Stock\Item\Version\Version $version = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLineItem', [$item, $version]);

        return parent::getLineItem($item, $version);
    }

    /**
     * {@inheritDoc}
     */
    public function getLineItemIfExists(\Rialto\Stock\Item $item, \Rialto\Stock\Item\Version\Version $version = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLineItemIfExists', [$item, $version]);

        return parent::getLineItemIfExists($item, $version);
    }

    /**
     * {@inheritDoc}
     */
    public function hasLineItem(\Rialto\Stock\Item $item)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'hasLineItem', [$item]);

        return parent::hasLineItem($item);
    }

    /**
     * {@inheritDoc}
     */
    public function getLineItems()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLineItems', []);

        return parent::getLineItems();
    }

    /**
     * {@inheritDoc}
     */
    public function hasItems()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'hasItems', []);

        return parent::hasItems();
    }

    /**
     * {@inheritDoc}
     */
    public function isFull()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isFull', []);

        return parent::isFull();
    }

    /**
     * {@inheritDoc}
     */
    public function hasNewItem()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'hasNewItem', []);

        return parent::hasNewItem();
    }

    /**
     * {@inheritDoc}
     */
    public function getNewItem()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getNewItem', []);

        return parent::getNewItem();
    }

    /**
     * {@inheritDoc}
     */
    public function setNewItem(\Rialto\Purchasing\Catalog\PurchasingData $newItem = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setNewItem', [$newItem]);

        return parent::setNewItem($newItem);
    }

    /**
     * {@inheritDoc}
     */
    public function canBeSent()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'canBeSent', []);

        return parent::canBeSent();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalCost()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTotalCost', []);

        return parent::getTotalCost();
    }

    /**
     * {@inheritDoc}
     */
    public function getDeliveryLocation()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDeliveryLocation', []);

        return parent::getDeliveryLocation();
    }

    /**
     * {@inheritDoc}
     */
    public function setDeliveryLocation(\Rialto\Stock\Facility\Facility $loc)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDeliveryLocation', [$loc]);

        return parent::setDeliveryLocation($loc);
    }

    /**
     * {@inheritDoc}
     */
    public function validateDeliveryAddress(\Symfony\Component\Validator\Context\ExecutionContextInterface $context)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'validateDeliveryAddress', [$context]);

        return parent::validateDeliveryAddress($context);
    }

    /**
     * {@inheritDoc}
     */
    public function getDeliveryAddress()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDeliveryAddress', []);

        return parent::getDeliveryAddress();
    }

    /**
     * {@inheritDoc}
     */
    public function hasSupplier()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'hasSupplier', []);

        return parent::hasSupplier();
    }

    /**
     * {@inheritDoc}
     */
    public function getSupplier()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSupplier', []);

        return parent::getSupplier();
    }

    /**
     * {@inheritDoc}
     */
    public function setSupplier(\Rialto\Purchasing\Supplier\Supplier $supplier)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSupplier', [$supplier]);

        return parent::setSupplier($supplier);
    }

    /**
     * {@inheritDoc}
     */
    public function getSupplierName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSupplierName', []);

        return parent::getSupplierName();
    }

    /**
     * {@inheritDoc}
     */
    public function getBuildLocationOrNull()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBuildLocationOrNull', []);

        return parent::getBuildLocationOrNull();
    }

    /**
     * {@inheritDoc}
     */
    public function getLocation()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLocation', []);

        return parent::getLocation();
    }

    /**
     * {@inheritDoc}
     */
    public function getBuildLocation(): \Rialto\Stock\Facility\Facility
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBuildLocation', []);

        return parent::getBuildLocation();
    }

    /**
     * {@inheritDoc}
     */
    public function isAllocateFromCM()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isAllocateFromCM', []);

        return parent::isAllocateFromCM();
    }

    /**
     * {@inheritDoc}
     */
    public function getSupplierContacts()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSupplierContacts', []);

        return parent::getSupplierContacts();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderContacts()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getOrderContacts', []);

        return parent::getOrderContacts();
    }

    /**
     * {@inheritDoc}
     */
    public function getKitContacts()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getKitContacts', []);

        return parent::getKitContacts();
    }

    /**
     * {@inheritDoc}
     */
    public function hasWorkOrders()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'hasWorkOrders', []);

        return parent::hasWorkOrders();
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkOrders()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getWorkOrders', []);

        return parent::getWorkOrders();
    }

    /**
     * {@inheritDoc}
     */
    public function allWorkOrdersHaveRequestedDate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'allWorkOrdersHaveRequestedDate', []);

        return parent::allWorkOrdersHaveRequestedDate();
    }

    /**
     * {@inheritDoc}
     */
    public function hasReworkOrder()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'hasReworkOrder', []);

        return parent::hasReworkOrder();
    }

    /**
     * {@inheritDoc}
     */
    public function getAllocationStatus()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAllocationStatus', []);

        return parent::getAllocationStatus();
    }

    /**
     * {@inheritDoc}
     */
    public function getComments()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getComments', []);

        return parent::getComments();
    }

    /**
     * {@inheritDoc}
     */
    public function setComments($comments)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setComments', [$comments]);

        return parent::setComments($comments);
    }

    /**
     * {@inheritDoc}
     */
    public function getSupplierReference()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSupplierReference', []);

        return parent::getSupplierReference();
    }

    /**
     * {@inheritDoc}
     */
    public function setSupplierReference($ref)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSupplierReference', [$ref]);

        return parent::setSupplierReference($ref);
    }

    /**
     * {@inheritDoc}
     */
    public function getDatePrinted()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDatePrinted', []);

        return parent::getDatePrinted();
    }

    /**
     * {@inheritDoc}
     */
    public function isPrinted()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isPrinted', []);

        return parent::isPrinted();
    }

    /**
     * {@inheritDoc}
     */
    public function setSent($sender, $note, string $fileName = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSent', [$sender, $note, $fileName]);

        return parent::setSent($sender, $note, $fileName);
    }

    /**
     * {@inheritDoc}
     */
    public function isSent()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isSent', []);

        return parent::isSent();
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSent()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDateSent', []);

        return parent::getDateSent();
    }

    /**
     * {@inheritDoc}
     */
    public function getFirstDateSent()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFirstDateSent', []);

        return parent::getFirstDateSent();
    }

    /**
     * {@inheritDoc}
     */
    public function getSendHistory()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSendHistory', []);

        return parent::getSendHistory();
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdated()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUpdated', []);

        return parent::setUpdated();
    }

    /**
     * {@inheritDoc}
     */
    public function getDateUpdated()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDateUpdated', []);

        return parent::getDateUpdated();
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestedDate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRequestedDate', []);

        return parent::getRequestedDate();
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestedDate(\DateTime $date = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setRequestedDate', [$date]);

        return parent::setRequestedDate($date);
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', []);

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, '__toString', []);

        return parent::__toString();
    }

    /**
     * {@inheritDoc}
     */
    public function getInitiator()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getInitiator', []);

        return parent::getInitiator();
    }

    /**
     * {@inheritDoc}
     */
    public function isInitiatedBy($initiator)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isInitiatedBy', [$initiator]);

        return parent::isInitiatedBy($initiator);
    }

    /**
     * {@inheritDoc}
     */
    public function getOwner()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getOwner', []);

        return parent::getOwner();
    }

    /**
     * {@inheritDoc}
     */
    public function getReceipts()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getReceipts', []);

        return parent::getReceipts();
    }

    /**
     * {@inheritDoc}
     */
    public function getReceiptItem(\Rialto\Stock\Move\StockMove $move)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getReceiptItem', [$move]);

        return parent::getReceiptItem($move);
    }

    /**
     * {@inheritDoc}
     */
    public function getDeliveryLocationId()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDeliveryLocationId', []);

        return parent::getDeliveryLocationId();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderDate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getOrderDate', []);

        return parent::getOrderDate();
    }

    /**
     * {@inheritDoc}
     */
    public function getStreet1(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStreet1', []);

        return parent::getStreet1();
    }

    /**
     * {@inheritDoc}
     */
    public function getStreet2(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStreet2', []);

        return parent::getStreet2();
    }

    /**
     * {@inheritDoc}
     */
    public function getMailStop(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getMailStop', []);

        return parent::getMailStop();
    }

    /**
     * {@inheritDoc}
     */
    public function getCity(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCity', []);

        return parent::getCity();
    }

    /**
     * {@inheritDoc}
     */
    public function getStateCode(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStateCode', []);

        return parent::getStateCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getStateName(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStateName', []);

        return parent::getStateName();
    }

    /**
     * {@inheritDoc}
     */
    public function getPostalCode(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPostalCode', []);

        return parent::getPostalCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getCountryCode(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCountryCode', []);

        return parent::getCountryCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getCountryName(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCountryName', []);

        return parent::getCountryName();
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeRate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getExchangeRate', []);

        return parent::getExchangeRate();
    }

    /**
     * {@inheritDoc}
     */
    public function getSupplierId()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSupplierId', []);

        return parent::getSupplierId();
    }

    /**
     * {@inheritDoc}
     */
    public function isCompleted()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isCompleted', []);

        return parent::isCompleted();
    }

    /**
     * {@inheritDoc}
     */
    public function getShipper()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getShipper', []);

        return parent::getShipper();
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingMethod()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getShippingMethod', []);

        return parent::getShippingMethod();
    }

    /**
     * {@inheritDoc}
     */
    public function setShippingMethod(\Rialto\Shipping\Method\ShippingMethod $method = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setShippingMethod', [$method]);

        return parent::setShippingMethod($method);
    }

    /**
     * {@inheritDoc}
     */
    public function isApproved(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isApproved', []);

        return parent::isApproved();
    }

    /**
     * {@inheritDoc}
     */
    public function isRejected(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isRejected', []);

        return parent::isRejected();
    }

    /**
     * {@inheritDoc}
     */
    public function isPendingApproval(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isPendingApproval', []);

        return parent::isPendingApproval();
    }

    /**
     * {@inheritDoc}
     */
    public function getApprovalStatus(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getApprovalStatus', []);

        return parent::getApprovalStatus();
    }

    /**
     * {@inheritDoc}
     */
    public function setApprovalStatus(string $status)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setApprovalStatus', [$status]);

        return parent::setApprovalStatus($status);
    }

    /**
     * {@inheritDoc}
     */
    public function getApprovalReason(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getApprovalReason', []);

        return parent::getApprovalReason();
    }

    /**
     * {@inheritDoc}
     */
    public function setApprovalReason($reason)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setApprovalReason', [$reason]);

        return parent::setApprovalReason($reason);
    }

    /**
     * {@inheritDoc}
     */
    public function validateApprovalReason(\Symfony\Component\Validator\Context\ExecutionContextInterface $context)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'validateApprovalReason', [$context]);

        return parent::validateApprovalReason($context);
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCurrency', []);

        return parent::getCurrency();
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPriority', []);

        return parent::getPriority();
    }

    /**
     * {@inheritDoc}
     */
    public function setPriority($priority)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPriority', [$priority]);

        return parent::setPriority($priority);
    }

    /**
     * {@inheritDoc}
     */
    public function getSupplierTasks()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSupplierTasks', []);

        return parent::getSupplierTasks();
    }

    /**
     * {@inheritDoc}
     */
    public function getEmployeeTasks()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEmployeeTasks', []);

        return parent::getEmployeeTasks();
    }

    /**
     * {@inheritDoc}
     */
    public function isAutoAddItems(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isAutoAddItems', []);

        return parent::isAutoAddItems();
    }

    /**
     * {@inheritDoc}
     */
    public function setAutoAddItems($autoAdd)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAutoAddItems', [$autoAdd]);

        return parent::setAutoAddItems($autoAdd);
    }

    /**
     * {@inheritDoc}
     */
    public function isAutoAllocateTo(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isAutoAllocateTo', []);

        return parent::isAutoAllocateTo();
    }

    /**
     * {@inheritDoc}
     */
    public function setAutoAllocateTo($allocateTo)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAutoAllocateTo', [$allocateTo]);

        return parent::setAutoAllocateTo($allocateTo);
    }

    /**
     * {@inheritDoc}
     */
    public function canSupplyItem(\Rialto\Stock\Item $item, \Rialto\Stock\Item\Version\Version $version): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'canSupplyItem', [$item, $version]);

        return parent::canSupplyItem($item, $version);
    }

    /**
     * {@inheritDoc}
     */
    public function getProductionNotes()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getProductionNotes', []);

        return parent::getProductionNotes();
    }

    /**
     * {@inheritDoc}
     */
    public function setProductionNotes($productionNotes)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setProductionNotes', [$productionNotes]);

        return parent::setProductionNotes($productionNotes);
    }

    /**
     * {@inheritDoc}
     */
    public function popEvents(): array
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'popEvents', []);

        return parent::popEvents();
    }

}
