<?php

namespace Rialto\Filing;

use Rialto\Web\RialtoController;

/**
 * Base class for controllers in Filing.
 */
abstract class FilingController extends RialtoController
{
    /** @return DocumentFilesystem */
    protected function getDocumentFilesystem()
    {
        return $this->get(DocumentFilesystem::class);
    }
}
