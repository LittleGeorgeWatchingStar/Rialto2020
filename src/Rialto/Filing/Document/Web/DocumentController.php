<?php

namespace Rialto\Filing\Document\Web;

use Exception;
use Gumstix\Storage\StorageException;
use Rialto\Filing\Document\Document;
use Rialto\Filing\FilingController;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\FileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for managing documents that must be filed.
 *
 * @see Document
 */
class DocumentController extends FilingController
{
    /**
     * @Route("/filing/document/", name="filing_document_list")
     * @Method("GET")
     * @Template("filing/document/list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $documents = $this->getRepository(Document::class)
            ->findAll();
        return ['documents' => $documents];
    }

    /**
     * Create a new document.
     * @Route("/Filing/Document/", name="Filing_Document_create")
     * @Template("filing/document/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $document = new Document();
        return $this->handleForm($document, $request, 'created');
    }

    /**
     * Edit an existing document.
     * @Route("/Filing/Document/{id}/", name="Filing_Document_edit")
     * @Template("filing/document/edit.html.twig")
     */
    public function editAction(Document $document, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        return $this->handleForm($document, $request);
    }

    private function handleForm(Document $document, Request $request, $updated = 'updated')
    {
        $returnUri = $this->generateUrl('filing_document_list');

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->dbm->persist($document);
                $this->dbm->flush(); // make sure the document has an ID
                $filesystem = $this->getDocumentFilesystem();
                $filesystem->saveTemplateFile($document);
                $document->setUpdated();
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("$document $updated successfully.");
            return $this->redirect($returnUri);
        }

        return [
            'document' => $document,
            'form' => $form->createView(),
            'cancelUri' => $returnUri,
        ];
    }

    /**
     * Allows the user to download the template file.
     *
     * @Route("/Filing/Document/{id}/templateFile/",
     *   name="Filing_Document_templateFile")
     * @Method("GET")
     */
    public function downloadAction(Document $document)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        if (! $document->hasTemplateFile()) {
            throw $this->badRequest("$document has no template file");
        }
        $filesystem = $this->getDocumentFilesystem();
        try {
            $data = $filesystem->getTemplateContents($document);
        } catch (StorageException $ex) {
            throw $this->notFound("Template file for $document is missing", $ex);
        }

        $filename = sprintf('%s_%s', $document, $document->getTemplateFilename());
        return FileResponse::fromData($data, $filename);
    }
}
