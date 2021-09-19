<?php

namespace Rialto\Manufacturing\WorkType\Web;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form for converting unprinted labels into printed ones.
 *
 * This is done via a work order that "builds" printed labels from blank ones.
 */
class IssueProductLabelType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', IntegerType::class, [
                'label' => 'How many labels actually printed?',
                'constraints' => new Assert\Range(['min' => 1]),
            ])
            ->add('submit', SubmitType::class);
    }

}
