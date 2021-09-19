<?php

namespace Rialto\Magento2\Api\Web;

use Gumstix\Magento\Oauth\OAuthParams;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OAuthParamsType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oauth_consumer_key', TextType::class, [
                'required' => true,
            ])
            ->add('oauth_consumer_secret', TextType::class, [
                'required' => true,
            ])
            ->add('oauth_verifier', TextType::class, [
                'required' => true,
            ])
            ->add('store_base_url', TextType::class, [
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OAuthParams::class,
            'csrf_protection' => false,
        ]);
    }
}
