<?php

namespace Rialto\Stock\Returns\Problem;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Security\User\Orm\UserRepository;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Transfer\Transfer;

/**
 * Notifies warehouse staff that problems with returned items have been
 * solved, and they can now check those items back into stock.
 */
class CheckInEmail extends Email
{
    /** @var Facility */
    private $destination;

    public function __construct(User $user, Transfer $transfer)
    {
        $origin = $transfer->getOrigin();
        $this->destination = $transfer->getDestination();
        $this->template = "stock/returns/checkInEmail.html.twig";
        $this->params = [
            'user' => $user,
            'transfer' => $transfer,
            'origin' => $origin,
        ];

        $this->setFrom(EmailPersonality::BobErbauer());
        $this->subject = "You can now shelve returned items from {$origin}";
    }

    public function loadRecipients(ObjectManager $om)
    {
        /** @var $repo UserRepository */
        $repo = $om->getRepository(User::class);
        $this->setTo($repo->findByLocation($this->destination));
    }

}
