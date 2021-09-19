<?php
namespace Rialto\Stock\Returns\Web;

use Craue\FormFlowBundle\Form\FormFlow;

/**
 * A "form flow" that manages the multi-step wizard for checking in returned
 * parts from a manufacturer.
 *
 * @see https://github.com/craue/CraueFormFlowBundle
 */
class ReturnedItemsFlow extends FormFlow
{
    public function getName()
    {
        return 'ReturnedItems';
    }

    protected function loadStepsConfig()
    {
        return [
            $this->binsStep(),
            $this->itemsStep(),
        ];
    }

    /**
     * In this step, the user enters IDs for those bins that can be
     * clearly identified.
     */
    private function binsStep()
    {
        return [
            'label' => 'bins',
            'form_type' => ReturnedBinsType::class,
        ];
    }

    /**
     * In this step, the user enters whatever information she has available
     * about each unidentified or ambiguous bin she has received.
     */
    private function itemsStep()
    {
        return [
            'label' => 'items',
            'form_type' => ReturnedItemsType::class,
        ];
    }
}
