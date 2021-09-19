<?php

namespace Rialto\Filing\Document\Web;

use Rialto\Filing\Document\Document;
use Rialto\Filing\Document\DocumentField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for adding fields to a document.
 *
 * @see Document
 * @see DocumentField
 */
class DocumentFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value', TextType::class, [
                'label' => false,
                'required' => false,
        ])
            ->add('xPosition', IntegerType::class, [
                'label' => false,
            ])
            ->add('yPosition', IntegerType::class, [
                'label' => false,
            ])
            ->add('left', IntegerType::class, [
                'label' => false,
            ])
            ->add('alignment', ChoiceType::class, [
                'choices' => [
                    'left' => 'left',
                    'right' => 'right',
                ],
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DocumentField::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'DocumentField';
    }

}
