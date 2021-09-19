<?php


namespace Rialto\Purchasing\Manufacturer\Web;


use Rialto\Purchasing\Manufacturer\Manufacturer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManufacturerLogoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('logoFile', FileType::class, [
            'required' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Manufacturer::class);
    }

    public function getBlockPrefix()
    {
        return 'Manufacturer';
    }
}