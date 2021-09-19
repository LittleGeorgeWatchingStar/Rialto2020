<?php

namespace Rialto\Purchasing\Invoice\Reader\Email;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Rialto\Database\Orm\DbManager;
use Rialto\Purchasing\Invoice\Orm\SupplierInvoicePatternRepository;
use Rialto\Purchasing\Invoice\Orm\SupplierInvoiceRepository;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Invoice\SupplierInvoicePattern;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Automatically imports supplier invoices from the invoice mailbox.
 *
 * This requires no human interaction, but only does those suppliers that
 * are marked as having auto-importable invoices.
 */
class AutoImporter
{
    /**
     * @var DbManager
     */
    private $dbm;

    /**
     * @var SupplierInvoicePatternRepository
     */
    private $patternRepo;

    /**
     * @var SupplierInvoiceRepository
     */
    private $invoiceRepo;

    /**
     * @var SupplierMailbox
     */
    private $mailbox;

    /**
     * @var AttachmentParser
     */
    private $parser;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Invoice patterns for suppliers whose invoices can be auto-imported.
     * @var SupplierInvoicePattern[]
     */
    private $patterns = null;

    public function __construct(DbManager $dbm,
                                SupplierMailbox $mailbox,
                                AttachmentParser $parser,
                                ValidatorInterface $validator)
    {
        $this->dbm = $dbm;
        $this->patternRepo = $dbm->getRepository(SupplierInvoicePattern::class);
        $this->invoiceRepo = $dbm->getRepository(SupplierInvoice::class);
        $this->mailbox = $mailbox;
        $this->parser = $parser;
        $this->validator = $validator;
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return SupplierEmail[]
     */
    public function getEmails(): array
    {
        return $this->mailbox->getAll();
    }

    /**
     * @return SupplierInvoice[] New invoices that were entered successfully.
     */
    public function importEmail(SupplierEmail $email)
    {
        $pattern = $this->findMatchingPattern($email);
        if (!$pattern) {
            $this->logger->debug("No matching pattern for " . $email->getSubject());
            return [];
        }

        $this->logger->info(sprintf('Message "%s" matches %s',
            $email->getSubject(),
            $pattern->getSupplier()));
        $email->setPattern($pattern);

        $this->parser->findInvoices($email);

        $invoices = $this->handleInvoices($email);
        if (count($invoices) > 0) {
            $this->moveToCompleted($email);
        }
        return $invoices;
    }

    private function loadPatterns()
    {
        if (null === $this->patterns) {
            $this->patterns = $this->patternRepo->findAutoImportable();
        }
        return $this->patterns;
    }

    /** @return SupplierInvoicePattern|null */
    private function findMatchingPattern(SupplierEmail $email)
    {
        $patterns = $this->loadPatterns();
        return $this->patternRepo->findMatching($email, $patterns);
    }

    /**
     * @return SupplierInvoice[]
     */
    private function handleInvoices(SupplierEmail $email): array
    {
        $enteredOk = [];
        $invoices = $email->getInvoices();
        $this->logger->debug(sprintf('%s contains %d invoices.',
            $email->getSubject(),
            count($invoices)));
        foreach ($invoices as $invoice) {
            if ($this->findAlreadyEntered($invoice)) {
                $this->logger->info(ucfirst("$invoice already entered."));
                continue;
            }
            $success = $this->handleInvoice($invoice);
            if ($success) {
                $enteredOk[] = $invoice;
            }
        }
        return $enteredOk;
    }

    /**
     * @return SupplierInvoice|null
     */
    private function findAlreadyEntered(SupplierInvoice $invoice)
    {
        return $this->invoiceRepo->findBySupplierReference(
            $invoice->getSupplier(),
            $invoice->getSupplierReference());
    }

    /**
     * @return bool True if the invoice is handled successfully; false otherwise.
     */
    private function handleInvoice(SupplierInvoice $invoice): bool
    {
        $invoice->fixUnitCosts();

        $errors = $this->validator->validate($invoice);
        if (count($errors)) {
            $this->logger->error($this->formatErrors($errors));
            return false;
        }

        $invoice->prepare();
        $this->dbm->persist($invoice);
        $this->dbm->flush();

        $this->logger->notice("Entered $invoice.");
        return true;
    }

    private function formatErrors(ConstraintViolationListInterface $errors): string
    {
        $strings = [];
        foreach ($errors as $error) {
            /** @var $error ConstraintViolationInterface */
            $strings[] = $error->getMessage();
        }
        return join(' ', $strings);
    }

    private function moveToCompleted(SupplierEmail $email)
    {
        $archiveFolder = SupplierMailbox::FOLDER_ARCHIVE;
        $this->mailbox->moveMessage($email->getMessageId(), $archiveFolder);
        $this->logger->info(sprintf("Moved message \"%s\" to %s.",
            $email->getSubject(),
            $archiveFolder));
    }
}
