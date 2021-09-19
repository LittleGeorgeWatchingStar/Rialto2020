<?php

namespace Rialto\Purchasing\Order\Web;

use Gumstix\FormBundle\Form\DynamicFormType;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\Web\StockProducerType;
use Rialto\Security\Role\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Form type for editing a purchase order.
 */
class EditPurchaseOrderType extends DynamicFormType
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $auth;

    public function __construct(AuthorizationCheckerInterface $auth)
    {
        $this->auth = $auth;
    }

    public function getBlockPrefix()
    {
        return 'PurchaseOrder';
    }

    public function getParent()
    {
        return PurchaseOrderType::class;
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('validation_groups', ['Default', 'allocationLocations']);
    }

    /**
     * @param PurchaseOrder $po
     */
    protected function updateForm(FormInterface $form, $po)
    {

        $form->add('autoAddItems', CheckboxType::class, [
            'required' => false,
            'label' => 'Auto-allocator can allocate items from this order?',
            'label_attr' => [
                'class' => 'checkbox',
                'title' => 'Allow auto-allocator to allocate parts from this order to other orders.',
            ],
        ]);
        $form->add('autoAllocateTo', CheckboxType::class, [
            'required' => false,
            'label' => 'Auto-allocator can allocate items to this order?',
            'label_attr' => [
                'class' => 'checkbox',
                'title' => 'Allow the auto-allocator to allocate parts to this order.',
            ],
        ]);
        $form->add('items', CollectionType::class, [
            'entry_type' => StockProducerType::class,
            'by_reference' => true,
            'allow_add' => false,
            'allow_delete' => false,
            'label' => false,
        ]);

        if ($po->hasSupplier()) {
            $supplier = $po->getSupplier();
            $form->add('newItem', EntityType::class, [
                'class' => PurchasingData::class,
                'query_builder' => function (PurchasingDataRepository $repo) use ($supplier) {
                    return $repo->createBuilder()
                        ->isActive()
                        ->bySupplier($supplier)
                        ->isPhysicalItem() // TODO: allow purchase of dummy items?
                        ->orderBySku()
                        ->getQueryBuilder();
                },
                'choice_label' => 'label',
                'required' => false,
                'placeholder' => $this->auth->isGranted(Role::PURCHASING) ? '-- custom --' : '',
            ]);
        }

        $form->add('syncAllocations', SubmitType::class, [
            'label' => 'Save and remove conflicting allocations',
            'validation_groups' => ['Default'],
        ]);
    }
}
