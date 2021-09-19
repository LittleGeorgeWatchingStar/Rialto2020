<?php

namespace Rialto\Sales\GLPosting\Web;

use Rialto\Sales\Customer\SalesArea;
use Rialto\Sales\GLPosting\SalesGLPosting;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Category\StockCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For creating new SalesGLPostings.
 */
class SalesGLPostingCreateType extends AbstractType
{
    public function getParent()
    {
        return SalesGLPostingType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('create', CheckboxType::class, [
            'required' => false,
            'mapped' => false,
        ]);
        $builder->add('salesArea', EntityType::class, [
            'class' => SalesArea::class,
            'mapped' => false,
            'required' => false,
            'placeholder' => '-- any --',
        ]);
        $builder->add('salesType', EntityType::class, [
            'class' => SalesType::class,
            'mapped' => false,
            'required' => false,
            'placeholder' => '-- any --',
        ]);
        $builder->add('stockCategory', EntityType::class, [
            'class' => StockCategory::class,
            'mapped' => false,
            'required' => false,
            'placeholder' => '-- any --',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_data' => function(FormInterface $form) {
                $create = $form->get('create')->getData();
                logDebug($create, 'create');
                if (! $create ) {
                    return null;
                }
                $area = $form->get('salesArea')->getData();
                $type = $form->get('salesType')->getData();
                $category = $form->get('stockCategory')->getData();
                return new SalesGLPosting($area, $type, $category);
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SalesGLPostingCreate';
    }

}
