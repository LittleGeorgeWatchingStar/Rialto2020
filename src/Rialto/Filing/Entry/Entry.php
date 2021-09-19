<?php

namespace Rialto\Filing\Entry;

use DateTime;
use Rialto\Database\Orm\Persistable;
use Rialto\Entity\RialtoEntity;
use Rialto\Filing\Document\Document;
use Rialto\Security\User\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A record of when a document was filed, including a copy of the filed
 * document.
 */
class Entry implements RialtoEntity, Persistable
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var Document
     */
    private $document;

    /**
     * @var DateTime
     */
    private $dateFiled;

    /**
     * @var User
     */
    private $filedBy;

    /**
     * @var UploadedFile
     * @Assert\File(maxSize="1M")
     * @Assert\NotNull
     */
    private $file;

    /**
     * @var string
     */
    private $filename = '';


    public function __construct(Document $document, User $filedBy)
    {
        $this->document = $document;
        $this->filedBy = $filedBy;
        $this->dateFiled = new DateTime();
    }


    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * The document that was filed.
     *
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    public function __toString()
    {
        return sprintf('%s filed on %s',
            $this->document,
            $this->dateFiled->format('Y-m-d'));
    }

    /**
     * The date this document was filed.
     *
     * @return DateTime
     */
    public function getDateFiled()
    {
        return $this->dateFiled;
    }

    /**
     * The user who filed this document.
     *
     * @return User
     */
    public function getFiledBy()
    {
        return $this->filedBy;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = trim($filename);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    public function getEntities()
    {
        return [$this];
    }

}
