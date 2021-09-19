<?php

namespace Rialto\Stock\Bin\Email;

use Rialto\Database\Orm\DbManager;
use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Security\Role\Role;
use Rialto\Security\User\Orm\UserRepository;
use Rialto\Security\User\User;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;

class BinSplitEmail extends Email
{
    /**
     * @var Facility The location of the bin that needs to be split.
     */
    private $facility;

    public function __construct(StockBin $original,
                                StockBin $new,
                                User $requestedBy)
    {
        $this->facility = $original->getFacility();

        $this->subject = sprintf("Please split %s of %s at %s",
            $original,
            $original->getSku(),
            $this->facility);
        $this->template = 'stock/bin/split-email.html.twig';
        $this->params = [
            'originalBin' => $original,
            'newBin' => $new,
            'user' => $requestedBy,
        ];

        $this->setFrom(EmailPersonality::BobErbauer());

    }

    /**
     * @return bool True if there are any recipients.
     */
    public function loadRecipients(DbManager $dbm)
    {
        /** @var $repo UserRepository */
        $repo = $dbm->getRepository(User::class);
        $recipients = $repo->findMailableByRole(Role::WAREHOUSE);
        $hasRecipients = false;

        foreach ($recipients as $user) {
            if ($this->facility->isColocatedWith($user->getDefaultLocation())) {
                $this->addTo($user);
                $hasRecipients = true;
            }
        }
        return $hasRecipients;
    }
}
