<?php

namespace Rialto\Stock\Count\Web;

use Craue\FormFlowBundle\Form\FormFlow;

/**
 * Uploading a stock count csv is a multi-step process, so we use this
 * form wizard.
 */
class CsvStockCountFlow extends FormFlow
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'csv_stock_count';
    }

    public function loadStepsConfig()
    {
        return [
            [
                'label' => 'upload',
                'form_type' => CsvStockCountType::class,
            ],
            [
                'label' => 'review',
            ]
        ];
    }

    public function getFormOptions($step, array $options = [])
    {
        $options['validation_groups'] = ['Default'];
        return $options;
    }
}
