<?php

namespace Rialto\Stock\Category\Web;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Account\Orm\GLAccountRepository;
use Rialto\Stock\Category\StockCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockCategoryType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'StockCategory';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockCategory::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
                'label' => 'Name',
            ])
            ->add('stockType', ChoiceType::class, [
                'label' => 'Stock Type',
                'choices' => [
                    'Finished Goods' => StockCategory::FINISHED_GOODS,
                    'Raw Materials' => StockCategory::RAW_MATERIALS,
                    'Dummy Items (No Movements)' => StockCategory::DUMMY_ITEMS_NO_MOVEMENTS,
                    'Labour' => StockCategory::LABOUR,
                ],
            ])
            ->add('stockAccount', EntityType::class, [
                'class' => GLAccount::class,
                'label' => 'Stock Account',
                'choice_label' => 'name',
                'query_builder' => function(GLAccountRepository $repo) {
                    return $repo->queryNonProfitAndLoss();
                }
            ])
            ->add('adjustmentAccount', EntityType::class, [
                'class' => GLAccount::class,
                'label' => 'Adjustment Account',
                'choice_label' => 'name',
                'query_builder' => function(GLAccountRepository $repo) {
                    return $repo->queryProfitAndLoss();
                }
            ])
            ->add('purchasePriceVarianceAccount', EntityType::class, [
                'class' => GLAccount::class,
                'label' => 'Purchase Price Variance Account',
                'choice_label' => 'name',
                'query_builder' => function(GLAccountRepository $repo) {
                    return $repo->queryProfitAndLoss();
                }
            ])
            ->add('materialUsageVarianceAccount', EntityType::class, [
                'class' => GLAccount::class,
                'label' => 'Material Usage Variance Account',
                'choice_label' => 'name',
                'query_builder' => function(GLAccountRepository $repo) {
                    return $repo->queryProfitAndLoss();
                }
            ])
            ->add('wipAccount', EntityType::class, [
                'class' => GLAccount::class,
                'label' => 'WIP Account',
                'choice_label' => 'name',
                'query_builder' => function(GLAccountRepository $repo) {
                    return $repo->queryNonProfitAndLoss();
                }
            ]);
    }
}
