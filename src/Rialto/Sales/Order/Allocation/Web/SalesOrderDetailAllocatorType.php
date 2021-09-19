<?php

namespace Rialto\Sales\Order\Allocation\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Sales\Order\Allocation\SalesOrderDetailAllocator;
use Rialto\Sales\Order\Allocation\SalesOrderDetailAllocatorManufactured;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesOrderDetailAllocatorType extends DynamicFormType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesOrderDetailAllocator::class,
            'error_bubbling' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SalesOrderDetailAllocator';
    }

    /**
     * @param SalesOrderDetailAllocator $allocator
     */
    protected function updateForm(FormInterface $form, $allocator)
    {
        $form->add('selected', CheckboxType::class, [
            'required' => false,
            'error_bubbling' => false,
            'attr' => ['class' => 'allocBox'],
        ]);
        if ($allocator->isManufactured()) {
            /** @var SalesOrderDetailAllocatorManufactured $allocator */

            $form->add('createChild', CheckboxType::class, [
                'required' => false,
            ]);

            $form->add('qtyToOrder', IntegerType::class);

            $form->add('fabQtyToOrder', IntegerType::class);

            if (count($allocator->getPcbPurchasingDataChoices()) != 0) {
                $form->add('pcbPurchasingData', ChoiceType::class, [
                    'choices' => $allocator->getPcbPurchasingDataChoices(),
                    'choice_label' => function (PurchasingData $value, $key, $index) use ($allocator) {
                        $isPreferred = $value === $allocator->getDefaultpcbPurchasingData();
                        return $value->getSupplierName() . " version ". $value->getVersion() . ($isPreferred ? '**' : '');
                    },
                ]);
            }

            $form->add('buildLocation', ChoiceType::class, [
                'choices' => $allocator->getBuildLocations(),
                'choice_label' => function (Facility $value, $key, $index) use ($allocator) {
                    $isPreferred = $value === $allocator->getDefaultBuildLocation();
                    return $value->getName() . ($isPreferred ? '**' : '');
                },
            ]);
        }
    }
}
