<?php

namespace Proxies\__CG__\Rialto\Sales\Order;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class SalesOrderDetail extends \Rialto\Sales\Order\SalesOrderDetail implements \Doctrine\ORM\Proxy\Proxy
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
            return ['__isInitialized__', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'id', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'sourceID', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'customerPartNo', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'qtyInvoiced', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'baseUnitPrice', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'finalUnitPrice', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'qtyOrdered', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'discountRate', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'dateDispatched', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'completed', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'taxRate', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'salesOrder', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'stockItem', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'discountAccount', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'version', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'customization', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'chargeForCustomizations', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'requirements', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'dirtyRequirements'];
        }

        return ['__isInitialized__', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'id', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'sourceID', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'customerPartNo', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'qtyInvoiced', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'baseUnitPrice', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'finalUnitPrice', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'qtyOrdered', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'discountRate', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'dateDispatched', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'completed', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'taxRate', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'salesOrder', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'stockItem', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'discountAccount', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'version', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'customization', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'chargeForCustomizations', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'requirements', '' . "\0" . 'Rialto\\Sales\\Order\\SalesOrderDetail' . "\0" . 'dirtyRequirements'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (SalesOrderDetail $proxy) {
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
     * {@inheritDoc}
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);

        parent::__clone();
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
    public function addQuantityInvoiced($qty)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addQuantityInvoiced', [$qty]);

        return parent::addQuantityInvoiced($qty);
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'close', []);

        return parent::close();
    }

    /**
     * {@inheritDoc}
     */
    public function getAllocations()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAllocations', []);

        return parent::getAllocations();
    }

    /**
     * {@inheritDoc}
     */
    public function getRequirements()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRequirements', []);

        return parent::getRequirements();
    }

    /**
     * {@inheritDoc}
     */
    public function resetRequirements(\Doctrine\Common\Persistence\ObjectManager $om)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'resetRequirements', [$om]);

        return parent::resetRequirements($om);
    }

    /**
     * {@inheritDoc}
     */
    public function clearRequirements()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'clearRequirements', []);

        return parent::clearRequirements();
    }

    /**
     * {@inheritDoc}
     */
    public function getCogsAccount()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCogsAccount', []);

        return parent::getCogsAccount();
    }

    /**
     * {@inheritDoc}
     */
    public function getStockAccount()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStockAccount', []);

        return parent::getStockAccount();
    }

    /**
     * {@inheritDoc}
     */
    public function getSalesAccount()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSalesAccount', []);

        return parent::getSalesAccount();
    }

    /**
     * {@inheritDoc}
     */
    public function hasCustomizations()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'hasCustomizations', []);

        return parent::hasCustomizations();
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomization()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCustomization', []);

        return parent::getCustomization();
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomization(\Rialto\Manufacturing\Customization\Customization $cust = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCustomization', [$cust]);

        return parent::setCustomization($cust);
    }

    /**
     * {@inheritDoc}
     */
    public function isChargeForCustomizations()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isChargeForCustomizations', []);

        return parent::isChargeForCustomizations();
    }

    /**
     * {@inheritDoc}
     */
    public function setChargeForCustomizations($charge)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setChargeForCustomizations', [$charge]);

        return parent::setChargeForCustomizations($charge);
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscountAccount()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDiscountAccount', []);

        return parent::getDiscountAccount();
    }

    /**
     * {@inheritDoc}
     */
    public function setDiscountAccount(\Rialto\Accounting\Ledger\Account\GLAccount $account)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDiscountAccount', [$account]);

        return parent::setDiscountAccount($account);
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscountAccountId()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDiscountAccountId', []);

        return parent::getDiscountAccountId();
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscountRate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDiscountRate', []);

        return parent::getDiscountRate();
    }

    /**
     * {@inheritDoc}
     */
    public function setDiscountRate($rate)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDiscountRate', [$rate]);

        return parent::setDiscountRate($rate);
    }

    /**
     * {@inheritDoc}
     */
    public function getDiscountPercentage()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDiscountPercentage', []);

        return parent::getDiscountPercentage();
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
    public function getSourceId()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSourceId', []);

        return parent::getSourceId();
    }

    /**
     * {@inheritDoc}
     */
    public function setSourceId($sourceID)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSourceId', [$sourceID]);

        return parent::setSourceId($sourceID);
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
    public function getOrderNumber()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getOrderNumber', []);

        return parent::getOrderNumber();
    }

    /**
     * {@inheritDoc}
     */
    public function getSalesOrder()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSalesOrder', []);

        return parent::getSalesOrder();
    }

    /**
     * {@inheritDoc}
     */
    public function setSalesOrder(\Rialto\Sales\Order\SalesOrder $order)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSalesOrder', [$order]);

        return parent::setSalesOrder($order);
    }

    /**
     * {@inheritDoc}
     */
    public function isForSameOrder(\Rialto\Allocation\Consumer\StockConsumer $other)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isForSameOrder', [$other]);

        return parent::isForSameOrder($other);
    }

    /**
     * {@inheritDoc}
     */
    public function setQtyOrdered($qty)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setQtyOrdered', [$qty]);

        return parent::setQtyOrdered($qty);
    }

    /**
     * {@inheritDoc}
     */
    public function addQtyOrdered($diff)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addQtyOrdered', [$diff]);

        return parent::addQtyOrdered($diff);
    }

    /**
     * {@inheritDoc}
     */
    public function setQuantity($qty)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setQuantity', [$qty]);

        return parent::setQuantity($qty);
    }

    /**
     * {@inheritDoc}
     */
    public function getQtyOrdered()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getQtyOrdered', []);

        return parent::getQtyOrdered();
    }

    /**
     * {@inheritDoc}
     */
    public function getQuantity()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getQuantity', []);

        return parent::getQuantity();
    }

    /**
     * {@inheritDoc}
     */
    public function validateQuantity(\Symfony\Component\Validator\Context\ExecutionContextInterface $context)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'validateQuantity', [$context]);

        return parent::validateQuantity($context);
    }

    /**
     * {@inheritDoc}
     */
    public function getQtyInvoiced()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getQtyInvoiced', []);

        return parent::getQtyInvoiced();
    }

    /**
     * {@inheritDoc}
     */
    public function getQtyToShip()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getQtyToShip', []);

        return parent::getQtyToShip();
    }

    /**
     * {@inheritDoc}
     */
    public function getStandardCost()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStandardCost', []);

        return parent::getStandardCost();
    }

    /**
     * {@inheritDoc}
     */
    public function getSku()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSku', []);

        return parent::getSku();
    }

    /**
     * {@inheritDoc}
     */
    public function getStockCode()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStockCode', []);

        return parent::getStockCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomerPartNo(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCustomerPartNo', []);

        return parent::getCustomerPartNo();
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomerPartNo($partNo)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCustomerPartNo', [$partNo]);

        return parent::setCustomerPartNo($partNo);
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
    public function getStockItem()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStockItem', []);

        return parent::getStockItem();
    }

    /**
     * {@inheritDoc}
     */
    public function isMatch($stockCode, \Rialto\Manufacturing\Customization\Customization $c = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isMatch', [$stockCode, $c]);

        return parent::isMatch($stockCode, $c);
    }

    /**
     * {@inheritDoc}
     */
    public function getTaxRate(): float
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTaxRate', []);

        return parent::getTaxRate();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalQtyUndelivered()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTotalQtyUndelivered', []);

        return parent::getTotalQtyUndelivered();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalQtyOrdered()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTotalQtyOrdered', []);

        return parent::getTotalQtyOrdered();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalWeight()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTotalWeight', []);

        return parent::getTotalWeight();
    }

    /**
     * {@inheritDoc}
     */
    public function setBaseUnitPrice($price)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setBaseUnitPrice', [$price]);

        return parent::setBaseUnitPrice($price);
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseUnitPrice()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBaseUnitPrice', []);

        return parent::getBaseUnitPrice();
    }

    /**
     * {@inheritDoc}
     */
    public function getPriceAdjustment()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPriceAdjustment', []);

        return parent::getPriceAdjustment();
    }

    /**
     * {@inheritDoc}
     */
    public function getFinalUnitPrice()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFinalUnitPrice', []);

        return parent::getFinalUnitPrice();
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedPrice()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getExtendedPrice', []);

        return parent::getExtendedPrice();
    }

    /**
     * {@inheritDoc}
     */
    public function getUnitValue()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUnitValue', []);

        return parent::getUnitValue();
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedValue()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getExtendedValue', []);

        return parent::getExtendedValue();
    }

    /**
     * {@inheritDoc}
     */
    public function getUnitWeight()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUnitWeight', []);

        return parent::getUnitWeight();
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getVersion', []);

        return parent::getVersion();
    }

    /**
     * {@inheritDoc}
     */
    public function setVersion(\Rialto\Stock\Item\Version\Version $version)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setVersion', [$version]);

        return parent::setVersion($version);
    }

    /**
     * {@inheritDoc}
     */
    public function getFullSku()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFullSku', []);

        return parent::getFullSku();
    }

    /**
     * {@inheritDoc}
     */
    public function getVersionedStockCode()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getVersionedStockCode', []);

        return parent::getVersionedStockCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDescription', []);

        return parent::getDescription();
    }

    /**
     * {@inheritDoc}
     */
    public function getHarmonizationCode()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getHarmonizationCode', []);

        return parent::getHarmonizationCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getEccnCode()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEccnCode', []);

        return parent::getEccnCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getRoHS()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRoHS', []);

        return parent::getRoHS();
    }

    /**
     * {@inheritDoc}
     */
    public function getCountryOfOrigin()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCountryOfOrigin', []);

        return parent::getCountryOfOrigin();
    }

    /**
     * {@inheritDoc}
     */
    public function getWeight()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getWeight', []);

        return parent::getWeight();
    }

    /**
     * {@inheritDoc}
     */
    public function hasWeight()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'hasWeight', []);

        return parent::hasWeight();
    }

    /**
     * {@inheritDoc}
     */
    public function validateWeight(\Symfony\Component\Validator\Context\ExecutionContextInterface $context)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'validateWeight', [$context]);

        return parent::validateWeight($context);
    }

    /**
     * {@inheritDoc}
     */
    public function validateCountryOfOrigin(\Symfony\Component\Validator\Context\ExecutionContextInterface $context)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'validateCountryOfOrigin', [$context]);

        return parent::validateCountryOfOrigin($context);
    }

    /**
     * {@inheritDoc}
     */
    public function validateHarmonizationCode(\Symfony\Component\Validator\Context\ExecutionContextInterface $context)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'validateHarmonizationCode', [$context]);

        return parent::validateHarmonizationCode($context);
    }

    /**
     * {@inheritDoc}
     */
    public function isControlled()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isControlled', []);

        return parent::isControlled();
    }

    /**
     * {@inheritDoc}
     */
    public function isAssembly()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isAssembly', []);

        return parent::isAssembly();
    }

    /**
     * {@inheritDoc}
     */
    public function isDummy()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isDummy', []);

        return parent::isDummy();
    }

    /**
     * {@inheritDoc}
     */
    public function isPurchased()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isPurchased', []);

        return parent::isPurchased();
    }

    /**
     * {@inheritDoc}
     */
    public function hasSubcomponents()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'hasSubcomponents', []);

        return parent::hasSubcomponents();
    }

    /**
     * {@inheritDoc}
     */
    public function getBom(): \Rialto\Manufacturing\Bom\Bom
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBom', []);

        return parent::getBom();
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
    public function isCancelled()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isCancelled', []);

        return parent::isCancelled();
    }

    /**
     * {@inheritDoc}
     */
    public function isInvoiced()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isInvoiced', []);

        return parent::isInvoiced();
    }

    /**
     * {@inheritDoc}
     */
    public function isNew()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isNew', []);

        return parent::isNew();
    }

    /**
     * {@inheritDoc}
     */
    public function requiresAllocation()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'requiresAllocation', []);

        return parent::requiresAllocation();
    }

    /**
     * {@inheritDoc}
     */
    public function setTaxRate($rate)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setTaxRate', [$rate]);

        return parent::setTaxRate($rate);
    }

    /**
     * {@inheritDoc}
     */
    public function getDueDate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDueDate', []);

        return parent::getDueDate();
    }

    /**
     * {@inheritDoc}
     */
    public function getAllocationStatus(): \Rialto\Allocation\Status\DetailedRequirementStatus
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAllocationStatus', []);

        return parent::getAllocationStatus();
    }

}