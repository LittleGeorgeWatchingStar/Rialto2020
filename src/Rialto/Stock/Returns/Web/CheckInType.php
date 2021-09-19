<?php

namespace Rialto\Stock\Returns\Web;

use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Stock\Returns\ReturnedItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * For checking in returned items whose problems have been resolved.
 */
class CheckInType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('submit', SubmitType::class, [
                'label' => 'Check-in resolved items from:',
            ])
            ->add('location', EntityType::class, [
                'class' => Facility::class,
                'query_builder' => function (FacilityRepository $repo) {
                    return $repo->createQueryBuilder('l')
                        ->join(ReturnedItem::class, 'i', 'WITH', 'i.returnedFrom = l');
                },
                'placeholder' => '-- select --',
                'label' => false,
            ]);
    }
}
