<?php

namespace Rialto\Stock;

use Rialto\Database\Orm\DbManager;
use Rialto\Email\MailerInterface;
use Rialto\Security\User\User;
use Rialto\Stock\Bin\BinSplitEvent;
use Rialto\Stock\Bin\Email\BinSplitEmail;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Transfer\Email\TransferEmail;
use Rialto\Stock\Transfer\Web\TransferReceipt;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Listens for events which should result in an email being sent.
 */
class EmailEventListener implements EventSubscriberInterface
{
    /** @var MailerInterface */
    private $mailer;

    /** @var TokenStorageInterface */
    private $tokens;

    /** @var DbManager */
    private $dbm;

    /**
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     */
    public static function getSubscribedEvents()
    {
        return [
            StockEvents::TRANSFER_RECEIPT => 'emailTransferShortage',
            StockEvents::BIN_SPLIT => 'requestBinSplit',
        ];
    }

    public function __construct(
        MailerInterface $mailer,
        TokenStorageInterface $tokens,
        DbManager $dbm)
    {
        $this->mailer = $mailer;
        $this->tokens = $tokens;
        $this->dbm = $dbm;
    }

    public function emailTransferShortage(TransferReceipt $event)
    {
        $transfer = $event->getTransfer();
        if ($transfer->hasMissingItems()) {
            $email = new TransferEmail($transfer, $this->dbm);
            $email->addCc($this->getCurrentUser());
            $this->mailer->send($email);
        }
    }

    /** @return User|null */
    private function getCurrentUser()
    {
        $token = $this->tokens->getToken();
        return $token ? $token->getUser() : null;
    }

    /**
     * If someone who is not physically located at the warehouse splits a bin,
     * the warehouse folks need to be notified so they can actually do the
     * physical split. Otherwise the database will not match reality.
     */
    public function requestBinSplit(BinSplitEvent $event)
    {
        $originalBin = $event->getOriginal();
        if (! $this->isSplitEmailRequired($originalBin)) {
            return;
        }
        $user = $this->getCurrentUser();
        $newBin = $event->getNew();
        $email = new BinSplitEmail($originalBin, $newBin, $user);
        if ($email->loadRecipients($this->dbm)) {
            $this->mailer->send($email);
        }
    }

    /**
     * We need to send the email if the person requesting the split is not
     * in the same physical location as the bin itself.
     */
    private function isSplitEmailRequired(StockBin $bin)
    {
        $user = $this->getCurrentUser();
        $userLoc = $user ? $user->getDefaultLocation() : null;
        if ($bin->isInTransit()) {
            return false;
        }
        $binLoc = $bin->getFacility();
        return ! $binLoc->isColocatedWith($userLoc);
    }
}
