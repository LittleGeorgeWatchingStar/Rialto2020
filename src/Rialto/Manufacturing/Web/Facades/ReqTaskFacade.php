<?php

namespace Rialto\Manufacturing\Web\Facades;


use Rialto\Allocation\Requirement\RequirementTask\RequirementTask;
use Twig\Environment;

class ReqTaskFacade
{
    /** @var RequirementTask */
    private $requirementTask;

    /** @var Environment */
    private $twig;

    public function __construct(RequirementTask $requirementTask,  Environment $twig)
    {
        $this->requirementTask = $requirementTask;
        $this->twig = $twig;
    }

    public function getQty()
    {
        return $this->requirementTask->getQtyAllocated();
    }

    public function getFullSku()
    {
        return $this->requirementTask->getFullSku();
    }

    public function getHtml()
    {
        $template = $this->twig->createTemplate('<div>'.
            '{% if task.source %}'.
                '<span>'.
                    '{{ task.qtyAllocated | number_format }}'.
                    '&nbsp;'.
                    '&times;'.
                    '&nbsp;'.
                    '{{ task.fullSku }}'.
                    '&nbsp;'.
                    'from {{ allocation_source(task.source) }}  '.
                    '{{ expected_arrival_date(task) }}'.
                '</span>'.
            '{% else %}'.
                '<span class="attention">'.
                    '<a href="{{ path(\'purchase_order_allocate\', {
                        id: task.purchaseOrder.id,
                        fromCM: 1,
                    }) }}#to_allocate">'.
                        '{{ task.qtyAllocated }}'.
                        '&nbsp;'.
                        '&times;'.
                        '&nbsp;'.
                        '{{ task.fullSku }}'.
                        '&nbsp;'.
                        'unallocated.'.
                    '</a>'.
                '</span>'.
            '{% endif %}'.
        '</div>');
        return $template->render([
            'task'=> $this->requirementTask
        ]);
    }

    public function getEstimatedArrivalDate()
    {
        return $this->requirementTask->getEstimatedArrivalDate();
    }
}
