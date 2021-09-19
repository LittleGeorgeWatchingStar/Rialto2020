<?php

namespace Rialto\Purchasing\Catalog\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\Version\Web\VersionChoiceType;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnexpectedValueException;

/**
 * For editing purchasing data records.
 */
class EditType extends DynamicFormType
{
    public function getParent()
    {
        return BaseType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchasingData::class,
            'validation_groups' => ['Default', 'edit']
        ]);
    }

    public function getBlockPrefix()
    {
        return 'PurchasingData';
    }

    protected function updateForm(FormInterface $form, $purchData)
    {
        /* @var $purchData PurchasingData */
        $stockItem = $purchData->getStockItem();

        if ($stockItem->isPurchased() || $stockItem->isDummy()) {
            if (! $purchData->getSupplier()) {
                $form->add('supplier', JsEntityType::class, [
                    'class' => Supplier::class,
                ]);
            }
        } elseif ($stockItem->isManufactured()) {
            if (! $purchData->getBuildLocation()) {
                $form->add('buildLocation', EntityType::class, [
                    'class' => Facility::class,
                    'query_builder' => function (FacilityRepository $repo) {
                        return $repo->queryValidDestinations();
                    },
                    'choice_label' => 'name',
                    'label' => "Manufacturing location",
                    'placeholder' => '-- choose --',
                ]);
            }
        } else {
            throw new UnexpectedValueException("Stock item is not a physical part");
        }

        $form->add('quotationNumber', TextType::class, [
            'label' => 'Quotation no.',
            'required' => false,
        ]);
        $form->add('supplierDescription', TextType::class, [
            'label' => 'Supplier description',
            'required' => false,
        ]);
        if ($stockItem->isVersioned()) {
            $form->add('version', VersionChoiceType::class, [
                'choices' => $this->getVersionChoices($purchData),
                'allow_any' => true,
            ]);
        }
        $form->add('qtyAvailable', IntegerType::class, [
            'label' => 'Current stock level',
            'required' => false,
        ]);
        $form->add('productUrl', UrlType::class, [
            'label' => "Supplier's product URL",
            'required' => false,
        ]);

        if ($stockItem instanceof ManufacturedStockItem) {
            $form->add('turnkey', CheckboxType::class, [
                'label' => 'Is a turnkey build?',
                'required' => false,
            ]);
        }

        $form->add('endOfLife', DateType::class, [
            'label' => 'End-of-life date',
            'required' => false,
        ]);

        $form->add('costBreaks', CollectionType::class, [
            'entry_type' => CostBreakType::class,
            'entry_options' => ['label' => false],
            'by_reference' => false,
            'prototype' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'label' => false,
        ]);
    }

    private function getVersionChoices(PurchasingData $purchData)
    {
        $stockItem = $purchData->getStockItem();
        $choices = $stockItem->getActiveVersions();
        $current = $purchData->getVersion();
        if ($current->isSpecified() && (! in_array($current, $choices))) {
            $choices[] = $current;
        }
        return $choices;
    }
}
