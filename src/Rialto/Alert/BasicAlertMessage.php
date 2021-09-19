<?php

namespace Rialto\Alert;

use Psr\Log\LogLevel;

/**
 * @author Ian Phillips <ian@gumstix.com>
 */
class BasicAlertMessage
implements AlertMessage
{
    /** @var string */
    private $level;

    /** @var string */
    private $message;

    /** @var AlertResolution */
    private $resolution = null;

    /**
     * Static factory method.
     *
     * @return BasicAlertMessage
     */
    public static function createError($string)
    {
        return new self($string, LogLevel::ERROR);
    }

    /**
     * Static factory method.
     *
     * @return BasicAlertMessage
     */
    public static function createWarning($string)
    {
        return new self($string, LogLevel::WARNING);
    }

    /**
     * Static factory method.
     *
     * @return BasicAlertMessage
     */
    public static function createNotice($string)
    {
        return new self($string, LogLevel::NOTICE);
    }

    protected function __construct($msg, $level)
    {
        $this->message = $msg;
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getResolution()
    {
        return $this->resolution;
    }

    public function setResolution(AlertResolution $res)
    {
        $this->resolution = $res;
        return $this;
    }

    public function isError()
    {
        return in_array($this->level, [
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::EMERGENCY,
        ]);
    }

    public function isNotice()
    {
        return $this->level == LogLevel::NOTICE;
    }

    public function __toString()
    {
        return $this->message;
    }
}
