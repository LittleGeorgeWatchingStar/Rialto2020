<?php

namespace Rialto\Manufacturing\Kit\Reminder;

use Psr\Log\LoggerInterface;
use Rialto\Email\MailerInterface;
use Rialto\Security\User\Orm\UserRepository;
use Rialto\Stock\Transfer\Orm\TransferRepository;
use Rialto\Stock\Transfer\Transfer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Send an email to remind the CM to check in a transfer
 */
class SendEmailCommand extends Command
{
    const NAME = 'transfer:reminder';

    /**
     * @var TransferRepository
     */
    private $transfers;

    /**
     * @var UserRepository
     */
    private $users;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(TransferRepository $transfers,
                                UserRepository $users,
                                MailerInterface $mailer,
                                LoggerInterface $logger)
    {
        parent::__construct(self::NAME);
        $this->transfers = $transfers;
        $this->users = $users;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this->setDescription("Send an email to remind the CM to check in a transfer");
        $this->addArgument('transferId', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $transferId = $input->getArgument('transferId');
        /** @var Transfer $transfer */
        $transfer = $this->transfers->find($transferId);
        if (!$transfer) {
            $this->logger->error("No such transfer $transferId.");
            return 1;
        }
        if ($transfer->isReceived()) {
            $this->logger->info("$transfer is already received.");
            return 0;
        }

        $email = new ReminderEmail($transfer);
        if (!$email->hasRecipients()) {
            $this->logger->notice("$transfer has no kit contacts");
            return 1;
        }
        $email->loadSubscribersFromRepo($this->users);
        $this->mailer->send($email);

        return 0;
    }
}
