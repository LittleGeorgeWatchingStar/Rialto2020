<?php

namespace Rialto\Web\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Entity input type where the user types in the ID of the entity.
 */
class TextEntityType extends AbstractType
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new RialtoEntityToIdTransformer($this->om, $options['class']);
        $builder->addViewTransformer($transformer, true);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['class']);
        $resolver->setDefined(['property']);
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function getBlockPrefix()
    {
        return 'text_entity';
    }
}
