<?php

namespace Rialto\Purchasing\Order\Web;

use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Shipping\Method\Web\ShippingMethodType;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing a purchase order.
 */
class PurchaseOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deliveryLocation', EntityType::class, [
                'class' => Facility::class,
                'query_builder' => function (FacilityRepository $repo) {
                    return $repo->queryValidDestinations();
                },
                'choice_label' => 'name',
                'label' => 'Delivery location',
                'placeholder' => '-- choose --',
            ])
            ->add('shippingMethod', ShippingMethodType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('comments', TextareaType::class, [
                'required' => false,
            ])
            ->add('productionNotes', TextareaType::class, [
                'required' => false,
            ])
            ->add('supplierReference', TextType::class, [
                'label' => 'Supplier reference',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrder::class,
        ]);
    }
}
