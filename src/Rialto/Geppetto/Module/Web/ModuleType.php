<?php

namespace Rialto\Geppetto\Module\Web;

use Rialto\Geppetto\Module\Module;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Web\VersionTextType;
use Rialto\Web\Form\TextEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating Modules
 */
class ModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stockItem', TextEntityType::class, [
                'class' => StockItem::class
            ])
            ->add('version', VersionTextType::class)
            ->add('quantity', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Module::class,
        ]);
    }
}
