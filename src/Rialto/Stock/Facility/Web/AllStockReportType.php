<?php

namespace Rialto\Stock\Facility\Web;

use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AllStockReportType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('location', EntityType::class, [
            'class' => Facility::class,
            'query_builder' => function (FacilityRepository $repo) {
                return $repo->queryActive();
            },
            'choice_label' => 'name',
            'required' => false,
            'placeholder' => '-- all --',
        ]);
        $builder->add('sellable', ChoiceType::class, [
            'label' => 'Type',
            'choices' => [
                '-- all --' => '',
                'Products and boards' => 'yes',
                'Parts, PCBs, etc' => 'no',
            ],
            'required' => false,
        ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', AllStockReport::class);
        $resolver->setDefault('method', 'get');
        $resolver->setDefault('csrf_protection', false);
    }


}
