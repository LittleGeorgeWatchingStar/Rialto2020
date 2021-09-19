<?php

namespace Rialto\Magento2\Order;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Gumstix\GeographyBundle\Model\PostalAddress;
use JMS\Serializer\Annotation\Type;
use Rialto\Accounting\Card\CardTransaction;
use Rialto\Magento2\Storefront\Storefront;
use Rialto\Payment\PaymentMethod\Orm\PaymentMethodRepository;
use Rialto\Payment\PaymentMethod\PaymentMethod;
use Rialto\Sales\Order\Import\ImportableItem;
use Rialto\Sales\Order\Import\ImportableOrder;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Salesman\Salesman;
use Rialto\Sales\Type\SalesType;
use Rialto\Security\User\User;
use Rialto\Shipping\Method\Orm\ShippingMethodRepository;
use Rialto\Shipping\Method\ShippingMethod;

/**
 * A deserialized sales order from the Magento API.
 */
class Order implements ImportableOrder
{
    const AUTH_CODE_UNKNOWN = 'unknown';

    /**
     * The order status used when Magento thinks an order might be fraudulent.
     */
    const STATUS_FRAUD = 'fraud';

    /**
     * The order status used when the Magento order is canceled.
     */
    const STATUS_CANCELED = 'canceled';

    /** @var Storefront */
    private $store;

    /** @Type("string") */
    public $increment_id;

    /** @Type("string") */
    public $entity_id;

    /**
     * @var DateTime
     * @Type("DateTime<'Y-m-d H:i:s', 'UTC'>")
     */
    public $created_at;

    /**
     * @var DateTime
     * @Type("DateTime<'Y-m-d H:i:s', 'UTC'>")
     */
    public $updated_at;

    /** @Type("double") */
    public $shipping_amount;

    /**
     * @var Address
     * @Type("Rialto\Magento2\Order\Address")
     */
    public $billing_address;

    /**
     * @Type("string")
     */
    public $status;

    /**
     * @var OrderItem[]
     * @Type("array<Rialto\Magento2\Order\OrderItem>")
     */
    public $items;

    /**
     * @var Payment
     * @Type("Rialto\Magento2\Order\Payment")
     */
    public $payment;

    /**
     * @Type("string")
     */
    public $customer_note;

    /**
     * @Type("array")
     */
    public $extension_attributes;


    public function setStore(Storefront $store)
    {
        $this->store = $store;
    }

    /**
     * @return int
     */
    public function getSourceId()
    {
        return $this->entity_id;
    }

    /** @return User */
    public function getCreatedBy()
    {
        return $this->store->getUser();
    }

    public function getEmail()
    {
        return $this->billing_address->email;
    }

    public function getCompanyName()
    {
        return $this->billing_address->company ?: $this->getContactName();
    }

    public function getContactName()
    {
        return $this->getBillingName();
    }

    public function getContactPhone()
    {
        return $this->billing_address->telephone;
    }

    public function getCustomerReference()
    {
        return $this->increment_id;
    }

    /** @return PostalAddress */
    public function getCustomerAddress()
    {
        return $this->billing_address;
    }

    /** @return string */
    public function getTaxId()
    {
        return $this->billing_address->vat_id;
    }

    /** @return PostalAddress */
    public function getBillingAddress()
    {
        return $this->billing_address;
    }

    public function getBillingName()
    {
        return $this->billing_address->getFullName();
    }

    /** @return PostalAddress */
    public function getShippingAddress()
    {
        if (!$this->hasShippingInfo('address')) {
            return $this->getBillingAddress();
        }

        $data = $this->getShippingInfo('address');
        return $this->postalAddress($data);
    }

    /** @return PostalAddress */
    public function postalAddress(array $data)
    {
        $address = new Address();
        $address->address_type = isset($data['address_type']) ? $data['address_type'] : null;
        $address->city = isset($data['city']) ? $data['city'] : null;
        $address->company = isset($data['company']) ? $data['company'] : $address->firstname . ' ' . $address->lastname;
        $address->country_id = isset($data['country_id']) ? $data['country_id'] : null;
        $address->email = isset($data['email']) ? $data['email'] : null;
        $address->firstname = isset($data['firstname']) ? $data['firstname'] : null;
        $address->lastname = isset($data['lastname']) ? $data['lastname'] : null;
        $address->postcode = isset($data['postcode']) ? $data['postcode'] : null;
        $address->region = isset($data['region']) ? $data['region'] : null;
        $address->street = isset($data['street']) ? $data['street'] : null;
        $address->telephone = isset($data['telephone']) ? $data['telephone'] : null;
        return $address;
    }


    public function getShippingCompany()
    {
        if (!$this->hasShippingInfo('address')) {
            return $this->getCompanyName();
        }

        $comapny = isset($this->getShippingInfo('address')['company'])
            ? $this->getShippingInfo('address')['company']
            : $this->getShippingInfo('address')['firstname'] . ' ' . $this->getShippingInfo('address')['lastname'];
        return $comapny;
    }

