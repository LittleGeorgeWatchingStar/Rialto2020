<?php

namespace Rialto\Geppetto\Design\Web;


use Rialto\Accounting\Currency\Currency;
use Rialto\Geppetto\Design\DesignRevision2;
use Rialto\Geppetto\Module\Web\ModuleType;
use Rialto\Measurement\Web\DimensionsType;
use Rialto\Stock\Item\StockItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating Geppetto designs/design revisions.
 */
class DesignRevision2Type extends  AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('boardSku', EntityType::class, [
                'class' => StockItem::class,
                'property_path' => 'board',
            ])
            ->add('designName', TextType::class, [
                'empty_data' => '',
            ])
            ->add('designDescription', TextareaType::class, [
                'empty_data' => '',
            ])
            ->add('designPermalink', UrlType::class, [
                'required' => false,
            ])
            ->add('versionCode', TextType::class, [
                'empty_data' => '',
            ])
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
            ])
            ->add('designPublic', CheckboxType::class) // For Madison.
            ->add('designOwnerIdentifier', TextType::class, [ // For Madison.
                'empty_data' => '',
            ])
            ->add('imageUrl', TextType::class, [ // For Madison.
                'empty_data' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DesignRevision2::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'DesignRevision';
    }
}