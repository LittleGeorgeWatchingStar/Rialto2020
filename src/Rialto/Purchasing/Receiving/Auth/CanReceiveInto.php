<?php

namespace Rialto\Purchasing\Receiving\Auth;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CanReceiveInto extends Constraint
{
    public $message = "You cannot receive into {{ facility }}.";
}
