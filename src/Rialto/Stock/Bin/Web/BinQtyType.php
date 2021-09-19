<?php

namespace Rialto\Stock\Bin\Web;

use Rialto\Stock\Bin\StockBin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For adjusting the quantity on a StockBin.
 */
class BinQtyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('newQty', IntegerType::class, [
            'label' => 'Quantity'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockBin::class,
        ]);
    }
}
