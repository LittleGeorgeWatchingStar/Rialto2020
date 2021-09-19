<?php

namespace Rialto\Manufacturing\Web\Facades;


use Rialto\Allocation\Requirement\RequirementTask\RequirementTask;
use Rialto\Allocation\Requirement\RequirementTask\RequirementTaskFactory;
use Rialto\Manufacturing\ClearToBuild\ClearToBuildEstimate;
use Rialto\Manufacturing\ClearToBuild\ClearToBuildFactory;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
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
                        }) }}">
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
        $template = $this->twig->createTemplate(
            '{% include "manufacturing/production/task/list.html.twig" with {
                tasks: order.supplierTasks,
                group: \'supplier\'
            } %}');
        return $template->render([
            'order'=> $this->purchaseOrder
        ]);
    }

    public function getSupplier()
    {
        return [
            'id' => $this->purchaseOrder->getSupplierId(),
            'name' => $this->purchaseOrder->getSupplierName(),
        ];
    }

    public function getPurchaseOrderNumberHtml()
    {
        $template = $this->twig->createTemplate(
            '{% if is_granted(\'ROLE_PURCHASING\') %}
                {{ purchase_order_link(order) }}
            {% else %}
                {{ order.id }}
            {% endif %}
            {% if order.supplierReference %}
                <div class="supplierReference tooltip"
                     title="{{ order.supplier }} reference no.">
                    {{ order.supplierReference }}
                </div>
            {% endif %}');
        return $template->render([
            'order'=> $this->purchaseOrder
        ]);
    }

    public function getPriorityHtml()
    {
        $template = $this->twig->createTemplate(
            '{% if order.priority %}
                {% if is_granted(\'ROLE_EMPLOYEE\') %}
                    <a href="{{ path(\'supplier_purchaseorder_requesteddate\', {
                        id: order.id
                    }) }}">
                        #{{ order.priority }}
                    </a>
                {% else %}
                    #{{ order.priority }}
                {% endif %}
            {% endif %}');
        return $template->render([
            'order'=> $this->purchaseOrder
        ]);
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
                }) }}">csv
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
