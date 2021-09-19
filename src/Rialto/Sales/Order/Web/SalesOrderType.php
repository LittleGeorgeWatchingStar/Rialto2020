<?php

namespace Rialto\Sales\Order\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Accounting\Currency\Currency;
use Rialto\Geography\Address\Web\AddressEntityType;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Type\SalesType;
use Rialto\Security\User\User;
use Rialto\Shipping\ReasonForShipping;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\DatalistType;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesOrderType extends DynamicFormType
{
    /**
     * @param SalesOrder $order
     */
    protected function updateForm(FormInterface $form, $order)
    {
        assertion($order instanceof SalesOrder);
        $customer = $order->getCustomer();
        $form->add('createdBy', EntityType::class, [
            'class' => User::class,
            'label' => 'Created By',
            'invalid_message' => 'Invalid User'
        ]);
        $form->add('customerBranch', EntityType::class, [
            'class' => CustomerBranch::class,
            'choices' => $customer->getBranches(),
            'label' => 'Customer branch',
            'invalid_message' => 'Invalid branch',
        ]);
        if (!$order->getId() || $order->isQuotation()) {
            $form->add('salesStage', ChoiceType::class, [
                'label' => 'Quote/order',
                'choices' => [
                    'Order' => SalesOrder::ORDER,
                    'Quotation' => SalesOrder::QUOTATION,
                    'Budget quotation' => SalesOrder::BUDGET,
                ],
                'invalid_message' => 'Invalid sales stage',
            ]);
        }
        $form->add('salesType', EntityType::class, [
            'class' => SalesType::class,
            'choice_label' => 'name',
            'label' => 'Type',
            'invalid_message' => 'Invalid sales type',
        ]);
        $form->add('billingName', TextType::class, [
            'label' => 'Bill to',
        ]);
        $form->add('billingAddress', AddressEntityType::class, [
            'label' => 'Address',
            'attr' => ['class' => 'address'],
            'invalid_message' => 'Invalid billing address',
        ]);
        $form->add('customerReference', TextType::class, [
            'label' => 'Customer Ref.',
            'required' => false,
        ]);
        $form->add('customerTaxId', TextType::class, [
            'label' => 'Customer Tax ID',
            'required' => false,
        ]);
        $form->add('comments', TextareaType::class, [
            'label' => 'Comments',
            'required' => false,
        ]);
        $form->add('productionNotes', TextareaType::class, [
            'label' => 'Production Notes',
            'required' => false,
        ]);
        $form->add('deliveryDate', DateType::class, [
            'label' => 'Requested delivery date',
            'required' => false,
        ]);
        $form->add('targetShipDate', DateType::class, [
            'required' => false,
        ]);
        $form->add('reasonForShipping', DatalistType::class, [
            'choices' => ReasonForShipping::all(),
            'invalid_message' => 'Invalid reason for shipping',
        ]);
        $form->add('deliveryCompany', TextType::class, [
            'label' => 'Company',
        ]);
        $form->add('deliveryName', TextType::class, [
            'label' => 'Ship to',
        ]);
        $form->add('shippingAddress', AddressEntityType::class, [
            'label' => 'Address',
            'attr' => ['class' => 'address'],
            'invalid_message' => 'Invalid shipping address',
        ]);
        $form->add('contactPhone', TextType::class, [
            'label' => 'Phone',
        ]);
        $form->add('email', TextType::class, [
            'label' => 'Email',
        ]);
        $form->add('reasonNotToShip', TextType::class, [
            'label' => 'Do Not Ship',
            'required' => false,
            'attr' => ['placeholder' => 'reason...'],
        ]);
        $form->add('shipFromFacility', EntityType::class, [
            'class' => Facility::class,
            'query_builder' => function (FacilityRepository $repo) {
                return $repo->queryValidDestinations();
            },
            'choice_label' => 'name',
            'label' => 'Ship from',
        ]);

        if ($order->containsShippableItems()) {
            $form->add('shippingPrice', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Shipping charge',
            ]);
        }

        $form->add('newItem', JsEntityType::class, [
            'class' => StockItem::class,
            'required' => !$order->hasItems(),
            'label' => 'Add item',
        ]);

        $form->add('submit', SubmitType::class);
    }

    public function getBlockPrefix()
    {
        return 'SalesOrder';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesOrder::class,
            'attr' => ['class' => 'standard'],
        ]);
    }
}


