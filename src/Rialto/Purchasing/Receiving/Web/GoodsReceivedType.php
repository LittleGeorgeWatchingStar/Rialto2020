<?php

namespace Rialto\Purchasing\Receiving\Web;

use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Time\Web\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Form type for creating a GRN.
 */
class GoodsReceivedType extends AbstractType
{
    /** @var AuthorizationCheckerInterface */
    private $auth;

    public function __construct(AuthorizationCheckerInterface $auth)
    {
        $this->auth = $auth;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('receivedInto', EntityType::class, [
            'class' => Facility::class,
            'query_builder' => function (FacilityRepository $repo) {
                return $repo->queryActive();
            },
            'label' => 'Receive into',
        ]);
        $builder->add('items', CollectionType::class, [
            'entry_type' => ItemReceivedType::class,
            'by_reference' => true,
            'allow_add' => false,
            'allow_delete' => false,
        ]);
        $builder->add('allowReceive', CheckboxType::class, [
            'required' => true,
        ]);
        if ($this->auth->isGranted(Role::ADMIN)) {
            $builder->add('date', DateTimeType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GoodsReceived::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'GoodsReceived';
    }

}
