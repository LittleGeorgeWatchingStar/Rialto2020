<?php

namespace Rialto\Tax\Authority\Web;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Tax\Authority\TaxAuthority;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing tax authorities.
 */
class TaxAuthorityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextType::class, [])
            ->add('account', EntityType::class, [
                'class' => GLAccount::class,
            ])
            ->add('purchaseAccount', EntityType::class, [
                'class' => GLAccount::class,
                'label' => 'Purchase account',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TaxAuthority::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'TaxAuthority';
    }

}
