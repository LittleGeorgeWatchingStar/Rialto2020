<?php

namespace Rialto\Stock\Publication;

use Gumstix\Storage\File;
use Gumstix\Storage\FileStorage;

/**
 * Provides filesystem access for stock item publications.
 */
class PublicationFilesystem
{
    /** @var FileStorage */
    private $storage;

    public function __construct(FileStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Saves the uploaded file for the given publication, if there is one.
     */
    public function saveFile(UploadPublication $pub)
    {
        assertion($pub->getId(), "Publication '$pub' has no ID");
        $uploadedFile = $pub->getFile();
        assertion(null != $uploadedFile, "Publication '$pub' has no file");

        $filename = sprintf('%s.%s',
            $pub->getId(),
            $uploadedFile->guessExtension());
        $pub->setFilename($filename);
        $key = $this->getKey($pub);
        $this->storage->putFile($key, $uploadedFile);
    }

    /** @return string */
    private function getDirectory(UploadPublication $pub)
    {
        return join('/', [
            'publications',
            $pub->getSku(),
        ]);
    }

    /**
     * Deletes the uploaded file associated with the given publication,
     * if there is one.
     *
     * Does NOT delete the publication record itself.
     */
    public function deleteFile(Publication $pub)
    {
        if (! $pub instanceof UploadPublication ) {
            return;
        }
        if (! $pub->getFilename() ) {
            return;
        }
        $this->storage->delete($this->getKey($pub));
    }

    private function getKey(UploadPublication $pub)
    {
        $filename = $pub->getFilename();
        assertion($filename);
        return join('/', [
            $this->getDirectory($pub),
            $filename
        ]);
    }

    /**
     * @return bool
     */
    public function hasFile(UploadPublication $pub)
    {
        if (! $pub->getFilename()) {
            return false;
        }
        return $this->storage->exists($this->getKey($pub));
    }

    /** @return File */
    public function getFile(UploadPublication $pub)
    {
        return $this->storage->getFile($this->getKey($pub));
    }

    /** @return string */
    public function getFileContents(UploadPublication $pub)
    {
        return $this->storage->get($this->getKey($pub));
    }

    /** @return string */
    public function getMimeType(UploadPublication $pub)
    {
        return $this->storage->getMimeType($this->getKey($pub));
    }
}
