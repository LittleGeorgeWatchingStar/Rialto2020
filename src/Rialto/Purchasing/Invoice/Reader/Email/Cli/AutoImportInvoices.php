<?php

namespace Rialto\Purchasing\Invoice\Reader\Email\Cli;

use Exception;
use Rialto\Email\Cli\CommandOutputEmail;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\MailerInterface;
use Rialto\Purchasing\Invoice\Reader\Email\AutoImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Mail\Exception\RuntimeException;

/**
 * Scans the email inbox for supplier invoices that can be automatically
 * imported and imports them.
 */
class AutoImportInvoices extends Command
{
    const NAME = 'purchasing:auto-import-invoices';

    /**
     * @var AutoImporter
     */
    private $importer;

    /**
     * @var MailerInterface
     */
    private $mailer;


    public function __construct(AutoImporter $importer, MailerInterface $mailer)
    {
        parent::__construct();
        $this->importer = $importer;
        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Automatically import invoices from supplier emails');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output = new CommandOutputEmail($output, self::NAME);
        $logger = new ConsoleLogger($output);
        $this->importer->setLogger($logger);

        try {
            $emails = $this->importer->getEmails();
        } catch (RuntimeException $ex) {
            $logger->error($ex->getMessage());
            return 1;
        }
        $logger->debug(sprintf("Found %d emails.", count($emails)));
        foreach ($emails as $email) {
            $logger->debug(sprintf('Reading "%s"', $email->getSubject()));
            try {
                $this->importer->importEmail($email);
            } catch (Exception $ex) {
                $logger->error($ex->getMessage());
            }
        }

        $this->mailer->loadSubscribers($output);
        if ($output->hasRecipients() && $output->hasContent()) {
            $output->setFrom(EmailPersonality::BobErbauer());
            $output->setSubject("Auto-imported supplier invoices");
            $this->mailer->send($output);
        }
        return 0;
    }
}
