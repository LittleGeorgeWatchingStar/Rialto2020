<?php

namespace Rialto\Purchasing\Catalog\Remote\Web;

use Rialto\Purchasing\Catalog\Remote\CatalogResult;
use Rialto\Stock\Item\Web\CreatePartType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For creating stock items from supplier catalog items.
 */
class CatalogResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('item', CreatePartType::class, [
                'label' => false,
            ])
            ->add('purchData', CollectionType::class, [
                'entry_type' => FromCatalogType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => true,
                'label' => 'Purchasing data'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CatalogResult::class);
        $resolver->setDefault('attr', ['class' => 'item']);
        $resolver->setDefault('validation_groups', ['Default', 'create']);
    }
}
