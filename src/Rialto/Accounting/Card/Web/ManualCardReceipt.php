<?php

namespace Rialto\Accounting\Card\Web;

use DateTime;
use Rialto\Accounting\Card\CardTransaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Payment\PaymentMethod\PaymentMethod;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class ManualCardReceipt
{
    /**
     * @var SalesOrder
     * @Assert\NotNull()
     */
    private $salesOrder;

    /**
     * @var PaymentMethod
     * @Assert\NotNull()
     */
    private $card;

    /**
     * @var int
     * @Assert\Range(min=1)
     */
    public $transactionId;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(max="20")
     */
    public $authCode;

    /**
     * @var float
     * @Assert\Range(min=0.01)
     */
    public $amount;

    /**
     * @var DateTime
     * @Assert\NotNull()
     * @Assert\DateTime()
     */
    private $created;

    /**
     * @var bool
     */
    public $capture = false;

    public function __construct(array $data, DbManager $dbm)
    {
        $this->salesOrder = (! empty($data['salesOrder']))
            ? $dbm->find(SalesOrder::class, $data['salesOrder'])
            : null;
        $this->amount = isset($data['amount']) ? $data['amount'] : null;
    }

    /**
     * @return SalesOrder
     */
    public function getSalesOrder()
    {
        return $this->salesOrder;
    }

    public function setSalesOrder(SalesOrder $salesOrder = null)
    {
        $this->salesOrder = $salesOrder;
    }

    /**
     * @Assert\Callback()
     */
    public function validateMaxAmount(ExecutionContextInterface $context)
    {
        $outstanding = $this->salesOrder->getTotalAmountOutstanding();
        if ($this->amount > $outstanding) {
            $context->buildViolation('Only $_amt outstanding on _ord.')
                ->setParameter('_amt', number_format($outstanding, 2))
                ->setParameter('_ord', $this->salesOrder)
                ->atPath('amount')
                ->addViolation();
        }
    }

    /**
     * @return PaymentMethod
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param PaymentMethod $card
     */
    public function setCard(PaymentMethod $card)
    {
        $this->card = $card;
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated(DateTime $created)
    {
        $this->created = $created;
    }

    /** @return CardTransaction */
    public function createCardReceipt()
    {
        $cardTrans = new CardTransaction(
            $this->card,
            $this->transactionId,
            $this->authCode,
            $this->amount,
            $this->created
        );
        $this->salesOrder->addCardTransaction($cardTrans);

        if ($this->capture) {
            $cardTrans->capture($this->amount, $this->created);
        }
        return $cardTrans;
    }
}
