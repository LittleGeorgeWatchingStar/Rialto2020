<?php

namespace Rialto\Stock\Category\Web;

use Rialto\Stock\Category\StockCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For creating new StockCategories.
 */
class CreateStockCategoryType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'CreateStockCategory';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', TextType::class, [
            'label' => 'Category ID',
            'mapped' => false, // constructor argument
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_data' => function(FormInterface $form) {
                $id = $form->get('id')->getData();
                return new StockCategory($id);
            },
        ]);
    }

    public function getParent()
    {
        return StockCategoryType::class;
    }


}
