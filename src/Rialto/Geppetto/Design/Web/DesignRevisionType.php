<?php

namespace Rialto\Geppetto\Design\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Geppetto\Design\DesignRevision;
use Rialto\Geppetto\Module\Web\ModuleType;
use Rialto\Measurement\Web\DimensionsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated
 * use @see DesignRevision2Type
 */
class DesignRevisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('versionCode', TextType::class)
            ->add('pcbDimensions', DimensionsType::class)
            ->add('boardDimensions', DimensionsType::class)
            ->add('modules', CollectionType::class, [
                'entry_type' => ModuleType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('price', MoneyType::class, [
                'currency' => Currency::USD,
                'scale' => 2,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DesignRevision::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'DesignRevision';
    }
}
