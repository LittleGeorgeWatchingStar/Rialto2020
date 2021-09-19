<?php

namespace Rialto\Sales\Shipping;

use Rialto\Alert\AlertMessage;
use Rialto\Alert\BasicAlertMessage;
use Rialto\Alert\LinkResolution;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Security\Role\Role;
use Rialto\Shipping\Export\DeniedPartyException;
use Rialto\Shipping\Export\DeniedPartyScreener;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Checks to see if a sales order can be approved to ship.
 */
class SalesOrderShippingApproval
{
    const MAX_OUTSTANDING_AMOUNT = 150;

    /** @var ValidatorInterface */
    private $validator;

    /** @var DeniedPartyScreener */
    private $dps;

    /** @var RouterInterface */
    private $router;

    /** @var AuthorizationCheckerInterface */
    private $authorization = null;

    /** @var AlertMessage[] */
    private $messages = [];

    public function __construct(
        ValidatorInterface $validator,
        DeniedPartyScreener $dps,
        RouterInterface $router,
        AuthorizationCheckerInterface $auth)
    {
        $this->validator = $validator;
        $this->dps = $dps;
        $this->router = $router;
        $this->authorization = $auth;
    }

    /** @return AlertMessage[] */
    public function validate(SalesOrder $order)
    {
        $this->messages = [];
        $this->validateOrder($order);
        $this->checkForDeniedParties($order);
        $this->checkPayments($order);
        return $this->messages;
    }

    private function validateOrder(SalesOrderInterface $order)
    {
        $violations = $this->validator->validate($order);
        foreach ($violations as $violation) {
            $message = BasicAlertMessage::createError($violation->getMessage());
            $this->messages[] = $message;
        }
    }

    /**
     * Checks that the order is not for a terrorist or other "denied party".
     */
    private function checkForDeniedParties(SalesOrderInterface $order)
    {
        if (!$this->dps->isEnabled()) return;
        try {
            $response = $this->dps->screen($order);
            if ($response->hasDeniedParties()) {
                $this->logDeniedPartyAlert($order);
            }
        } catch (DeniedPartyException $ex) {
            $alert = $this->createDeniedPartyAlert($ex->getMessage());
            $this->messages[] = $alert;
        }
    }

    private function logDeniedPartyAlert(SalesOrderInterface $order)
    {
        $message = sprintf(
            'Order %s is for a prohibited party.',
            $order->getOrderNumber()
        );
        $alert = $this->createDeniedPartyAlert($message);
        $alert->setResolution($this->getDeniedPartyResolution($order));
        $this->messages[] = $alert;
    }

    private function createDeniedPartyAlert($text)
    {
        return BasicAlertMessage::createError($text);
    }

    private function getDeniedPartyResolution(SalesOrderInterface $order)
    {
        $uri = $this->router->generate('Sales_SalesOrder_deniedParties', [
            'id' => $order->getOrderNumber()
        ]);
        return new LinkResolution($uri);
    }

    private function checkPayments(SalesOrder $order)
    {
        $cardAuth = $order->getCardAuthorization();

        $outstandingAmount = $order->getTotalPrice()
            - $order->getTotalAmountPaid()
            - ($cardAuth ? $cardAuth->getAmountAuthorized() : 0);

        if ($outstandingAmount >= self::MAX_OUTSTANDING_AMOUNT) {
            $daysDue = $order->getCustomer()->getPaymentTerms()->getDaysBeforeDue();
            if ($daysDue > 0) {
                $alert = BasicAlertMessage::createNotice(sprintf(
                    '$%s is still unpaid on order %s. Payment due in %s days',
                    number_format($outstandingAmount, 2),
                    $order->getOrderNumber(),
                    $daysDue
                ));
            } else {
                $alert = BasicAlertMessage::createWarning(sprintf(
                    '$%s is still unpaid on order %s.',
                    number_format($outstandingAmount, 2),
                    $order->getOrderNumber()
                ));
            }
            $this->messages[] = $alert;
        }
    }

    /**
     * @param AlertMessage[] $messages
     * @return bool
     */
    public function canApproveToShip(array $messages, bool $requestOverride)
    {
        foreach ($messages as $message) {
            if ($message->isError()) return false;
            if (!$requestOverride) return false;
            if ($message->isNotice()) continue;
            if (!$this->authorization->isGranted(Role::ACCOUNTING_OVERRIDE)) return false;
        }
        return true;
    }

    /**
     * @param AlertMessage[] $messages
     * @return bool
     */
    public function canOverrideWarnings(array $messages)
    {
        foreach ($messages as $message) {
            if ($message->isError()) return false;
            if ($message->isNotice()) continue;
            if (!$this->authorization->isGranted(Role::ACCOUNTING_OVERRIDE)) return false;
        }
        return true;
    }

    /**
     * @param AlertMessage[] $messages
     */
    public function canForceState(array $messages): bool
    {
        foreach ($messages as $message) {
            if ($message->getMessage() === 'State cannot be blank.') {
                return true;
            }
        }
        return false;
    }
}
