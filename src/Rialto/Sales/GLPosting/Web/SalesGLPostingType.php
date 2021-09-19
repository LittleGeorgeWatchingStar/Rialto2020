<?php

namespace Rialto\Sales\GLPosting\Web;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Sales\GLPosting\SalesGLPosting;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For editing existing SalesGLPostings.
 */
class SalesGLPostingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('salesAccount', EntityType::class, [
            'class' => GLAccount::class,
        ]);
        $builder->add('discountAccount', EntityType::class, [
            'class' => GLAccount::class,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesGLPosting::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SalesGLPosting';
    }

}