    public function getShippingName()
    {
        $firstname = isset($this->getShippingInfo('address')['firstname'])
            ? $this->getShippingInfo('address')['firstname'] : "";
        $lastname = isset($this->getShippingInfo('address')['lastname'])
            ? $this->getShippingInfo('address')['lastname'] : "";
        return sprintf('%s %s', $firstname, $lastname);
    }

    /**
     * @param string $field
     * @return bool
     */
    private function hasShippingInfo($field)
    {
        return isset($this->extension_attributes['shipping_assignments'][0]['shipping'][$field]);
    }

    /**
     * @return array
     */
    private function getShippingInfo($field)
    {
        $base = $this->extension_attributes['shipping_assignments'][0]['shipping'];
        return isset($base[$field]) ? $base[$field] : [];
    }

    /** @return SalesType */
    public function getSalesType()
    {
        return $this->isQuote()
            ? $this->store->getQuoteType()
            : $this->store->getSalesType();
    }

    public function getSalesStage()
    {
        return $this->isQuote() ? SalesOrder::BUDGET : SalesOrder::ORDER;
    }

    public function getBranchCode()
    {
        return ''; // deprecated
    }

    /** @return Salesman */
    public function getSalesman()
    {
        return $this->store->getSalesman();
    }

    public function getComments()
    {
        return $this->customer_note;
    }

    /** @return DateTime */
    public function getDateOrdered()
    {
        return $this->prepDate($this->created_at);
    }

    public function getDateUpdated()
    {
        return $this->prepDate($this->updated_at);
    }

    private function prepDate(DateTime $date)
    {
        $prepped = clone $date;
        $prepped->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        return $prepped;
    }

    public function getShippingMethod(ObjectManager $om)
    {
        if (!$this->hasShippingInfo('method')) {
            return null;
        }

        // No support for flat rate yet, so treat flat rates as hand carried orders
        $shipping_method = $this->getShippingInfo('method');
        $method = $shipping_method;
        if ($method == 'flatrate_flatrate') {
            $method = 'Hand-carried_HAND';
        }

        list($shipperName, $methodCode) = explode('_', $method);
        /** @var $repo ShippingMethodRepository */
        $repo = $om->getRepository(ShippingMethod::class);
        try {
            return $repo->findByShipperNameAndCode($shipperName, $methodCode);
        } catch (NoResultException $ex) {
            $error = "Unknown shipping method " . $shipping_method;
            throw new \UnexpectedValueException($error, $ex->getCode(), $ex);
        } catch (NonUniqueResultException $ex) {
            $error = "Ambiguous shipping method " . $shipping_method;
            throw new \UnexpectedValueException($error, $ex->getCode(), $ex);
        }
    }

    private function normalizeShippingMethodName($methodName)
    {
        switch (strtolower($methodName)) {
            case '3 day select':
                return 'Three-Day Select';
            default:
                return $methodName;
        }
    }

    public function getShippingPrice()
    {
        return $this->shipping_amount;
    }

    /** @return ImportableItem[] */
    public function getItems()
    {
        return $this->items;
    }

    public function isMissingCardAuthoriation(): bool
    {
        if ($this->isQuote()) {
            return false; // Card auth not needed
        }
        return !$this->hasCardAuthorization();
    }

    private function hasCardAuthorization(): bool
    {
        return (bool) $this->payment->getCcType();
    }

    /** @return CardTransaction */
    public function getCardAuthorization(ObjectManager $om)
    {
        /** @var $repo PaymentMethodRepository */
        $repo = $om->getRepository(PaymentMethod::class);
        try {
            $card = $repo->findByAbbreviation($this->payment->getCcType());
        } catch (NoResultException $ex) {
            $error = "Unknown credit card " . $this->payment->getCcType();
            throw new \UnexpectedValueException($error, $ex->getCode(), $ex);
        } catch (NonUniqueResultException $ex) {
            $error = "Ambiguous credit card " . $this->payment->getCcType();
            throw new \UnexpectedValueException($error, $ex->getCode(), $ex);
        }
        return new CardTransaction(
            $card,
            $this->payment->getLastTransId(),
            self::AUTH_CODE_UNKNOWN, // Magento never tells us the auth code
            $this->payment->getProcessedAmount(),
            $this->getDateOrdered());
    }

    /**
     * @param CardTransaction $authorization The authorization transaction
     * @return CardTransaction The capture transaction
     * @throws \UnexpectedValueException If a matching capture cannot be found.
     */
    public function getCardCapture(CardTransaction $authorization)
    {
        $captureId = $authorization->getTransactionId() . '-capture';
        if ($this->payment->getLastTransId() == $captureId) {
            return $authorization->capture(
                $this->payment->getCapturedAmount(),
                $this->getDateUpdated());
        }
        throw new \UnexpectedValueException("Unable to find a capture for $authorization");
    }

    /**
     * @return bool is the payment method a banktransfer
     */
    public function isQuote()
    {
        return $this->payment->isBankTransfer();
    }

    public function isSuspectedFraud()
    {
        return self::STATUS_FRAUD === trim($this->status);
    }

    public function isCanceled()
    {
        return self::STATUS_CANCELED === trim($this->status);
    }
}
