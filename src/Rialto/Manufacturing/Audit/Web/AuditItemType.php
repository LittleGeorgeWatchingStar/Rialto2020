<?php

namespace Rialto\Manufacturing\Audit\Web;


use Gumstix\FormBundle\Form\DynamicFormType;
use Rialto\Manufacturing\Audit\AuditItem;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing a AuditItem.
 */
class AuditItemType extends DynamicFormType
{
    /**
     * @param AuditItem $item
     */
    protected function updateForm(FormInterface $form, $item)
    {
        $qtyUndelivered = $item->getTotalQtyUndelivered();

        $attributes = [
            'class' => 'actualQty',
            'index' => $item->getFullSku(),
            'min' => 0,
            'max' => $qtyUndelivered,
        ];
        $form->add('actualQty', IntegerType::class, [
            'attr' => $attributes,
            'label' => false,
            'disabled' => $qtyUndelivered <= 0,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'AuditItem';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AuditItem::class,
        ]);
    }
}

