<?php

namespace Rialto\Manufacturing\Customization;


use Rialto\Email\Email;
use Rialto\Email\Mailable\EmailPersonality;
use Rialto\Email\MailerInterface;
use Rialto\Manufacturing\WorkOrder\WorkOrder;

class CustomizationErrorHandler
{
    /** @var MailerInterface */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handleErrors(WorkOrder $workOrder, array $errors)
    {
        if (count($errors) == 0) {
            return;
        }

        $mail = new Email();
        $mail->setFrom(EmailPersonality::BobErbauer());
        $mail->addTo($workOrder->getOwner());
        $mail->setSubject("Errors customizing $workOrder");
        $mail->setTemplate("manufacturing/work-order/customization/error-email.html.twig", [
            'errors' => $errors,
            'workOrder' => $workOrder,
        ]);

        $this->mailer->send($mail);
    }
}
