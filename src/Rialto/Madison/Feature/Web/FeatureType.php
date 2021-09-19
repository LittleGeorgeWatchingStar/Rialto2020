<?php

namespace Rialto\Madison\Feature\Web;


use Rialto\Madison\MadisonClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeatureType extends AbstractType
{
    /** @var MadisonClient */
    private $api;

    public function __construct(MadisonClient $api)
    {
        $this->api = $api;
    }

    public function getBlockPrefix()
    {
        return 'madison_feature';
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $features = $this->api->getFeatures();

        $resolver->setDefaults([
            'choices' => $this->getChoices($features),
            'choice_attr' => function($choice, $key, $value) use ($features) {
                $description = isset($features[$value]['description']) ?
                    $features[$value]['description'] :
                    '';
                return ['data-description' => $description];
            },
        ]);
    }

    private function getChoices($features)
    {
        $choices = [];
        foreach ($features as $code => $feature) {
            $name = $feature['name'];
            $choices[$name] = $code;
        }
        return $choices;
    }

}
