<?php

namespace Rialto\Stock\Shelf\Rack\Web;


use Rialto\Stock\Shelf\Shelf\Web\ShelfEditForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RackEditForm extends AbstractType
{
    public function getParent()
    {
        return RackBaseType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shelves', CollectionType::class, [
                'entry_type' => ShelfEditForm::class,
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', RackEdit::class);
    }

}
