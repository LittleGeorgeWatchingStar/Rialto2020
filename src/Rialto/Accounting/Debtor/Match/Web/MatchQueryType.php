<?php

namespace Rialto\Accounting\Debtor\Match\Web;


use Rialto\Sales\Customer\Customer;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For selecting which debtor transactions to match off against each other.
 */
class MatchQueryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customer', JsEntityType::class, [
                'class' => Customer::class,
                'property' => 'name',
                'required' => false,
            ])
            ->add('since', DateType::class, [
                'required' => false,
                'label' => 'Transactions since'
            ])
            ->add('exact', CheckboxType::class, [
                'label' => 'Exact matches only?',
                'required' => false,
            ])
            ->add('strategy', ChoiceType::class, [
                'label' => 'Matching strategy',
                'choices' => [
                    'many-to-many' => 'many',
                    'one-to-one' => 'one',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('method', 'get');
        $resolver->setDefault('csrf_protection', false);
    }
}
