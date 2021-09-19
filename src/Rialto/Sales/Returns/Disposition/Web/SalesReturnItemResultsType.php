<?php

namespace Rialto\Sales\Returns\Disposition\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Sales\Returns\Disposition\SalesReturnItemResults;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for entering the test results for a sales return item.
 */
class SalesReturnItemResultsType
extends DynamicFormType
{
    /**
     * @param SalesReturnItemResults $dispItem
     */
    protected function updateForm(FormInterface $form, $dispItem)
    {
        $rmaItem = $dispItem->getSalesReturnItem();
        $form->add('qtyPassed', IntegerType::class)
            ->add('qtyFailed', IntegerType::class)
            ->add('failureReason', TextType::class, [
                'required' => false,
            ])
            ->add('stockBin', EntityType::class, [
                'class' => StockBin::class,
                'query_builder' => function(StockBinRepository $repo) use ( $rmaItem ) {
                    return $repo->queryForSalesReturnItem($rmaItem);
                },
                'choice_label' => 'labelWithQuantity',
                'expanded' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesReturnItemResults::class
        ]);
    }
}

