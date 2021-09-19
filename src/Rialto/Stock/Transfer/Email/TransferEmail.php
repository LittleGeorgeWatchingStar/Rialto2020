<?php

namespace Rialto\Stock\Transfer\Email;

use Rialto\Database\Orm\DbManager;
use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Security\Role\Role;
use Rialto\Security\User\User;
use Rialto\Stock\Transfer\Transfer;

/**
 * Email sent when a location transfer is received short.
 *
 * To receive a transfer "short" means that the recipient did not get
 * all of the parts we thought we sent.
 */
class TransferEmail extends Email
{
    public function __construct(Transfer $transfer, DbManager $dbm)
    {
        $shortages = $transfer->getMissingItems();
        if ( empty($shortages) ) throw new \InvalidArgumentException(sprintf(
            'location transfer %s has no shortages', $transfer->getId()
        ));

        $this->subject = sprintf('Location transfer %s was received short',
            $transfer->getId()
        );
        $this->template = 'stock/transfer/shortage-email.html.twig';
        $this->params = [
            'transfer' => $transfer,
            'shortages' => $shortages,
        ];

        $this->setFrom(EmailPersonality::BobErbauer());
        $recipients = $this->getRecipients($dbm, Role::WAREHOUSE);
        foreach ( $recipients as $user ) {
            if (! $user->getEmail() ) continue;
            $this->addTo($user);
        }
    }

    private function getRecipients(DbManager $dbm, $role)
    {
        $mapper = $dbm->getRepository(User::class);
        return $mapper->findByRole($role);
    }
}
