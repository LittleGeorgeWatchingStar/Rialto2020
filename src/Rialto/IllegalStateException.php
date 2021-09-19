<?php

namespace Rialto;

/**
 * Throw this exception when a method is called on an object that is
 * not is a state to have that method called; for example: writing to a
 * stream object after it has been closed.
 */
class IllegalStateException
extends \LogicException
{
    /* No modifications */
}
