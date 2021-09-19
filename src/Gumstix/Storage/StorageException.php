<?php

namespace Gumstix\Storage;


use Exception;
use RuntimeException;


class StorageException
extends RuntimeException
{
    public static function fromPrevious(Exception $ex)
    {
        return new self($ex->getMessage(), $ex->getCode(), $ex);
    }
}
