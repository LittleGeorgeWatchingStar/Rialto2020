<?php

namespace Rialto\Manufacturing\Bom\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Manufacturing\Component\Web\ReferenceDesignatorType;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Orm\CustomizationRepository;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item\Version\Web\VersionChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Form type for editing an existing BOM item.
 *
 * @see BomItem
 */
class BomItemType extends DynamicFormType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BomItem::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'BomItem';
    }

    /**
     * @param BomItem $bomItem
     */
    protected function updateForm(FormInterface $form, $bomItem)
    {
        $component = $bomItem->getComponent();
        if ($component->isVersioned()) {
            $form->add('version', VersionChoiceType::class, [
                'choices' => $component->getActiveVersions(),
                'allow_any' => true,
            ]);
        }
        if ($component->isCustomizable()) {
            $form->add('customization', EntityType::class, [
                'class' => Customization::class,
                'query_builder' => function (CustomizationRepository $repo) use ($component) {
                    return $repo->createBuilder()
                        ->bySku($component->getSku())
                        ->getQueryBuilder();
                },
                'required' => false,
                'placeholder' => '-- none --',
            ]);
        }
        $form->add('quantity', IntegerType::class);
        $form->add('designators', ReferenceDesignatorType::class, [
            'attr' => ['rows' => 5, 'cols' => 50],
            'required' => false,
        ]);
        $form->add('workType', EntityType::class, [
            'class' => WorkType::class,
            'placeholder' => '-- choose --',
        ]);
        $form->add('primary', CheckboxType::class, [
            'label' => 'Primary Component?',
            'required' => false,
        ]);
    }
}
