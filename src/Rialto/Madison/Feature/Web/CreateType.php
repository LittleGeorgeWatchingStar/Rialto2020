<?php

namespace Rialto\Madison\Feature\Web;


use Rialto\Madison\Feature\StockItemFeature;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'CreateFeature';
    }

    public function getParent()
    {
        return EditType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('featureCode', FeatureType::class, [
            'mapped' => false,
            'required' => false,
            'placeholder' => '-- add a feature --',
        ]);
        $builder->remove('delete');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('item');
        $resolver->setAllowedTypes('item', StockItem::class);
        $resolver->setDefaults([
            'empty_data' => function(FormInterface $form) {
                $item = $form->getConfig()->getOption('item');
                $code = $form->get('featureCode')->getData();
                return $code ? new StockItemFeature($item, $code) : null;
            },
        ]);
    }

}
