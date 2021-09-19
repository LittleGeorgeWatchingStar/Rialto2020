<?php

namespace Rialto\Stock\Transfer\Web;

use Rialto\Stock\Bin\StockBin;
use Rialto\Web\Form\TextEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for entering a TransferExtraItem.
 */
class TransferExtraItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stockBin', TextEntityType::class, [
                'class' => StockBin::class,
                'label' => 'Reel/bin ID',
            ])
            ->add('stockCode', TextType::class, [
                'label' => 'Stock code',
            ]);
    }

    public function getBlockPrefix()
    {
        return 'TransferExtraItem';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransferExtraItem::class,
        ]);
    }

}
