<?php

namespace Rialto\Alert;

/**
 * @author Ian Phillips <ian@gumstix.com>
 */
interface AlertMessage
{
    /** @return string */
    public function getLevel();

    /** @return string */
    public function getMessage();

    /** @return AlertResolution */
    public function getResolution();

    /** @return bool */
    public function isError();

    /** @return bool */
    public function isNotice();
}
