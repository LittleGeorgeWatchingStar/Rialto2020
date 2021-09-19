<?php

namespace Rialto\Payment;

use AuthorizeNetAIM;
use AuthorizeNetResponse;
use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Company\Company;
use Rialto\Database\Orm\DbManager;
use Rialto\Payment\PaymentMethod\Orm\PaymentMethodRepository;
use Rialto\Payment\PaymentMethod\PaymentMethod;
use Rialto\Sales\Order\SalesOrder;

class AuthorizeNet implements PaymentGateway
{
    const SIGN_PAYMENT = 1;
    const SIGN_REFUND = -1;

    /** @var AuthorizeNetFactory */
    private $factory;

    /** @var DbManager */
    private $dbm;

    /** @var PaymentMethodRepository */
    private $repo;

    /**
     * If true, requests will be sent to the Authorize.net test server
     * instead of the production server.
     * @var boolean
     */
    private $sandbox = false;

    private $logfile = '';

    public function __construct(AuthorizeNetFactory $factory,
                                DbManager $dbm)
    {
        $this->factory = $factory;
        $this->dbm = $dbm;
        $this->repo = $dbm->getRepository(PaymentMethod::class);
    }

    public function setSandbox($bool)
    {
        $this->sandbox = (bool) $bool;
        return $this;
    }

    public function setLogFile(string $filepath)
    {
        $this->logfile = $filepath;
    }

    /** @return AuthorizeNetAIM */
    private function createAim()
    {
        $aim = $this->factory->createAIM();
        $aim->setSandbox($this->sandbox);
        $company = Company::findDefault($this->dbm);
        $aim->description = $company->getName();
        $aim->setLogFile($this->logfile);
        return $aim;
    }


    /**
     * {@inheritdoc }
     */
    public function authorize(CardAuth $cardInfo, SalesOrder $order)
    {
        if ($order->isFullyPaid()) {
            throw new \InvalidArgumentException("$order is fully paid");
        } elseif ($order->getCardAuthorization()) {
            throw new \InvalidArgumentException("$order is already authorized");
        }
        $aim = $this->createAim();
        $aim->card_num = $cardInfo->getNumber();
        $aim->exp_date = $cardInfo->formatExpiry('m-Y');
        $aim->card_code = $cardInfo->getCode();
        $aim->amount = $cardInfo->getAmount();

        $this->addOrderInformation($aim, $order);
        $this->addDeliveryInformation($aim, $order);
        $this->addLineItems($aim, $order);

        $response = $aim->authorizeOnly();
        $cardTrans = $this->createAuthorization($response);
        $order->addCardTransaction($cardTrans);
        return $cardTrans;
    }

    private function addOrderInformation(AuthorizeNetAIM $aim, SalesOrder $order)
    {
        $aim->invoice_num = $order->getOrderNumber();
        $aim->po_num = $order->getCustomerReference();

        $aim->first_name = $order->getContactFirstName();
        $aim->last_name = $order->getContactLastName();
        $aim->company = $order->getCompanyName();
        $this->addAddress($aim, $order->getBillingAddress());
        $aim->phone = $order->getContactPhone();
        $aim->email = $order->getEmail();
        $aim->cust_id = $order->getCustomer()->getId();
    }

    private function addAddress(AuthorizeNetAIM $aim, PostalAddress $address, $prefix = '')
    {
        $aim->setFields([
            "{$prefix}address" => join(', ', array_filter([
                $address->getStreet1(),
                $address->getStreet2(),
                $address->getMailStop(),
            ])),
            "{$prefix}city" => $address->getCity(),
            "{$prefix}state" => $address->getStateCode(),
            "{$prefix}zip" => $address->getPostalCode(),
            "{$prefix}country" => $address->getCountryCode(),
        ]);
    }

    private function addDeliveryInformation(AuthorizeNetAIM $aim, SalesOrder $order)
    {
        $aim->ship_to_first_name = $order->getDeliveryFirstName();
        $aim->ship_to_last_name = $order->getDeliveryLastName();
        $aim->ship_to_company = $order->getDeliveryCompany();
        $this->addAddress($aim, $order->getDeliveryAddress(), "ship_to_");
    }

