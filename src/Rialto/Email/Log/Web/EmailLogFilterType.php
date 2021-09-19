<?php

namespace Rialto\Email\Log\Web;

use MongoDB\BSON\Regex;
use Rialto\Time\Web\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailLogFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder
                ->create('since', DateType::class, [
                    'input' => 'string',
                    'format' => 'yyyy-MM-dd',
                    'property_path' => '[datetime]',
                    'required' => false
                ])
                ->addModelTransformer(new CallbackTransformer(
                    function ($fromModel) {
                        return $fromModel;
                    },
                    function ($fromForm) {
                        return $fromForm ? ['$gte' => $fromForm] : null;
                    }
                ))
            )
            ->add($builder
                ->create('to', SearchType::class, [
                    'required' => false,
                    'property_path' => '[context.to]',
                    'attr' => ['placeholder' => 'search...'],
                ])
                ->addModelTransformer(new CallbackTransformer(
                    function ($fromModel) {
                        return $fromModel;
                    },
                    function ($fromForm) {
                        return $fromForm
                            ? new Regex($fromForm, "i")
                            : null;
                    }
                ))
            )
            ->add($builder
                ->create('message', SearchType::class, [
                    'required' => false,
                    'attr' => ['placeholder' => 'search...'],
                ])
                ->addModelTransformer(new CallbackTransformer(
                    function ($fromModel) {
                        return $fromModel;
                    },
                    function ($fromForm) {
                        return $fromForm
                            ? new Regex($fromForm, "i")
                            : null;
                    }
                ))
            )
            ->add('limit', IntegerType::class, [
                'required' => false,
                'mapped' => false,
            ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('method', 'get');
        $resolver->setDefault('csrf_protection', false);
    }

    public function getBlockPrefix()
    {
        return null;
    }
}
