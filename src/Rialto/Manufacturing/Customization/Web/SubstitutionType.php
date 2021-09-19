<?php

namespace Rialto\Manufacturing\Customization\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Manufacturing\Component\Web\ReferenceDesignatorType;
use Rialto\Manufacturing\Customization\Substitution;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Form type for creating and editing Substitution records.
 */
class SubstitutionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => Substitution::getTypeOptions(),
                'placeholder' => '-- choose --',
            ])
            ->add('instructions', TextType::class)
            ->add('dnpDesignators', ReferenceDesignatorType::class, [
                'required' => false,
                'label' => 'DNP designators',
                'label_attr' => [
                    'class' => 'tooltip',
                    'title' => 'Comma-delimited list of designators',
                ],
            ])
            ->add('dnpComponent', JsEntityType::class, [
                'class' => StockItem::class,
                'required' => false,
                'label' => 'DNP component',
                'attr' => ['class' => 'component'],
            ])
            ->add('addDesignators', ReferenceDesignatorType::class, [
                'required' => false,
                'label' => 'Add designators',
                'label_attr' => [
                    'class' => 'tooltip',
                    'title' => 'Comma-delimited list of designators',
                ],
            ])
            ->add('addComponent', JsEntityType::class, [
                'class' => StockItem::class,
                'required' => false,
                'label' => 'Add component',
                'attr' => ['class' => 'component'],
            ])
            ->add('workType', EntityType::class, [
                'class' => WorkType::class,
                'required' => false,
            ])
            ->add('priceAdjustment', MoneyType::class, [
                'currency' => Currency::USD,
            ])
            ->add('flags', ChoiceType::class, [
                'required' => false,
                'choices' => Substitution::getFlagOptions(),
                'multiple' => true,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Substitution::class,
        ]);
    }
}
