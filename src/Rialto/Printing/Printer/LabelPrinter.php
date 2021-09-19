<?php

namespace Rialto\Printing\Printer;

use Rialto\Filetype\Postscript\PostscriptLabel;
use Rialto\Printing\Job\PrintJob;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * A printer for printing PostscriptLabel documents.
 *
 * @see PostscriptLabel
 */
class LabelPrinter extends Printer
{
    /**
     * @var string
     */
    const LOCKFILE_NAME = '/tmp/gumstix_label_printer_lockfile';

    const MAX_NUM_LABELS = 150;

    protected function getLockfileName()
    {
        return self::LOCKFILE_NAME;
    }

    public function printJob(PrintJob $job)
    {
        $ps = $this->getRawData($job);
        for ($i = 0; $i < $job->getNumCopies(); $i++) {
            $this->printString($ps);
        }
    }

    public function printLabel(PostscriptLabel $label, $qty = 1)
    {
        $ps = $label->render();
        for ($i = 0; $i < $qty; $i++) {
            $this->printString($ps);
        }
    }

    public function printString(string $data)
    {
        $this->open();
        $this->write($data);
        $this->close();
    }

    public static function validateQuantity($qty, ExecutionContextInterface $context)
    {
        if (!is_numeric($qty)) {
            $context->addViolation("'$qty is not a number.");
        } elseif ($qty <= 0) {
            $context->addViolation("Quantity to print must be positive.");
        } elseif ($qty > self::MAX_NUM_LABELS) {
            $context->addViolation(sprintf('Cannot print more than %s labels at a time.',
                self::MAX_NUM_LABELS
            ));
        }
    }
}