    private function addLineItems(AuthorizeNetAIM $aim, SalesOrder $order)
    {
        foreach ($order->getLineItems() as $item) {
            $aim->addLineItem(
                $item->getSku(),
                $item->getSku(),
                $item->getDescription(),
                $item->getQtyOrdered(),
                $item->getFinalUnitPrice(),
                ($item->getTaxRate() > 0) ? "Y" : "N");
        }
        $this->addAdditionalAmount($aim, 'tax', $order->getTaxAmount(), 'sales tax');
        $this->addAdditionalAmount($aim, 'freight', $order->getShippingPrice(),
            $order->getShippingMethod());
    }

    private function addAdditionalAmount(AuthorizeNetAIM $aim, $key, $amount, $description)
    {
        if ($amount <= 0) return;
        $aim->setField($key, join("<|>", [
            $key, $description, $amount
        ]));
    }

    /** @return CardTransaction */
    private function createAuthorization(AuthorizeNetResponse $response)
    {
        $this->checkResponse($response);
        return new CardTransaction(
            $this->getCreditCardFromResponse($response),
            $response->transaction_id,
            $response->authorization_code,
            $response->amount
        );
    }

    /**
     * @throws GatewayException
     *  If the response is rejected or has an error
     */
    private function checkResponse(AuthorizeNetResponse $response)
    {
        if (!$response->approved) {
            throw new AuthorizeNetException($response);
        }
        /* The amount returned from Authorize.net is always positive, even
         * for refunds, which is why we need the $sign argument. */
        assertion($response->amount >= 0);
    }

    /** @return PaymentMethod */
    private function getCreditCardFromResponse(AuthorizeNetResponse $response)
    {
        $type = $response->card_type;
        $card = $this->repo->findByName($type);
        if ($card) {
            return $card;
        }
        throw new \UnexpectedValueException("Unknown card type '$type'");
    }

    /**
     * {@inheritdoc }
     */
    public function chargeCard(CardTransaction $authorization, $amount, $invoiceNumber)
    {
        $aim = $this->createAim();
        $aim->invoice_num = $invoiceNumber;
        $response = $aim->priorAuthCapture($authorization->getTransactionId(), $amount);
        $this->checkResponse($response);
        $this->checkTransactionId($authorization, $response);
        return $authorization->capture($amount);
    }

    private function checkTransactionId(CardTransaction $original, AuthorizeNetResponse $response)
    {
        if ($original->getTransactionId() != $response->transaction_id) {
            throw new AuthorizeNetException($response, sprintf(
                'Response transaction ID %s does not match expected %s',
                $response->transaction_id,
                $original->getTransactionId()
            ));
        }
    }

    /**
     * {@inheritdoc }
     */
    public function void(CardTransaction $transaction, $invoiceNumber = null)
    {
        $aim = $this->createAim();
        if ($invoiceNumber) {
            $aim->invoice_num = $invoiceNumber;
        }
        $response = $aim->void($transaction->getTransactionId());
        $this->checkResponse($response);
        $transaction->setVoid(true);
    }

    /**
     * {@inheritdoc }
     */
    public function credit(CardTransaction $payment, $cardNumber, $amount = null)
    {
        assertion($payment->isCaptured(), "$payment has not been captured");

        $aim = $this->createAim();
        if (($order = $payment->getSalesOrder()) !== null) {
            $this->addOrderInformation($aim, $order);
        }
        if (null === $amount) {
            $amount = $payment->getAmountCaptured();
        } else {
            assertion($amount <= $payment->getAmountCaptured());
        }
        assertion($amount > 0);
        $response = $aim->credit($payment->getTransactionId(), $amount, $cardNumber);
        $cardTrans = $this->createRefund($payment, $response);
        assertion($cardTrans->getAmountCaptured() < 0);
        return $cardTrans;
    }

    /** @return CardTransaction */
    private function createRefund(CardTransaction $payment, AuthorizeNetResponse $response)
    {
        $this->checkResponse($response);
        return CardTransaction::refund(
            $payment,
            $this->getCreditCardFromResponse($response),
            $response->transaction_id,
            $response->authorization_code,
            -$response->amount
        );
    }

    public function getDepositAccount()
    {
        return GLAccount::fetchAuthorizeNet($this->dbm);
    }

    public function getFeeAccount()
    {
        return GLAccount::fetchAccruedTransactionFees($this->dbm);
    }

    public function getName()
    {
        return 'Authorize.net';
    }

}

