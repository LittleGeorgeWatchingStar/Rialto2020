<?php

namespace Rialto\Cms;

use Rialto\Database\Orm\DbManager;
use Rialto\Database\Orm\Persistable;
use Rialto\Entity\RialtoEntity;
use Rialto\Util\Strings\TextFormatter;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An entry in Rialto's internal content management system (CMS).
 *
 * The internal CMS stores text and HTML snippets that appear throughout
 * the application. This allows the text to be modified by the user, rather
 * than hard-coded.
 *
 * @UniqueEntity(fields="id", message="That ID is already in use.")
 */
class CmsEntry implements RialtoEntity, Persistable
{
    const FORMAT_HTML = 'html';
    const FORMAT_TEXT = 'text';

    /**
     * @Assert\NotBlank
     * @Assert\Regex(
     *   pattern="/^[a-zA-Z_.]+$/",
     *   message="The ID may only contain letters, underscores, and periods.")
     * @Assert\Length(max="50")
     */
    private $id;

    /**
     * @var string
     * @Assert\Choice(callback="getValidFormats", strict=true)
     */
    private $format = self::FORMAT_HTML;

    /**
     * The content of this entry, in HTML format.
     * @var string
     */
    private $content = '';

    /** @return CmsEntry|null */
    public static function fetch($id, DbManager $dbm)
    {
        return $dbm->find(CmsEntry::class, $id);
    }

    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return sprintf('CMS entry "%s"', $this->id);
    }

    public function setId($id)
    {
        $this->id = trim($id);
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    private function isPlainText()
    {
        return self::FORMAT_TEXT == $this->format;
    }

    public static function getValidFormats()
    {
        return [self::FORMAT_HTML, self::FORMAT_TEXT];
    }

    public static function getFormatChoices()
    {
        $f = self::getValidFormats();
        return array_combine($f, $f);
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getFormattedContent()
    {
        return $this->isPlainText() ? $this->getTextContent() : $this->content;
    }

    private function getTextContent()
    {
        $formatter = new TextFormatter();
        return $formatter->htmlToText($this->content);
    }

    public function setContent($content)
    {
        $this->content = trim($content);
    }

    public function getEntities()
    {
        return [$this];
    }
}
