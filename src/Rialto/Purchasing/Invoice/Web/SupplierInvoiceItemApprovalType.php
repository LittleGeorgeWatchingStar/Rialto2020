<?php

namespace Rialto\Purchasing\Invoice\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Account\Orm\GLAccountRepository;
use Rialto\Database\Orm\DbManager;
use Rialto\Purchasing\Invoice\SupplierInvoiceItem;
use Rialto\Purchasing\Receiving\GoodsReceivedItem;
use Rialto\Purchasing\Receiving\GoodsReceivedItemRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type used by SupplierInvoiceApprovalType.
 */
class SupplierInvoiceItemApprovalType extends DynamicFormType
{
    /** @var GLAccountRepository */
    private $repo;

    function __construct(DbManager $dbm)
    {
        $this->repo = $dbm->getRepository(GLAccount::class);
    }

    protected function updateForm(FormInterface $form, $invoiceItem)
    {
        /* @var $invoiceItem SupplierInvoiceItem */
        if ( $invoiceItem->isRegularItem() ) {
            $form->add('grnItems', EntityType::class, [
                'class' => GoodsReceivedItem::class,
                'query_builder' => function(GoodsReceivedItemRepository $repo) use ($invoiceItem) {
                    return $repo->queryBySupplierInvoiceItem($invoiceItem);
                },
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'choice_label' => 'summary',
                'required' => false,
                'attr' => ['class' => 'checkbox_group'],
            ]);
        }
        $form->add('GLAccount', EntityType::class, [
            'class' => GLAccount::class,
            'required' => false,
            'preferred_choices' => $this->repo
                ->findCommonInvoiceAccounts($invoiceItem->getSupplier()),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierInvoiceItem::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierInvoiceItem';
    }
}
