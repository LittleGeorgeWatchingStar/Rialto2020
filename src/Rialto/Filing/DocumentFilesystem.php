<?php

namespace Rialto\Filing;

use Gumstix\Storage\FileStorage;
use Rialto\Filing\Document\Document;
use Rialto\Filing\Entry\Entry;

/**
 * Accesses document templates stored on the filesystem.
 */
class DocumentFilesystem
{
    /**
     * @var FileStorage
     */
    private $storage;

    public function __construct(FileStorage $storage)
    {
        $this->storage = $storage;
    }

    public function saveTemplateFile(Document $document)
    {
        $uploadedFile = $document->getTemplateFile();
        if ( null === $uploadedFile ) {
            return;
        }
        $filename = 'template.'. $uploadedFile->guessExtension();
        $document->setTemplateFilename($filename);
        $key = $this->getTemplateKey($document);
        $this->storage->putFile($key, $uploadedFile);
    }

    private function getTemplateKey(Document $document)
    {
        $dir = $this->getDocumentDirectory($document);
        $filename = $document->getTemplateFilename();
        assertion($filename);
        return "$dir/$filename";
    }

    private function getDocumentDirectory(Document $document)
    {
        $id = $document->getId();
        assertion($id, "Document $document has no ID");
        return join('/', ['filing', $id]);
    }

    /** @return string the file data */
    public function getTemplateContents(Document $document)
    {
        $key = $this->getTemplateKey($document);
        return $this->storage->get($key);
    }

    public function saveEntry(Entry $entry)
    {
        $id = $entry->getId();
        assertion(null != $id);
        $uploadedFile = $entry->getFile();
        assertion(null !== $uploadedFile);

        $filename = $id .'.'. $uploadedFile->guessExtension();
        $entry->setFilename($filename);
        $key = $this->getEntryKey($entry);
        $this->storage->putFile($key, $uploadedFile);
    }

    private function getEntryKey(Entry $entry)
    {
        $dir = $this->getEntryDirectory($entry);
        $filename = $entry->getFilename();
        assertion($filename);
        return "$dir/$filename";
    }

    private function getEntryDirectory(Entry $entry)
    {
        $document = $entry->getDocument();
        assertion(null != $document);

        $baseDir = $this->getDocumentDirectory($document);
        return join('/', [$baseDir, 'entry']);
    }

    /** @return string the file contents */
    public function getEntryContents(Entry $entry)
    {
        $key = $this->getEntryKey($entry);
        return $this->storage->get($key);
    }
}
