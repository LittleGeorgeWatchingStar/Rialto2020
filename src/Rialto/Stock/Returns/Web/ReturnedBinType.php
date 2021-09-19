<?php

namespace Rialto\Stock\Returns\Web;


use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Bin\Web\BinStyleType;
use Rialto\Stock\Returns\ReturnedItem;
use Rialto\Web\Form\TextEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReturnedBinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bin', TextEntityType::class, [
                'class' => StockBin::class,
                'attr' => ['placeholder' => 'bin ID'],
            ])
            ->add('binStyle', BinStyleType::class, [
                'placeholder' => '-- bin style --',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ReturnedItem::class);
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            $bin = $form->get('bin')->getData();
            return $bin ? ReturnedItem::fromBin($bin) : null;
        });
    }

}
