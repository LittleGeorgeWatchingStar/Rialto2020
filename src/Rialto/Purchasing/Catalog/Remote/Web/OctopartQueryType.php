<?php

namespace Rialto\Purchasing\Catalog\Remote\Web;

use Rialto\Purchasing\Catalog\Remote\OctopartQuery;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OctopartQueryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('supplier', JsEntityType::class, [
                'class' => Supplier::class,
                'required' => false,
            ])
            ->add('catalogNo', TextType::class, [
                'required' => false,
            ])
            ->add('manufacturerCode', TextType::class, [
                'required' => false,
            ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OctopartQuery::class,
            'method' => 'get',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return null;
    }
}
