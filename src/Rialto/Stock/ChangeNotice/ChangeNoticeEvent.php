<?php

namespace Rialto\Stock\ChangeNotice;

use Symfony\Component\EventDispatcher\Event;

/**
 * Fires when a change notice is created.
 */
class ChangeNoticeEvent extends Event
{
    /** @var ChangeNotice */
    private $notice;

    public function __construct(ChangeNotice $notice)
    {
        $this->notice = $notice;
    }

    /** @return ChangeNotice */
    public function getNotice()
    {
        return $this->notice;
    }
}