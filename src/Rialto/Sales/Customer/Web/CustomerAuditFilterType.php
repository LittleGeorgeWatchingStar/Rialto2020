<?php

namespace Rialto\Sales\Customer\Web;

use Rialto\Sales\Salesman\Salesman;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\ArrayToCommaDelimitedStringTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A standard set of filters for various customer audit reports.
 */
class CustomerAuditFilterType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startDate', DateType::class, [
                'input' => 'string',
                'required' => false,
        ])
            ->add('endDate', DateType::class, [
                'input' => 'string',
                'required' => false,
            ])
            ->add('country', CountryType::class, [
                'required' => false,
                'placeholder' => '-- any --',
            ])
            ->add(
                $builder->create('state', TextType::class, [
                    'required' => false,
                    'label' => 'State code',
                    'label_attr' => [
                        'class' => 'tooltip',
                        'title' => 'Comma-separated values are allowed, eg: GA,TN'
                    ],
                ])->addModelTransformer(new ArrayToCommaDelimitedStringTransformer())
            )
            ->add('salesman', EntityType::class, [
                'class' => Salesman::class,
                'required' => false,
                'placeholder' => '-- any --',
            ])
            ->add('filter', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'get',
        ]);
    }


}
