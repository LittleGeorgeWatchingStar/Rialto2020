<?php

namespace Rialto\Purchasing\Order\Web;

use Rialto\Purchasing\Order\POBuildFiles;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is fired whenever build/engineering files are updated/added, etc.
 */
class POBuildFilesEvent extends Event
{
    /** @var POBuildFiles */
    private $buildFiles;

    public function __construct(POBuildFiles $buildFiles)
    {
        $this->buildFiles = $buildFiles;
    }

    /** @return POBuildFiles */
    public function getBuildFiles()
    {
        return $this->buildFiles;
    }

}
