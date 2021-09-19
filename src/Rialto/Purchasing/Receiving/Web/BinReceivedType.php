<?php

namespace Rialto\Purchasing\Receiving\Web;

use Rialto\Stock\Bin\BinStyle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For entering bins that were received as part of a PO receipt.
 */
class BinReceivedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $itemReceived ItemReceived */
        $itemReceived = isset($options['item_received'])
            ? $options['item_received']
            : null;
        $builder->add('qtyReceived', IntegerType::class, [
            'label' => false,
        ]);
        $builder->add('binStyle', EntityType::class, [
            'class' => BinStyle::class,
            'data' => $itemReceived ? $itemReceived->getDefaultBinStyle() : null,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BinReceived::class,
        ]);
        /* item_received is used to populate the default bin style. */
        $resolver->setDefined('item_received');
    }
}
