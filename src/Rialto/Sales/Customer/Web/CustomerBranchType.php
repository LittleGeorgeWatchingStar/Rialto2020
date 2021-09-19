<?php

namespace Rialto\Sales\Customer\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Geography\Address\Web\AddressEntityType;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Customer\SalesArea;
use Rialto\Sales\Salesman\Salesman;
use Rialto\Shipping\Shipper\Orm\ShipperRepository;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Stock\Facility\Facility;
use Rialto\Tax\Authority\TaxAuthority;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerBranchType
extends DynamicFormType
{
    public function getBlockPrefix()
    {
        return 'CustomerBranch';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomerBranch::class,
        ]);
    }

    /**
     * @param CustomerBranch $branch
     */
    protected function updateForm(FormInterface $form, $branch)
    {
        $form->add('branchName', TextType::class, [
            'label' => 'Branch name',
        ])
        ->add('contactName', TextType::class, [
            'label' => 'Contact',
        ])
        ->add('address', AddressEntityType::class, [
            'label' => 'Address',
        ])
        ->add('salesman', EntityType::class, [
            'label' => 'Sales person',
            'class' => Salesman::class,
            'choice_label' => 'name',
        ])
        ->add('salesArea', EntityType::class, [
            'label' => 'Sales area',
            'class' => SalesArea::class,
            'choice_label' => 'description',
        ])
        ->add('defaultLocation', EntityType::class, [
            'label' => 'Draw stock from',
            'class' => Facility::class,
            'choice_label' => 'name',
        ])
        ->add('contactPhone', TextType::class, [
            'label' => 'Phone number',
            'required' => false,
        ])
        ->add('fax', TextType::class, [
            'label' => 'Fax number',
            'required' => false,
        ])
        ->add('email', EmailType::class, [
            'label' => 'Email',
        ])
        ->add('taxAuthority', EntityType::class, [
            'label' => 'Tax authority',
            'class' => TaxAuthority::class,
            'choice_label' => 'description',
        ])
        ->add('defaultShipper', EntityType::class, [
            'label' => 'Default freight company',
            'class' => Shipper::class,
            'query_builder' => function(ShipperRepository $repo) {
                return $repo->queryActive();
            },
            'choice_label' => 'name',
        ])
        ->add('customerBranchCode', TextType::class, [
            'label' => 'Customers internal branch code (EDI)',
            'required' => false,
        ]);
    }
}
