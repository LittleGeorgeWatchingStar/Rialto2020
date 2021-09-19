<?php

namespace Rialto\Manufacturing\Kit\Web;


use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Shipping\Method\Web\ShippingMethodType;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Transfer\Transfer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form for starting a new work order kit.
 */
class BeginKitType extends DynamicFormType
{
    /**
     * @param Transfer $transfer
     */
    protected function updateForm(FormInterface $form, $transfer)
    {
        $dest = $transfer->getDestination();
        $form
            ->add('purchaseOrders', EntityType::class, [
                'class' => PurchaseOrder::class,
                'query_builder' => function (PurchaseOrderRepository $repo) use ($dest) {
                    return $repo->queryOrdersToKitByDestination($dest);
                },
                'multiple' => true,
                'expanded' => true,
                'constraints' => new Assert\Count(['min' => 1]),
            ])
            ->add('origin', EntityType::class, [
                'class' => Facility::class,
                'label' => 'Kit from',
            ])
            ->add('create', SubmitType::class, [
                'label' => 'Begin kitting',
            ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transfer::class,
        ]);
    }

}
