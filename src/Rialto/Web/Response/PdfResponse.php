<?php

namespace Rialto\Web\Response;

class PdfResponse
{
    public static function create($pdfData, $filename)
    {
        return FileResponse::fromData($pdfData, $filename, 'application/pdf');
    }
}
