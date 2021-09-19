<?php

namespace Rialto\Filing\Entry\Web;

use Exception;
use Gumstix\Storage\StorageException;
use Rialto\Database\Orm\EntityList;
use Rialto\Filing\Document\Document;
use Rialto\Filing\Entry\Entry;
use Rialto\Filing\FilingController;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\FileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages document entries.
 *
 * @see Entry
 */
class EntryController extends FilingController
{
    /**
     * @Route("/filing/entry/", name="filing_entry_list")
     * @Method("GET")
     * @Template("filing/entry/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $repo = $this->getRepository(Entry::class);
        $filters = $request->query->all();
        $list = new EntityList($repo, $filters);
        return ['entries' => $list];
    }

    /**
     * @Route("/Filing/Document/{id}/entry/", name="Filing_Entry_create")
     * @Template("filing/entry/entry-create.html.twig")
     */
    public function createAction(Document $document, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $returnUri = $this->generateUrl('Filing_Document_edit', [
            'id' => $document->getId(),
        ]);
        $entry = new Entry($document, $this->getCurrentUser());
        $form = $this->createForm(EntryType::class, $entry);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->dbm->persist($entry);
                $this->dbm->flush(); // Make sure $entry has an ID.
                $filesystem = $this->getDocumentFilesystem();
                $filesystem->saveEntry($entry);
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Uploaded an entry for $document successfully.");
            return $this->redirect($returnUri);
        }

        return [
            'document' => $document,
            'form' => $form->createView(),
            'cancelUri' => $returnUri,
        ];
    }

    /**
     * @Route("/Filing/Entry/{id}/file/",
     *   name="Filing_Entry_download")
     * @Method("GET")
     */
    public function downloadAction(Entry $entry)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $filesystem = $this->getDocumentFilesystem();
        try {
            $data = $filesystem->getEntryContents($entry);
        } catch (StorageException $ex) {
            throw $this->notFound("File for $entry is missing", $ex);
        }
        $filename = sprintf('%s_%s', $entry, $entry->getFilename());
        return FileResponse::fromData($data, $filename);
    }
}
