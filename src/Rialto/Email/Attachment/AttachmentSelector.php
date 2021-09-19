<?php

namespace Rialto\Email\Attachment;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Allows the user to select from a list of possible attachments.
 *
 * Use this as a helper class inside your subclass of Email.
 */
class AttachmentSelector
{
    /**
     * Possible attachments that the user can select to attach or not.
     *
     * @var Attachment[]
     */
    private $available = [];

    /**
     * The names of the attachments that the user has actually selected to
     * attach.
     *
     * @var string[]
     */
    private $selected = [];

    /**
     * Adds an attachment to the list of attachments available to the user.
     */
    public function add(Attachment $attachment, $selectByDefault = true)
    {
        $filename = $attachment->getFilename();
        $this->available[$filename] = $attachment;
        if ($attachment->exists() && $selectByDefault) {
            $this->selected[] = $filename;
        }
    }

    public function getAvailable()
    {
        return $this->available;
    }

    public function hasAvailableAttachments()
    {
        return count($this->available) > 0;
    }

    public function isMissingAttachments()
    {
        foreach ($this->available as $name => $attachment) {
            if (! $attachment->exists()) {
                return true;
            }
        }
        return false;
    }

    /**
     * The names of the files that the user has selected.
     *
     * This method is intended to be the binding point used by the form type.
     *
     * @return string[]
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * The names of the files that the user has selected.
     *
     * This method is intended to be the binding point used by the form type.
     *
     * @param string[] $attachments
     */
    public function setSelected(array $attachments)
    {
        $this->selected = $attachments;
    }

    /**
     * @Assert\Callback
     */
    public function validateAttachments(ExecutionContextInterface $context)
    {
        foreach ($this->selected as $filename) {
            if (! $this->available[$filename]->exists()) {
                $context->buildViolation("File '$filename' does not exist.")
                    ->atPath('selected')
                    ->addViolation();
            }
        }
    }

    /**
     * The actual attachments that the user has selected.
     *
     * @return Attachment[]
     */
    public function getSelectedAttachments()
    {
        $attachments = [];
        foreach ($this->selected as $filename) {
            $attachments[$filename] = $this->available[$filename];
        }
        return $attachments;
    }
}
