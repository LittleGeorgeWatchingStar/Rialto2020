<?php

namespace Rialto\Manufacturing\Customization\Web;


use Rialto\Manufacturing\Customization\CustomizationStrategy;
use Rialto\Manufacturing\Customization\Customizer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For adding customization strategies to a Customization record.
 *
 * @see CustomizationStrategy
 */
class CustomizationStrategyType extends AbstractType
{
    /** @var Customizer */
    private $customizer;

    public function __construct(Customizer $customizer)
    {
        $this->customizer = $customizer;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('choices', $this->getChoices());
    }

    private function getChoices()
    {
        $strategies = $this->customizer->getRegisteredStrategies();
        $choices = array_keys($strategies);
        return array_combine($choices, $choices);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

}
