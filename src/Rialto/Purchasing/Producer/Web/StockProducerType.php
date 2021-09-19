<?php

namespace Rialto\Purchasing\Producer\Web;

use Gumstix\FormBundle\Form\ConditionDecorator;
use Gumstix\FormBundle\Form\DynamicFormType;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Account\Orm\GLAccountRepository;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Orm\CustomizationRepository;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Producer\StockProducerVoter;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\Version\Web\VersionChoiceType;
use Rialto\Time\Web\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Form type for editing PO items and work orders.
 */
class StockProducerType extends DynamicFormType
{
    /** @var AuthorizationCheckerInterface */
    private $auth;

    public function __construct(AuthorizationCheckerInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param FormInterface $form
     * @param StockProducer $poItem
     */
    protected function updateForm(FormInterface $form, $poItem)
    {
        $form = new AuthDecorator($form, $this->auth, $poItem);

        if ($poItem instanceof WorkOrder) {
            $this->addWorkOrderInputs($form, $poItem);
        } elseif ($poItem->isStockItem()) {
            $this->addStockItemInputs($form, $poItem);
        } else {
            $this->addNonStockInputs($form, $poItem);
        }
        $this->addGenericInputs($form, $poItem);
    }

    private function addWorkOrderInputs(AuthDecorator $form, WorkOrder $workOrder)
    {
        $item = $workOrder->getStockItem();
        $this->addVersioningInputs($form, $workOrder);

        if ($workOrder->isCategory(StockCategory::BOARD)) {
            $form->addIfGranted(StockProducerVoter::PARENT, 'parent', EntityType::class, [
                'class' => WorkOrder::class,
                'choices' => $this->getPossibleParents($workOrder),
                'choice_label' => 'fullSku',
                'required' => false,
                'label' => 'Parent/packaging:',
                'placeholder' => '-- none --',
            ]);
        }

        $form->addIfGranted(StockProducerVoter::ALLOCATE, 'openForAllocation', CheckboxType::class, [
            'label' => 'Is open for allocation?',
            'required' => false,
        ]);
        $form->addIfGranted(StockProducerVoter::REWORK, 'rework', CheckboxType::class, [
            'label' => 'Is rework?',
            'required' => false,
        ]);
        $form->addIfGranted(StockProducerVoter::INSTRUCTIONS, 'instructions', TextareaType::class, [
            'required' => false,
            'attr' => ['placeholder' => "Custom instructions for $item"],
        ]);
    }

    private function getPossibleParents(WorkOrder $child)
    {
        $parents = $child->getPurchaseOrder()->getWorkOrders();
        $parents = array_filter($parents, function (WorkOrder $parent) use ($child) {
            return ($parent !== $child)
                && $parent->isCategory(StockCategory::PRODUCT);
        });
        return $parents;
    }

    private function addVersioningInputs(
        AuthDecorator $form,
        StockProducer $poItem)
    {
        $purchData = $poItem->getPurchasingData();
        $stockItem = $poItem->getStockItem();
        $purchVersion = $purchData->getVersion();
        $editVersion = $stockItem->isVersioned()
            && $stockItem->hasSpecifiedVersions()
            && $this->auth->isGranted(StockProducerVoter::VERSION, $poItem)
            && (!$purchVersion->isSpecified());

        $form->addIf($editVersion, 'version', VersionChoiceType::class, [
            'choices' => $stockItem->getActiveVersions(),
            'preferred_choices' => [$stockItem->getAutoBuildVersion()],
            'placeholder' => '-- choose --',
        ]);

        $editCmz = $stockItem->isCustomizable()
            && $this->auth->isGranted(StockProducerVoter::CUSTOMIZATION, $poItem);
        $form->addIf($editCmz, 'customization', EntityType::class, [
            'class' => Customization::class,
            'query_builder' => function (CustomizationRepository $repo) use ($stockItem) {
                return $repo->createBuilder()
                    ->bySku($stockItem)
                    ->getQueryBuilder();
            },
            'required' => false,
            'placeholder' => 'No customization',
        ]);
    }

    /** For purchased items, not manufactured */
    private function addStockItemInputs(AuthDecorator $form,
                                        StockProducer $poItem)
    {
        $stockItem = $poItem->getStockItem();
        $supplier = $poItem->getSupplier();
        $form->addIfGranted(StockProducerVoter::PURCH_DATA, 'purchasingData', EntityType::class, [
            'class' => PurchasingData::class,
            'query_builder' => function (PurchasingDataRepository $repo) use ($stockItem, $supplier) {
                return $repo->createBuilder()
                    ->isActive()
                    ->byItem($stockItem)
                    ->bySupplier($supplier)
                    ->getQueryBuilder();
            },
            'choice_label' => 'label',
        ]);

        $this->addVersioningInputs($form, $poItem);

        $form->addIf(!$poItem->getDescription(), 'description', TextType::class, [
            'required' => false,
            'attr' => ['placeholder' => 'description...'],
        ]);
    }


    private function addNonStockInputs(AuthDecorator $form, StockProducer $producer)
    {
        $form->add('description', TextType::class, [
            'required' => false,
            'attr' => ['placeholder' => 'description...'],
        ]);
        $form->add('versionReference', TextType::class, [
            'required' => false,
            'attr' => ['placeholder' => 'version...'],
        ]);
        $form->addIf(!$producer->isInProcess(), 'GLAccount', EntityType::class, [
            'class' => GLAccount::class,
            'query_builder' => function (GLAccountRepository $repo) {
                return $repo->queryValidAccountsForPurchaseOrderItem();
            },
        ]);
    }

    private function addGenericInputs(AuthDecorator $form, StockProducer $poItem)
    {
        $form->addIfGranted(StockProducerVoter::COST, 'unitCost', MoneyType::class, [
            'currency' => Currency::USD,
            'scale' => StockProducer::UNIT_COST_PRECISION,
            'label' => $poItem->isWorkOrder() ?
                'Labour cost per unit' : 'Unit cost',
        ]);
        $form->addIfGranted(StockProducerVoter::QTY_ORDERED, 'qtyOrdered', NumberType::class);
        $form->add('requestedDate', DateType::class, [
            'required' => false,
            'attr' => ['class' => 'datepicker'],
        ]);
        $form->add('commitmentDate', DateType::class, [
            'required' => false,
            'attr' => ['class' => 'datepicker'],
        ]);
        $form->addIfGranted(StockProducerVoter::FLAGS, 'flags', ChoiceType::class, [
            'choices' => $poItem->getFlagOptions(),
            'expanded' => true,
            'multiple' => true,
            'required' => false,
        ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockProducer::class,
            'error_mapping' => [
                'qtyRemaining' => 'qtyOrdered',
                'qtyUninvoiced' => 'qtyOrdered',
            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'StockProducer';
    }
}

/**
 * Adds elements to the form only if certain privileges are granted.
 */
class AuthDecorator extends ConditionDecorator
{
    private $auth;
    private $producer;

    public function __construct(FormInterface $form,
                                AuthorizationCheckerInterface $auth,
                                StockProducer $producer)
    {
        parent::__construct($form);
        $this->auth = $auth;
        $this->producer = $producer;
    }

    public function addIfGranted($attribute, $child, $type, array $options = [])
    {
        $granted = $this->auth->isGranted($attribute, $this->producer);
        $this->addIf($granted, $child, $type, $options);
    }

}
