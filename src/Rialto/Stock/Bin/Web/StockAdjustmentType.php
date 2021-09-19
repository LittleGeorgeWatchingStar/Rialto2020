<?php

namespace Rialto\Stock\Bin\Web;

use Rialto\Security\Role\Role;
use Rialto\Stock\Count\StockAdjustment;
use Rialto\Time\Web\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form type for adjusting stock levels.
 */
class StockAdjustmentType extends AbstractType
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->authChecker = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('bins', CollectionType::class, [
            'entry_type' => StockBinAdjustmentType::class,
            'entry_options' => ['label' => false],
            'label' => false,
        ]);
        $builder->add('memo', TextType::class);

        if ($this->authChecker->isGranted(Role::ADMIN)) {
            /* Admins can create adjustments after the fact. */
            $builder->add('date', DateType::class, [
                'constraints' => [
                    new Assert\Range([
                        'max' => 'now',
                        'maxMessage' => 'Future dates are not allowed',
                    ])
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockAdjustment::class,
            'validation_groups' => ['Default', 'standardCost'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'StockAdjustment';
    }

}
