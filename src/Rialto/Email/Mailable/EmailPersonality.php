<?php

namespace Rialto\Email\Mailable;

/**
 * Static factory for creating automated "email personalities".
 */
class EmailPersonality
{
    /** @return Mailable */
    public static function BobErbauer()
    {
        return new GenericMailable('bob@gumstix.com', 'Bob Erbauer');
    }

    /** @return Mailable */
    public static function CharlesBartlett()
    {
        return new GenericMailable('charles@gumstix.com', 'Charles Bartlett');
    }
}
