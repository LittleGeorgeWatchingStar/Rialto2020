<?php

namespace Rialto\Sales\Returns\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Sales\Returns\SalesReturnItem;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * For editing items in a sales return.
 */
class SalesReturnItemType extends DynamicFormType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesReturnItem::class
        ]);
    }

    /**
     * @param SalesReturnItem $rmaItem
     */
    protected function updateForm(FormInterface $form, $rmaItem)
    {
        $form->add('qtyAuthorized', IntegerType::class);
        $form->add('passDisposition', ChoiceType::class, [
            'choices' => SalesReturnItem::getValidPassDispositions(
                $rmaItem->getStockItem()
            ),
        ]);
        $form->add('failDisposition', ChoiceType::class, [
            'choices' => SalesReturnItem::getValidFailDispositions(
                $rmaItem->getStockItem()
            ),
        ]);
    }
}
