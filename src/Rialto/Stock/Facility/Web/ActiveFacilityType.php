<?php

namespace Rialto\Stock\Facility\Web;

use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type that allows the user to choose an active facility.
 */
class ActiveFacilityType extends AbstractType
{
    public function getParent()
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Facility::class,
            'query_builder' => function (FacilityRepository $repo) {
                return $repo->queryActive();
            },
            'invalid_message' => 'Invalid facility.',
        ]);
    }
}
