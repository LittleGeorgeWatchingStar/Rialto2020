<?php

namespace Rialto\Purchasing\Recurring\Cli;

use DateTime;
use Exception;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Company\Company;
use Rialto\Database\Orm\DbManager;
use Rialto\Email\MailerInterface;
use Rialto\Exception\InvalidDataException;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Recurring\Email\AutoInvoiceEmail;
use Rialto\Purchasing\Recurring\RecurringInvoice;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Automatically creates supplier invoices, according to the RecurringInvoice
 * records.
 */
class AutoInvoiceCommand extends Command
{
    const NAME = 'purchasing:auto-invoice';

    /** @var DbManager */
    private $dbm;

    /** @var MailerInterface */
    private $mailer;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(DbManager $dbm,
                                MailerInterface $mailer,
                                ValidatorInterface $validator)
    {
        parent::__construct(self::NAME);
        $this->dbm = $dbm;
        $this->mailer = $mailer;
        $this->validator = $validator;
    }

    protected function configure()
    {
        $this->setAliases(['rialto:auto-invoice'])
            ->setDescription('Automatically creates supplier invoices, according to the RecurringInvoice records.')
            ->addArgument('date', InputArgument::OPTIONAL, 'The date for which to add invoices', 'now');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new DateTime($input->getArgument('date'));
        $invoicesToAdd = $this->dbm->getRepository(RecurringInvoice::class)
            ->findByDate($date);

        $email = new AutoInvoiceEmail();

        $this->dbm->beginTransaction();
        try {
            foreach ($invoicesToAdd as $rInvoice) {
                /* @var $rInvoice RecurringInvoice */
                $output->write("Processing $rInvoice...");
                $suppTrans = $this->findExisting($rInvoice, $date);
                if ($suppTrans) {
                    $output->writeln(" already entered.");
                    $email->addAlreadyEntered($suppTrans);
                } elseif ($rInvoice->getSubtotalAmount() > 0) {
                    $suppTrans = $this->createInvoice($rInvoice, $date);
                    $output->writeln(" entered successfully.");
                    $email->addNewInvoice($suppTrans);
                } else {
                    $output->writeln(" INVALID SUBTOTAL AMOUNT.");
                    $email->addInvalid($rInvoice);
                }
            }
            $this->dbm->flush();

            $overdue = $this->dbm->getRepository(SupplierTransaction::class)
                ->findOverdueInvoices($date);
            $email->setOverdueInvoices($overdue);

            $email->loadSubscribers($this->dbm);
            if ($email->getBody()) {
                if ($email->hasRecipients()) {
                    $this->mailer->send($email);
                } else {
                    $output->writeln("Warning: no email subscribers found.");
                }
            }
            $this->dbm->flushAndCommit();
        } catch (Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
    }

    /** @return SupplierTransaction|null */
    private function findExisting(RecurringInvoice $rInvoice, DateTime $date)
    {
        return $this->dbm->getRepository(SupplierTransaction::class)
            ->findByRecurringInvoiceAndDate($rInvoice, $date);
    }

    /** @return SupplierTransaction */
    private function createInvoice(RecurringInvoice $rInvoice, DateTime $date)
    {
        $invoice = $rInvoice->createInvoice($date);
        $this->assertInvoiceIsValid($invoice);
        $this->dbm->persist($invoice);

        $sysType = SystemType::fetchPurchaseInvoice($this->dbm);
        $company = Company::findDefault($this->dbm);
        $invoice->prepare();
        $suppTrans = $invoice->approve($sysType, $company);
        $this->dbm->persist($suppTrans);

        return $suppTrans;
    }

    private function assertInvoiceIsValid(SupplierInvoice $invoice)
    {
        $groups = ['Default', 'approval'];
        $errors = $this->validator->validate($invoice, null, $groups);
        if (count($errors) > 0) {
            throw new InvalidDataException(sprintf(
                "Recurring invoice for %s is not valid: %s",
                $invoice->getSupplier(),
                $errors));
        }
    }
}
