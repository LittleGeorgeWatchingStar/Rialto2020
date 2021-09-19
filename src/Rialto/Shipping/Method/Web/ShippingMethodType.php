<?php

namespace Rialto\Shipping\Method\Web;

use Gumstix\FormBundle\Form\JsChoiceType;
use Rialto\Shipping\Shipper\Orm\ShipperRepository;
use Rialto\Shipping\Shipper\Shipper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ShippingMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shipper', EntityType::class, [
                'class' => Shipper::class,
                'query_builder' => function(ShipperRepository $repo) {
                    return $repo->queryActive();
                },
                'required' => $options['required'],
                'attr' => ['class' => 'shipper'],
                'placeholder' => '-- Select Shipper --'
            ])
            ->add('code', JsChoiceType::class, [
                'label' => 'Shipping method',
                'required' => $options['required'],
                'placeholder' => 'loading...',
                'attr' => ['class' => 'code'],
            ]);

        $transformer = new ShippingMethodToArrayTransformer();
        $builder->addViewTransformer($transformer);
    }
}
