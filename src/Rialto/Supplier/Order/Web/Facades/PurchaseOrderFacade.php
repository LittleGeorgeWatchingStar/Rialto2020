<?php

namespace Rialto\Supplier\Order\Web\Facades;


use Rialto\Allocation\Requirement\RequirementTask\RequirementTask;
use Rialto\Allocation\Requirement\RequirementTask\RequirementTaskFactory;
use Rialto\Manufacturing\ClearToBuild\ClearToBuildEstimate;
use Rialto\Manufacturing\ClearToBuild\ClearToBuildFactory;
use Rialto\Manufacturing\WorkOrder\WorkOrderCollection;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Task\Task;
use Rialto\Util\Date\Date;
use Twig\Environment;

class PurchaseOrderFacade
{
    /** @var PurchaseOrder */
    private $purchaseOrder;

    /** @var RequirementTask[] */
    private $reqTasks = [];

    /** @var StockProducer[] */
    private $items = [];

    /** @var ClearToBuildEstimate */
    private $poClearToBuildEstimates = [];

    /** @var Environment */
    private $twig;

    public function __construct(PurchaseOrder $purchaseOrder, RequirementTaskFactory $factory, ClearToBuildFactory $clearToBuild, Environment $twig)
    {
        $this->purchaseOrder = $purchaseOrder;

        $this->reqTasks = $factory->getPurchaseOrderRequirementTasks($purchaseOrder);

        $this->poClearToBuildEstimates = $clearToBuild->getEstimateForPurchaseOrder($purchaseOrder);

        $this->twig = $twig;

        $this->items = $purchaseOrder->getLineItems();
    }

    public function getId()
    {
        return $this->purchaseOrder->getId();
    }

    public function getAgeHtml()
    {
        $template = $this->twig->createTemplate('{{ order.firstDateSent | overdueForManufacturingDashboard(\'-6 weeks\') }}');
        return $template->render([
            'order'=> $this->purchaseOrder
        ]);
    }

    public function getIdleHtml()
    {
        $template = $this->twig->createTemplate(
            '{% if order.isSent %}
                {% if is_granted(\'ROLE_PURCHASING\') %}
                    <a href="{{ path(\'supplier_po_pester\', {
                        order: order.id,
                        }) }}"
                       target="_top">
                        {{ order.dateUpdated | overdueForManufacturingDashboard(\'-1 day\') }}
                    </a>
                {% else %}
                    {{ order.dateUpdated | overdueForManufacturingDashboard(\'-1 day\') }}
                {% endif %}
            {% endif %}');
        return $template->render([
            'order'=> $this->purchaseOrder
        ]);
    }

    public function getSupplierTasksHtml()
    {
        $filteredTasks = array_filter($this->purchaseOrder->getSupplierTasks(), function (Task $task) {
            return $task->getName() !== Task::COMMITMENT_TASK_LABEL &&
                $task->getName() !== Task::DUE_TASK_LABEL;
        });
        return $this->twig->render('manufacturing/production/task/list.html.twig', [
            'tasks' => $filteredTasks,
            'group' => 'supplier']);
    }

    public function getPurchaseOrderNumberHtml()
    {
        $template = $this->twig->createTemplate('{{ purchase_order_link(order) }}
                                                {% if order.supplierReference %}
                                                    <div class="supplierReference tooltip"
                                                        title="{{ supplier.name }} reference no.">
                                                        {{ order.supplierReference }}
                                                    </div>
                                                {% endif %}');
        return $template->render([
            'order'=> $this->purchaseOrder,
            'supplier' => $this->purchaseOrder->getSupplier(),
        ]);
    }

    public function getPriorityHtml()
    {
        $template = $this->twig->createTemplate(
            '{% if order.priority %}
                {% if is_granted(\'ROLE_EMPLOYEE\') %}
                    <a href="{{ path(\'supplier_purchaseorder_requesteddate\', {
                        id: order.id
                    }) }}"
                       target="_top">
                        #{{ order.priority }} 
                        {% if order.requestedDate %}
                            <span class="requested-date">
                                {{ order.requestedDate | user_date }}
                            </span>
                        {% endif %}
                    </a>
                {% else %}
                    #{{ order.priority }}
                    {% if order.requestedDate %}
                        <span class="requested-date">
                            {{ order.requestedDate | user_date }}
                        </span>
                    {% endif %}
                {% endif %}
            {% endif %}');
        return $template->render([
            'order'=> $this->purchaseOrder
        ]);
    }

    public function getPoPriority(): ?int
    {
        return $this->purchaseOrder->getPriority();
    }

    public function getPoCommitmentDate(): ?string
    {
        $wos = WorkOrderCollection::fromPurchaseOrder($this->purchaseOrder);
        $date = $wos->getNextOutstandingCommitmentDate();
        if (null === $date) {
            return null;
        } else {
            return Date::toIso($date);
        }
    }

    public function getCommitmentDateDisplay()
    {
        $wos = WorkOrderCollection::fromPurchaseOrder($this->purchaseOrder);
        $date = $wos->getNextOutstandingCommitmentDate();
        if (null === $date) {
            $filteredTasks = array_filter($this->purchaseOrder->getSupplierTasks(), function (Task $task) {
                return $task->getName() === Task::COMMITMENT_TASK_LABEL;
            });
            return $this->twig->render('manufacturing/production/task/list.html.twig', [
                'tasks' => $filteredTasks,
                'group' => 'supplier']);

        } else {
            $filteredTasks = array_filter($this->purchaseOrder->getSupplierTasks(), function (Task $task) {
                return $task->getName() === Task::DUE_TASK_LABEL;
            });
            return $this->twig->render('manufacturing/production/task/list.html.twig', [
                'tasks' => $filteredTasks,
                'group' => 'supplier']);
        }
    }

    public function getItems()
    {
        $items = array_filter($this->items, function (StockProducer $item) {
            return $item->isWorkOrder();
        });
        return array_map(function (StockProducer $item) {
            return new ItemFacade($item);
        }, $items);
    }

    public function getPartsHtml()
    {
        $template = $this->twig->createTemplate(
            '<a href="{{ path(\'supplier_order_components\', {
                \'id\': order.id
                }) }}"
                         target="_top">csv
            </a>');
        return $template->render([
            'order'=> $this->purchaseOrder
        ]);
    }

    public function getCompanyTasksHtml()
    {
        $template = $this->twig->createTemplate(
            '{% include "manufacturing/production/task/list.html.twig" with {
                tasks: order.employeeTasks,
                group: \'employee\'
            } %}');
        return $template->render([
            'order'=> $this->purchaseOrder
        ]);
    }

    public function getTasks()
    {
        return array_map(function (RequirementTask $task) {
            return new ReqTaskFacade($task, $this->twig);
            }, $this->reqTasks);
    }

    public function getClearToBuild()
    {
        return new ClearToBuildFacade($this->poClearToBuildEstimates);
    }
}
