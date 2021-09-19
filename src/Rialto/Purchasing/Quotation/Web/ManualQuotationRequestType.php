<?php

namespace Rialto\Purchasing\Quotation\Web;


use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Orm\CustomizationRepository;
use Rialto\Purchasing\Supplier\Contact\Orm\SupplierContactRepository;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Rialto\Web\Form\CommaDelimitedArrayType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For manually creating an RFQ for a single item.
 */
class ManualQuotationRequestType extends DynamicFormType
{
    /**
     * @param ManualQuotationRequest $model
     */
    protected function updateForm(FormInterface $form, $model)
    {
        $item = $model->getStockItem();
        $category = $model->getStockCategory();
        $form
            ->add('contacts', EntityType::class, [
                'class' => SupplierContact::class,
                'query_builder' => function (SupplierContactRepository $repo) use ($category) {
                    return $repo->queryPotentialSuppliers($category);
                },
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'emailLabel',
                'label' => 'Supplier contacts',
                'group_by' => 'supplier.name',
            ])
            ->add('customization', EntityType::class, [
                'class' => Customization::class,
                'query_builder' => function (CustomizationRepository $repo) use ($item) {
                    return $repo->createBuilder()
                        ->bySku($item)
                        ->getQueryBuilder();
                },
                'required' => false,
                'placeholder' => '-- none --',
            ])
            ->add('quantities', CommaDelimitedArrayType::class, [
                'required' => false,
            ])
            ->add('leadTimes', CommaDelimitedArrayType::class, [
                'required' => false,
            ])
            ->add('comments', TextareaType::class, [
                'required' => false,
            ]);
    }

    public function getBlockPrefix()
    {
        return 'quotation_request';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ManualQuotationRequest::class,
        ]);
    }

}
