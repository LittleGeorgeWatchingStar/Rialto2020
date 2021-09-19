<?php

namespace Rialto\Manufacturing\BuildFiles\Email;

use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Manufacturing\BuildFiles\BuildFiles;
use Rialto\Security\User\User;

/**
 * This is the email that is sent to notify a PO owner that someone
 * has uploaded build/engineering files needed for his PO.
 */
class BuildFilesUploadNotification extends Email
{
    public function __construct(
        User $uploader, BuildFiles $files, User $owner, array $orders)
    {
        $this->template = "manufacturing/buildFiles/uploadNotification.html.twig";
        $this->subject = sprintf('%s has uploaded build files for %s',
            $uploader->getName(),
            $files->getFullSku());

        $this->setFrom(EmailPersonality::BobErbauer());
        $this->addTo($owner);

        $this->params = [
            'uploader' => $uploader,
            'files' => $files,
            'owner' => $owner,
            'orders' => $orders,
        ];
    }
}
