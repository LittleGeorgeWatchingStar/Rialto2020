<?php


namespace Rialto\Alert;


/**
 * Allows objects to record non-fatal warnings that can be carried back
 * to the controller and displayed to the user.
 */
trait HasWarnings
{
    /** @var string[] */
    private $warnings = [];

    public function addWarning(string $warning)
    {
        $this->warnings[] = $warning;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
