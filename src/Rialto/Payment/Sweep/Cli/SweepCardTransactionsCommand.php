<?php

namespace Rialto\Payment\Sweep\Cli;

use Gumstix\Time\DateRange;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\MailerInterface;
use Rialto\Payment\Sweep\CardTransactionSweep;
use Rialto\Payment\Sweep\Email\SweepEmail;
use Rialto\Payment\Sweep\Orm\SweepGateway;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sweeps unsettled card transactions into the bank account.
 */
class SweepCardTransactionsCommand extends Command
{
    const NAME = 'payment:sweep-card-trans';

    /** @var SweepGateway */
    private $gateway;

    /** @var CardTransactionSweep */
    private $sweeper;

    /** @var MailerInterface */
    private $mailer;

    public function __construct(SweepGateway $gateway,
                                CardTransactionSweep $sweeper,
                                MailerInterface $mailer)
    {
        parent::__construct(self::NAME);
        $this->gateway = $gateway;
        $this->sweeper = $sweeper;
        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this->setAliases(['rialto:sweep-card-trans'])
            ->setDescription('Sweep credit card transactions into the bank account')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, "Don't make any actual changes");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('dry-run')) {
            $output->writeln("<info>Dry run mode enabled.</info>");
        }

        $dates = DateRange::create()
            ->withStart('-3 months')
            ->withEnd('now');

        $transactions = $this->gateway->findUnsweptTransactions($dates);
        $output->writeln(sprintf('Found %s transactions to sweep.',
            number_format(count($transactions))));

        $groups = $this->gateway->getPaymentMethodGroups();

        if (!$input->getOption('dry-run')) {
            $this->gateway->transactional(function () use ($transactions) {
                $this->sweeper->sweep($transactions);
            });
        }

        $email = new SweepEmail($transactions, $groups);
        $email->setFrom(EmailPersonality::BobErbauer());
        $this->gateway->loadRecipients($email);

        $this->mailer->send($email);
    }

}
