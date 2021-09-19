<?php

namespace Rialto\Stock\Transfer\Web;

use Rialto\Shipping\Method\Web\ShippingMethodType;
use Rialto\Shipping\Shipper\Orm\ShipperRepository;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Stock\Transfer\Transfer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating a location transfer.
 */
class TransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $from = $options['from'];
        $item = $options['item'];
        $builder->add('destination', EntityType::class, [
            'class' => Facility::class,
            'query_builder' => function (FacilityRepository $repo) {
                return $repo->queryValidDestinations();
            },
            'label' => 'To',
        ]);

        $builder->add('shippingMethod', ShippingMethodType::class, [
            'label' => false,
        ]);

        $builder->add('bins', EntityType::class, [
            'class' => StockBin::class,
            'query_builder' => function (StockBinRepository $repo) use ($from, $item) {
                return $repo->queryByLocationAndItem($from, $item);
            },
            'choice_label' => 'labelWithQtyAndVersion',
            'multiple' => true,
            'expanded' => true,
            'attr' => ['class' => 'checkbox_group'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transfer::class,
            'validation_groups' => ['Default', 'create'],
        ]);
        $resolver->setRequired(['from', 'item']);
    }

    public function getBlockPrefix()
    {
        return 'Transfer';
    }

}
