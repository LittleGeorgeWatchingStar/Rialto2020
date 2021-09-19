<?php

namespace Rialto\Manufacturing\WorkOrder;

use Rialto\Email\Email;
use Rialto\Security\User\User;

/**
 * Email requesting the build of an in-house work order.
 */
class WorkOrderEmail extends Email
{
    /** @var WorkOrder */
    private $wo;

    public function __construct(WorkOrder $wo, User $from)
    {
        $this->wo = $wo;
        $this->setFrom($from);
        $this->subject = 'Please assemble work order '. $wo->getId();
        $this->template = 'manufacturing/order/email.html.twig';
        $this->params = [
            'wo' => $this->wo,
            'item' => $this->wo->getStockItem(),
            'filename' => $this->getInstructionsFilename(),
            'from' => $this->getFrom(),
        ];
    }

    private function getInstructionsFilename()
    {
        return sprintf('Build Instructions #%s.pdf', $this->wo->getId());
    }

    public function attachBuildInstructions(WorkOrderPdfGenerator $generator)
    {
        $filename = $this->getInstructionsFilename();
        $pdfData = $generator->getPdf($this->wo);
        $this->addAttachmentFromFileData($pdfData, 'application/pdf', $filename);
    }
}
