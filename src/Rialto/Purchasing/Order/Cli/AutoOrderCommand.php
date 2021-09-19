<?php

namespace Rialto\Purchasing\Order\Cli;

use Rialto\Database\Orm\DbManager;
use Rialto\Email\MailerInterface;
use Rialto\Manufacturing\WorkOrder\Cli\AutoBuildCommand;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\Email\AutoOrderEmail;
use Rialto\Purchasing\Order\PurchaseInitiator;
use Rialto\Purchasing\Order\PurchaseOrderFactory;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\StockNeedMapper;
use Rialto\Stock\Facility\StockNeed;
use SplObjectStorage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Automatically order new purchased parts.
 *
 * @see AutoBuildCommand, which auto-creates work orders for manufactured items.
 */
class AutoOrderCommand extends ContainerAwareCommand implements PurchaseInitiator
{
    const INITIATOR_CODE = 'AutoOrder';

    /** @var DbManager */
    private $dbm;

    /** @var Facility */
    private $stockLocation;

    /** @var ValidatorInterface */
    private $validator;

    /** @var Table */
    private $outputTable;

    /** @var PurchaseOrderFactory */
    private $poFactory;

    private $errors = [];

    public function getInitiatorCode()
    {
        return self::INITIATOR_CODE;
    }

    protected function configure()
    {
        $this->setName('rialto:auto-order')
            ->setDescription('Auto-order stock')
            ->addOption('dry-run', null, InputOption::VALUE_NONE,
                'Show what needs to be ordered without ordering anything.');
    }

    /**
     * @param Output $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $this->dbm = $container->get(DbManager::class);
        $this->stockLocation = Facility::fetchHeadquarters($this->dbm);
        $this->poFactory = $container->get(PurchaseOrderFactory::class);
        $this->validator = $container->get(ValidatorInterface::class);

        $this->outputTable = new Table($output);
        $this->outputTable->setHeaders(['Item', 'Qty to order', 'Errors']);

        $mapper = new StockNeedMapper($this->dbm);
        $allNeeds = $mapper->fetchNeedsToPurchase();
        $needsBySupplier = $this->indexNeedsBySupplier($allNeeds);
        if ($input->getOption('dry-run')) {
            $output->writeln("Dry-run mode enabled.");
            $purchasedNeeds = $allNeeds;
        } else {
            $purchasedNeeds = $this->createOrdersAsNeeded($needsBySupplier);
        }

        if ($output->isVerbose()) {
            $this->outputTable->render();
        }

        $email = new AutoOrderEmail($purchasedNeeds, $this->errors);
        $email->setDryRun($input->getOption('dry-run'));
        if ($email->shouldBeSent()) {
            $email->loadSubscribers($this->dbm);
            $mailer = $container->get(MailerInterface::class);
            $mailer->send($email);
        }
    }

    private function indexNeedsBySupplier(array $needs)
    {
        $needsBySupplier = new SplObjectStorage();
        $purchRepo = $this->dbm->getRepository(PurchasingData::class);

        foreach ($needs as $stockNeed) {
            /* @var $stockNeed StockNeed */
            $stockNeed->loadPurchasingData($purchRepo);
            $errors = $this->validate($stockNeed);
            if ((count($errors) == 0) && ($stockNeed->getQtyToOrder() > 0)) {
                $supplier = $stockNeed->getSupplier();
                $needs = isset($needsBySupplier[$supplier]) ?
                    $needsBySupplier[$supplier] : [];
                $needs[] = $stockNeed;
                $needsBySupplier[$supplier] = $needs;
            }
        }

        return $needsBySupplier;
    }

    private function validate(StockNeed $need)
    {
        static $groups = ['Default', 'purchasing'];
        $violations = $this->validator->validate($need, null, $groups);
        foreach ($violations as $error) {
            $this->errors[$error->getMessage()][] = $need->getStockItem();
        }
        return $violations;
    }

    private function createOrdersAsNeeded(SplObjectStorage $needsBySupplier)
    {
        $currentPO = null;
        $purchased = [];
        foreach ($needsBySupplier as $currentSupplier) {
            $needs = $needsBySupplier[$currentSupplier];
            assertion($currentSupplier instanceof Supplier);
            foreach ($needs as $stockNeed) {
                /* @var $stockNeed StockNeed */
                if (! $currentPO) {
                    $currentPO = $this->poFactory->create($this);
                    $currentPO->setSupplier($currentSupplier);
                    $currentPO->setDeliveryLocation($this->stockLocation);
                    $this->dbm->persist($currentPO);
                }
                $purchData = $stockNeed->getPurchasingData();
                $poItem = $currentPO->addItemFromPurchasingData($purchData);
                $poItem->setQtyOrdered($stockNeed->getQtyToOrder());
                $poItem->resetUnitCost();
                $this->dbm->persist($poItem);

                $stockNeed->setPurchaseOrderItem($poItem);
                $purchased[] = $stockNeed;

                if ($currentPO->isFull()) {
                    $currentPO = null;
                }
            }
            $currentPO = null;
            $currentSupplier = null;
        }
        $this->dbm->flush();
        return $purchased;
    }
}

