<?php

namespace Rialto\Allocation\Web;

use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\AllocationInterface;
use Rialto\Allocation\Consumer\StockConsumer;
use Rialto\Allocation\EstimatedArrivalDate\EstimatedArrivalDateGenerator;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Requirement\RequirementTask\RequirementTask;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Allocation\Status\AllocationStatus;
use Rialto\Allocation\Status\DetailedRequirementStatus;
use Rialto\Manufacturing\Allocation\MissingStockConsumer;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\StockBin;
use Rialto\Web\EntityLinkExtension;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig_Function;
use UnexpectedValueException;


/**
 * Twig extensions for the allocation bundle.
 */
class AllocationExtension extends EntityLinkExtension
{
    /** @var RouterInterface */
    private $router;

    /** @var ValidatorInterface */
    private $validator;

    /** @var EstimatedArrivalDateGenerator */
    private $estimatedArrivalDateGenerator;

    public function __construct(
        AuthorizationCheckerInterface $auth,
        RouterInterface $router,
        ValidatorInterface $validator,
        EstimatedArrivalDateGenerator $estimatedArrivalDateGenerator)
    {
        parent::__construct($auth);
        $this->router = $router;
        $this->validator = $validator;
        $this->estimatedArrivalDateGenerator = $estimatedArrivalDateGenerator;
    }

    public function getFunctions()
    {
        return [
            $this->simpleMethod('allocation_consumer', 'allocationConsumer'),
            $this->simpleMethod('requirement_consumer', 'requirementConsumer'),
            $this->simpleMethod('allocation_source', 'source'),
            $this->simpleMethod('allocation_quantity', 'quantity'),
            $this->simpleMethod('rialto_consumer_status', 'consumerStatus'),
            $this->simpleMethod('consumer_allocation_link', 'consumerAllocationLink'),
            $this->simpleMethod('rialto_allocation_timeline', 'timeline'),
            $this->simpleMethod('allocation_producer', 'producer'),
            $this->simpleMethod('rialto_allocation_errors', 'errors'),
            $this->simpleMethod('expected_arrival_date', 'expectedArrivalDate'),
        ];
    }

    private function simpleMethod($name, $method)
    {
        return new Twig_Function(
            $name,
            [$this, $method],
            ['is_safe' => ['html']]
        );
    }

    public function allocationConsumer(StockAllocation $alloc, $absolute = false)
    {
        try {
            $consumer = $alloc->getConsumer();
        } catch (EntityNotFoundException $ex) {
            return $this->renderInvalid($alloc, [$ex->getMessage()]);
        }
        $label = $alloc->getConsumerDescription();
        return $this->consumer($consumer, $label, $absolute);
    }

    public function requirementConsumer(Requirement $requirement, $absolute = false)
    {
        return $this->consumer(
            $requirement->getConsumer(),
            $requirement->getConsumerDescription(),
            $absolute);
    }

    private function consumer(StockConsumer $consumer, $label, $absolute)
    {
        if ($consumer instanceof WorkOrder) {
            $entity = $consumer->getPurchaseOrder();
            return $this->poLink($entity, $label, $absolute);
        } elseif ($consumer instanceof SalesOrderDetail) {
            $entity = $consumer->getSalesOrder();
            return $this->salesOrderLink($entity, $label, $absolute);
        } elseif ($consumer instanceof MissingStockConsumer) {
            return $label;
        }
        throw $this->unexpectedType($consumer, 'consumer');
    }

    private function poLink(PurchaseOrder $po, $label, $absolute)
    {
        $url = $this->poUrl($po, $absolute);
        return $this->linkIfGranted(Role::EMPLOYEE, $url, $label);
    }

    private function poUrl(PurchaseOrder $po, $absolute = RouterInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate('purchase_order_view', [
            'order' => $po->getId(),
        ], $absolute);
    }

    private function salesOrderLink(SalesOrder $order, $label, $absolute)
    {
        $url = $this->router->generate('sales_order_view', [
            'order' => $order->getId(),
        ], $absolute);
        return $this->linkIfGranted(Role::EMPLOYEE, $url, $label);
    }

    private function unexpectedType($object, $type)
    {
        return new UnexpectedValueException(sprintf(
            'Unexpected %s type %s', $type, get_class($object)
        ));
    }

    /** @return string */
    private function renderInvalid(StockAllocation $alloc, $messages)
    {
        return sprintf('<span class="error">invalid allocation %s (%s)</span>',
            $alloc->getId(),
            join(', ', $messages)
        );
    }

