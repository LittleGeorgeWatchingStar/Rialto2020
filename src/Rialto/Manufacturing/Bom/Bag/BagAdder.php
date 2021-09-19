<?php

namespace Rialto\Manufacturing\Bom\Bag;


use Rialto\Email\MailerInterface;
use Rialto\Stock\Item\Version\ItemVersion;

/**
 * Adds a bag to the BOM of a board, if a bag is needed and one of the
 * correct size can be found.
 */
class BagAdder
{
    /** @var BagFinder */
    private $finder;

    /** @var MailerInterface */
    private $mailer;

    public function __construct(
        BagFinder $finder,
        MailerInterface $mailer)
    {
        $this->finder = $finder;
        $this->mailer = $mailer;
    }

    public function addBagIfNeeded(ItemVersion $parent)
    {
        if (!$this->finder->isBagNeeded($parent)) {
            return;
        }
        if (!$parent->hasDimensions()) {
            $this->notify($parent);
            return;
        }
        $bag = $this->finder->findMatchingBag($parent);
        if ($bag === null) {
            $this->notify($parent);
            return;
        }
        $this->finder->addBagToBom($parent, $bag);
    }

    private function notify(ItemVersion $board)
    {
        $email = new NoMatchingBagEmail($board, $this->finder);
        $this->mailer->loadSubscribers($email);
        if ($email->hasRecipients()) {
            $this->mailer->send($email);
        }
    }
}
