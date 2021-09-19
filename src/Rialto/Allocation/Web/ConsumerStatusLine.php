<?php

namespace Rialto\Allocation\Web;

/**
 * Helper class for rendering the allocation status of a consumer.
 */
class ConsumerStatusLine
{
    public $qty;
    public $icon;
    public $text;

    public function __construct($qty, $icon, $text)
    {
        $this->qty = $qty;
        $this->icon = $icon;
        $this->text = $text;
    }

    public function isVisible()
    {
        return $this->qty > 0;
    }

}
