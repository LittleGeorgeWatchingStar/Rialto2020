<?php

namespace Rialto\Web\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Entity input type where the list of choices is loaded via XHR.
 */
class JsEntityType extends AbstractType
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $dbm)
    {
        $this->om = $dbm;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['class']);
        $resolver->setDefined(['property']);

        $resolver->setDefaults([
            'choice_loader' => function (Options $options) {
                $transformer = new RialtoEntityToIdTransformer($this->om, $options['class']);
                return new JsEntityChoiceLoader($transformer);
            },
            'choice_label' => function (Options $options) {
                return function ($choice) {
                    return (string) $choice;
                };
            },
            'attr' => ['class' => 'js-entity'],
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'js_entity';
    }
}
