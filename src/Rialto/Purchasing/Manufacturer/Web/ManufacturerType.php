<?php

namespace Rialto\Purchasing\Manufacturer\Web;

use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for adding/editing manufacturers.
 */
class ManufacturerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('supplier', JsEntityType::class, [
                'class' => Supplier::class,
                'required' => false,
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
            ])
            ->add('logoFile', FileType::class, [
                'required' => false,
            ])
            ->add('conflictUrl', UrlType::class, [
                'required' => false,
            ])
            ->add('conflictFile', FileType::class, [
                'required' => false,
            ])
            ->add('smelterData', CheckboxType::class, [
                'required' => false,
                'label' => 'Detailed? (smelter data)',
            ])
            ->add('policy', ChoiceType::class, [
                'choices' => Manufacturer::getPolicyChoices(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Manufacturer::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Manufacturer';
    }

}
