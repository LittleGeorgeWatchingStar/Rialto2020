<?php

namespace Rialto\Purchasing\Receiving\Web;

use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class GrnFilter extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('supplier', JsEntityType::class, [
                'class' => Supplier::class,
                'required' => false,
            ])
            ->add('invoiced', ChoiceType::class, [
                'choices' =>[
                    'All' => '',
                    'No' => 'no',
                    'Partial' => 'partial'
                ],
                'required' => false,
            ])
            ->add('startDate', DateType::class, [
                'input' => 'string',
                'label' => "Between",
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'input' => 'string',
                'label' => 'and',
                'required' => false,
            ])
            ->add('_limit', IntegerType::class, [
                'required' => false,
                'attr' => ['title' => 'Enter 0 for no limit']
            ])
        ;
    }

    public function getParent()
    {
        return FilterForm::class;
    }

}
