<?php

namespace Rialto\Email;

/**
 * An email sent by a cron job.
 */
class CronJobEmail extends Email
{
    protected $contentType = 'text/html';

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }
}
