<?php

namespace Rialto\Stock\Item\Web;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 */
class StockItemTemplateApiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Maintain backwards-compatibility with "description" => "name".
        $builder->remove('name');
        $builder->add('description', TextType::class, [
            'property_path' => 'name',
        ]);

        $builder->add('pattern', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'POST',
            'allow_extra_fields' => true,
        ]);
    }

    public function getParent()
    {
        return StockItemTemplateType::class;
    }

    public function getBlockPrefix()
    {
        return 'StockItem';
    }
}
