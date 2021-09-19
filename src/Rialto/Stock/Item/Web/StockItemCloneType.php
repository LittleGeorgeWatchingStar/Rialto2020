<?php

namespace Rialto\Stock\Item\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For cloning a stock item.
 */
class StockItemCloneType extends DynamicFormType
{
    /**
     * @param StockItemClone $clone
     */
    protected function updateForm(FormInterface $form, $clone)
    {
        $form->add('stockCode', TextType::class);
        if ( $clone->isVersioned() ) {
            $form->add('initialVersion', TextType::class);
            $form->add('copyBomFrom', EntityType::class, [
                'class' => ItemVersion::class,
                'choices' => $clone->getVersions(),
                'label' => 'Copy BOM from version'
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockItemClone::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'StockItemClone';
    }
}
