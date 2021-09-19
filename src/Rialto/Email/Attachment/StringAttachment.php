<?php

namespace Rialto\Email\Attachment;

use Gumstix\Storage\ContentType;
use Swift_Attachment;

class StringAttachment extends Attachment
{
    /**
     * @var string
     */
    private $content = null;

    protected function __construct($filename, $content)
    {
        parent::__construct($filename);
        $this->content = $content;
    }

    public function exists()
    {
        return (bool) $this->content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    public function createSwiftAttachment()
    {
        $data = $this->getContent();
        return new Swift_Attachment(
            $data, $this->getFilename(), ContentType::fromData($data));
    }
}
