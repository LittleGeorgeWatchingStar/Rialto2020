<?php


namespace Rialto\Sales\Order\Import;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Sales\Salesman\Salesman;
use Rialto\Sales\Type\SalesType;
use Rialto\Security\User\User;
use Rialto\Shipping\Method\ShippingMethod;

interface ImportableOrder
{
    public function getSourceId();

    /** @return User */
    public function getCreatedBy();

    public function getEmail();

    public function getCompanyName();

    public function getContactName();

    public function getContactPhone();

    public function getCustomerReference();

    /** @return PostalAddress */
    public function getCustomerAddress();

    /** @return string */
    public function getTaxId();

    /** @return PostalAddress */
    public function getBillingAddress();

    public function getBillingName();

    /** @return PostalAddress */
    public function getShippingAddress();

    public function getShippingCompany();

    public function getShippingName();

    /** @return SalesType */
    public function getSalesType();

    public function getSalesStage();

    /** @deprecated */
    public function getBranchCode();

    /** @return Salesman */
    public function getSalesman();

    public function getComments();

    /** @return DateTime */
    public function getDateOrdered();

    /** @return ShippingMethod */
    public function getShippingMethod(ObjectManager $om);

    public function getShippingPrice();

    /** @return ImportableItem[] */
    public function getItems();
}
