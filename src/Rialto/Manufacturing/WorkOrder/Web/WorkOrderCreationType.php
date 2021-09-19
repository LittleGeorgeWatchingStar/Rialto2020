<?php

namespace Rialto\Manufacturing\WorkOrder\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Orm\CustomizationRepository;
use Rialto\Manufacturing\WorkOrder\WorkOrderCreation;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form that binds to a WorkOrderCreation instance.
 */
class WorkOrderCreationType
extends DynamicFormType
{
    public function getBlockPrefix()
    {
        return 'WorkOrderCreation';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WorkOrderCreation::class,
        ]);
    }

    protected function updateForm(FormInterface $form, $creation)
    {
        /* @var $creation WorkOrderCreation */
        $parentItem = $creation->getParentItem();

        $form->add('purchasingData', EntityType::class, [
            'class' => PurchasingData::class,
            'query_builder' => function(PurchasingDataRepository $repo) use ($parentItem) {
                return $repo->queryActive($parentItem);
            },
            'choice_label' => 'supplierSummary',
            'label' => 'Location',
        ]);

        $versions = $parentItem->getActiveVersions();
        $form->add('version', EntityType::class, [
            'class' => ItemVersion::class,
            'choices' => $versions,
            'preferred_choices' => [$parentItem->getAutoBuildVersion()],
        ]);

        $form->add('customization', EntityType::class, [
            'class' => Customization::class,
            'query_builder' => function(CustomizationRepository $repo) use ($parentItem) {
                return $repo->createBuilder()
                    ->bySku($parentItem)
                    ->getQueryBuilder();
            },
            'placeholder' => 'No customization',
            'required' => false,
        ]);

        $form->add('qtyOrdered', IntegerType::class, [
            'label' => 'Quantity to order',
        ]);
        $form->add('openForAllocation', CheckboxType::class, [
            'label' => 'Is open for allocation?',
            'required' => false,
        ]);
        $form->add('instructions', TextareaType::class, [
            'required' => false,
            'label' => 'Custom instructions',
        ]);

        if ( $creation->hasChild() ) {
            $form->add('createChild', CheckboxType::class, [
                'label' => sprintf('Create child order for %s?', $creation->getChildItem()),
                'required' => false,
            ]);
        }
    }
}
