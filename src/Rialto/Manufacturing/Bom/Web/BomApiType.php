<?php

namespace Rialto\Manufacturing\Bom\Web;

use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing Bill of Materials (BOM) via the API.
 */
class BomApiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('bomItems', CollectionType::class, [
            'entry_type' => BomItemApiType::class,
            'by_reference' => false,
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemVersion::class,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Bom';
    }

}
