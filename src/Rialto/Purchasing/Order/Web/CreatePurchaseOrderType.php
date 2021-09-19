<?php

namespace Rialto\Purchasing\Order\Web;

use Rialto\Purchasing\Order\PurchaseInitiator;
use Rialto\Purchasing\Order\PurchaseOrderFactory;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating a purchase order.
 */
class CreatePurchaseOrderType extends AbstractType implements PurchaseInitiator
{
    /** @var PurchaseOrderFactory */
    private $factory;

    public function __construct(PurchaseOrderFactory $factory)
    {
        $this->factory = $factory;
    }

    public function getInitiatorCode()
    {
        return 'PO System';
    }

    public function getBlockPrefix()
    {
        return 'PurchaseOrder';
    }

    public function getParent()
    {
        return PurchaseOrderType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('supplier', JsEntityType::class, [
            'class' => Supplier::class,
            'mapped' => false,
        ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('empty_data', function(FormInterface $form) {
            $supplier = $form->get('supplier')->getData();
            if (! $supplier instanceof Supplier) {
                throw new TransformationFailedException("Supplier is required");
            }
            $order = $this->factory->create($this);
            $order->setSupplier($supplier);
            return $order;
        });
    }
}