    /**
     * @param AllocationInterface|BasicStockSource $arg
     * @return string
     */
    public function source($arg)
    {
        if ($arg instanceof AllocationInterface) {
            $source = $arg->getSource();
        } else {
            $source = $arg;
        }

        if ($source instanceof StockBin) {
            return $this->renderStockBin($source);
        } elseif ($source instanceof StockProducer) {
            return $this->producer($source);
        }
        throw $this->unexpectedType($source, "stock source");
    }

    /** @return string */
    private function renderStockBin(StockBin $bin)
    {
        $location = $bin->getLocation();
        $label = sprintf('%s (%s) %s',
            $bin,
            number_format($bin->getQuantity()),
            $location->getName()
        );
        return $this->stockBinLink($bin, $label);
    }

    private function stockBinLink(StockBin $bin, $label)
    {
        $url = $this->router->generate('stock_bin_view', [
            'bin' => $bin->getId(),
        ]);
        return $this->linkIfGranted(Role::EMPLOYEE, $url, $label);
    }

    /** @return string */
    public function quantity(StockAllocation $alloc)
    {
        return sprintf('%s from %s',
            number_format($alloc->getQtyAllocated()),
            $this->source($alloc)
        );
    }

    public function consumerStatus(StockConsumer $consumer)
    {
        $status = DetailedRequirementStatus::forConsumer($consumer);
        $lines = [];
        $lines[] = new ConsumerStatusLine($status->getQtyDelivered(), 'status-complete.png', 'delivered');
        $lines[] = new ConsumerStatusLine($status->getNetQtyAtLocation(), 'status-ok.png', 'in stock');
        $lines[] = new ConsumerStatusLine($status->getNetQtyElsewhere(), 'status-warning.png', 'elsewhere');
        $lines[] = new ConsumerOrderStatus($status->getNetQtyOrdered(), $status->getSentProducers(), 'status-warning.png');
        $lines[] = new ConsumerOrderStatus($status->getNetQtyToOrder(), $status->getUnsentProducers(), 'status-error.png');
        $lines[] = new ConsumerStatusLine($status->getQtyUnallocated(), 'status-error.png', 'unallocated');

        $per = $this->getRequirementsPerConsumer($consumer);
        $html = '';
        foreach ($lines as $line) {
            if ($line->isVisible()) {
                $html .= $this->statusIcon($line, $per);
            }
        }
        return $html;
    }

    private function getRequirementsPerConsumer(StockConsumer $consumer)
    {
        $total = 0;
        foreach ($consumer->getRequirements() as $req) {
            $total += $req->getUnitQtyNeeded();
        }
        return $total;
    }

    private function statusIcon(ConsumerStatusLine $line, $per)
    {
        $src = "/icons/{$line->icon}";
        $class = str_replace(' ', '_', "allocation {$line->text}");
        return strtr('<div class="_class"><img src="_src" alt="_text" /> _qty_per _text</div>', [
            '_qty' => number_format($line->qty),
            '_per' => ($per > 1) ? '/' . number_format($per) : '',
            '_src' => $src,
            '_text' => $line->text,
            '_class' => $class,
        ]);
    }

    /**
     * A link to the main allocation page for $consumer.
     *
     * @return string
     */
    public function consumerAllocationLink(StockConsumer $consumer)
    {
        if ($consumer instanceof WorkOrder) {
            return $this->router->generate('purchase_order_allocate', [
                'id' => $consumer->getOrderNumber(),
            ]);
        } elseif ($consumer instanceof SalesOrderDetail) {
            return $this->router->generate('sales_orderitem_allocate', [
                'id' => $consumer->getId(),
            ]);
        } else {
            return $this->router->generate('allocation_list', [
                'stockItem' => $consumer->getSku(),
            ]);
        }
    }

    public function timeline(SalesOrder $order)
    {
        if ($order->isCompleted()) {
            return '';
        }
        $status = $order->getAllocationStatus();
        if (!$status->isFullyAllocated()) {
            return 'not fully allocated';
        }
        $commitmentDate = $status->getLatestCommitmentDate();
        return $this->getTimelineLabel($status, $commitmentDate);
    }

