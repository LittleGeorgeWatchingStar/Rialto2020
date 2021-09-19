<?php

namespace Rialto\Stock\Item\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Orm\ItemVersionRepository;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for quickly adding a packaging product to a board.
 */
class StockItemPackageType extends DynamicFormType
{
    /**
     * @param FormInterface $form
     * @param StockItemPackage $packager
     */
    protected function updateForm(FormInterface $form, $packager)
    {
        $form
            ->add('sku', TextType::class, [
                'label' => 'New SKU',
            ])
            ->add('board', EntityType::class, [
                'class' => ItemVersion::class,
                'choices' => $packager->getValidBoards(),
                'choice_label' => 'codeWithDimensions',
                'label' => 'Version',
            ])
            ->add('box', EntityType::class, [
                'class' => ItemVersion::class,
                'query_builder' => function(ItemVersionRepository $repo) {
                    return $repo->queryEligibleBoxes();
                },
                'required' => false,
                'placeholder' => '-- no box --',
                'choice_label' => 'fullCodeWithDimensions',
            ])
            ->add('label', JsEntityType::class, [
                'class' => StockItem::class,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockItemPackage::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'StockItemPackage';
    }
}
