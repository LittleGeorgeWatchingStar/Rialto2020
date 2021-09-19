<?php

namespace Rialto\Accounting\Transaction\Web;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Time\Web\IsoDateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionTemplateType extends AbstractType
{
    const CSRF_ID = 'create-transaction';

    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entries', CollectionType::class, [
                'entry_type' => EntryTemplateType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('date', IsoDateType::class)
            ->add('memo', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransactionTemplate::class,
            'csrf_token_id' => self::CSRF_ID,
        ]);
    }

}


class EntryTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account', EntityType::class, [
                'class' => GLAccount::class
            ])
            ->add('amount', NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EntryTemplate::class,
        ]);
    }

}