    private function getTimelineLabel(AllocationStatus $status, DateTime $commitment = null)
    {
        if ($status->isKitComplete()) {
            return 'in stock';
        }
        if (!$commitment) {
            return 'no commitment';
        }
        if ($this->getDateDiff($commitment) == 0) {
            return 'due today';
        } elseif ($commitment < new DateTime()) {
            return 'overdue';
        }
        global $DefaultDateFormat;
        $format = $DefaultDateFormat ?: 'Y-m-d';
        return 'ready by ' . $commitment->format($format);
    }

    private function getDateDiff(DateTime $date)
    {
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $diff = $date->diff($today);
        return $diff->days;
    }

    /** @return string */
    public function producer(StockProducer $producer)
    {
        $po = $producer->getPurchaseOrder();
        return $this->renderPO($po);
    }

    /** @return string */
    private function renderPO(PurchaseOrder $po)
    {
        $label = $po->hasSupplier() ? 'PO #' : 'Asm ';
        $uri = $this->poUrl($po);
        $class = $po->isSent() ? '' : ' notSent';
        $icon = $this->getPoStatusIcon($po);
        $delivery = " to {$po->getDeliveryLocation()}";
        return sprintf('<a href="%s" class="po%s">%s%s%s%s</a>',
            $uri, $class, $label, $po->getId(), $delivery, $icon);
    }

    private function getPoStatusIcon(PurchaseOrder $po)
    {
        $status = $this->getPoStatus($po);
        return $status
            ? sprintf(' <span class="icon %s" title="%s"></span>', $status, $status)
            : '';
    }

    private function getPoStatus(PurchaseOrder $po)
    {
        if (!$po->isSent()) {
            return 'mail';
        }
        if (!$po->hasWorkOrders()) {
            return '';
        }
        $status = $po->getAllocationStatus();
        if (!$status->isFullyAllocated()) {
            return 'allocate';
        } elseif (!$status->isFullyStocked()) {
            return 'receive';
        } elseif (!$status->isEnRoute()) {
            return 'ship';
        }
        return '';
    }

    /** @return string */
    public function errors(StockAllocation $alloc)
    {
        $groups = ['Default', 'thorough'];
        $errors = $this->validator->validate($alloc, null, $groups);
        if (count($errors) == 0) {
            return '';
        }
        $output = '<ul class="errors">';
        foreach ($errors as $error) {
            $output .= "<li>{$error->getMessage()}</li>";
        }
        $output .= '</ul>';
        return $output;
    }

    /**
     * @param $arg AllocationInterface|RequirementTask
     */
    public function expectedArrivalDate($arg) {
        $source = $arg->getSource();
        $isWhereNeeded = $arg->isWhereNeeded();

        if ($arg instanceof RequirementTask) {
            $estimatedArrivalDate = $arg->getEstimatedArrivalDate();
            $commitmentDate = $arg->getCommitmentDate();
            $requestedDate = $arg->getRequestedDate();

        } else if ($arg instanceof AllocationInterface) {
            $estimatedArrivalDate = $this->estimatedArrivalDateGenerator->generate($arg);
            if ($source instanceof StockProducer) {
                $commitmentDate = $source->getCommitmentDate();
                $requestedDate = $source->getRequestedDate();
            } else {
                $commitmentDate = null;
                $requestedDate = null;
            }

        } else {
            return null;
        }

        if ($source instanceof StockBin) {
            if ($estimatedArrivalDate) {
                return $this->expectedArrivalDateOutput($estimatedArrivalDate);
            } else if (!$isWhereNeeded) {
                return '<span class="days-left no-date">Transfer required.</span>';
            }
        } else if ($source instanceof StockProducer) {
            $po = $source->getPurchaseOrder();
            if (!$po->isSent()) {
                return '<span class="days-left no-date">Unsent.</span>';
            }

            if ($commitmentDate) {
                return $this->expectedArrivalDateOutput($commitmentDate);
            }

            if ($requestedDate) {
                return $this->expectedArrivalDateOutput($requestedDate);
            }

            if ($estimatedArrivalDate) {
                return $this->expectedArrivalDateOutput($estimatedArrivalDate);
            }

            return '<span class="days-left no-date">Shipping info incomplete.</span>';
        }
        return null;
    }

    private function expectedArrivalDateOutput(DateTime $date): string
    {
        $now = new DateTime();
        $isLate = $date < $now;
        $class = 'days-left' . ($isLate ? ' late' : '');
        return "<span class=\"{$class}\" title=\"Expected arrival date\">{$date->format('Y-m-d')}</span>";
    }
}
