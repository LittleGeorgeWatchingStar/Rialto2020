<?php

namespace Rialto\Purchasing\Order\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Purchasing\Order\SingleItemPurchaseOrder;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Orm\ItemVersionRepository;
use Rialto\Time\Web\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For creating a PO for a single stock item.
 */
class SingleItemOrderType extends DynamicFormType
{
    /**
     * @param SingleItemPurchaseOrder $sipo
     */
    protected function updateForm(FormInterface $form, $sipo)
    {
        if ($sipo->isVersioned()) {
            $form->add('version', EntityType::class, [
                'class' => ItemVersion::class,
                'query_builder' => function (ItemVersionRepository $repo) use ($sipo) {
                    return $repo->queryActiveByItem($sipo);
                },
                'placeholder' => '-- choose --',
            ]);
        }
        $form->add('orderQty', IntegerType::class, [
            'label' => 'Quantity to order',
        ]);
        $form->add('requestedDate', DateType::class, [
            'label' => 'Needed by',
            'required' => false,
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
            'data_class' => SingleItemPurchaseOrder::class,
            'validation_groups' => ['purchasing', 'standardCost']
        ]);
    }

}
