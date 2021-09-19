<?php

namespace Rialto\Web\Response;

use Gumstix\Storage\ContentType;
use Symfony\Component\HttpFoundation\Response;

/**
 * For returning a file download response.
 */
class FileResponse
{
    public static function fromData($data, $filename, $contentType = null)
    {
        $response = new Response($data);
        $response->headers->set('Content-type', $contentType ?: ContentType::fromData($data));
        $response->headers->set('Content-disposition', "attachment; filename=\"$filename\"");
        return $response;
    }
}
