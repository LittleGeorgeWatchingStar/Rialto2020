<?php

namespace Rialto\Geography\Address\Web;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For editing Address instances.
 */
class AddressEntityType extends AbstractType
{
    /** @var AddressToArrayTransformer */
    private $transformer;

    public function __construct(AddressToArrayTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('street1', TextType::class)
            ->add('street2', TextType::class, ['required' => false])
            ->add('mailStop', TextType::class, ['required' => false])
            ->add('city', TextType::class)
            ->add('stateCode', TextType::class, ['label' => 'State/province'])
            ->add('postalCode', TextType::class, ['label' => 'Postal code'])
            ->add('countryCode', CountryType::class, [
                'label' => 'Country',
                'preferred_choices' => $options['preferred_countries'],
                'placeholder' => '-- country --',
            ]);

        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // The data transformer does this for us.
            'data_class' => null,

            // Top 5 countries we sell to, in order of frequency
            'preferred_countries' => ['US', 'GB', 'CA', 'AU', 'DE'],
        ]);
    }
}
