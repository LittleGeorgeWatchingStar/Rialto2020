<?php

namespace Rialto\Manufacturing\BuildFiles;

use Symfony\Component\EventDispatcher\Event;

/**
 * This event is fired whenever build/engineering files are updated/added, etc.
 */
class BuildFilesEvent extends Event
{
    /** @var BuildFiles */
    private $buildFiles;

    public function __construct(BuildFiles $buildFiles)
    {
        $this->buildFiles = $buildFiles;
    }

    /** @return BuildFiles */
    public function getBuildFiles()
    {
        return $this->buildFiles;
    }

}
