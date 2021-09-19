<?php

namespace Rialto\Stock\Publication\Web;

use Exception;
use Rialto\Database\Orm\EntityList;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Publication\Orm\PublicationRepository;
use Rialto\Stock\Publication\Publication;
use Rialto\Stock\Publication\PublicationFilesystem;
use Rialto\Stock\Publication\UploadPublication;
use Rialto\Stock\Publication\UrlPublication;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for managing publications related to stock items.
 */
class PublicationController extends RialtoController
{
    /** @var PublicationRepository */
    private $repo;

    /** @var PublicationFilesystem */
    private $filesystem;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->getRepository(Publication::class);
        $this->filesystem = $this->get(PublicationFilesystem::class);
    }

    /**
     * List and search Publications.
     *
     * @Route("/stock/publication/", name="stock_publication_list")
     * @Method("GET")
     * @Template("stock/publication/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $form = $this->createForm(ListFilterType::class);
        $form->submit($request->query->all());
        $results = new EntityList($this->repo, $form->getData());
        return [
            'form' => $form->createView(),
            'list' => $results,
        ];
    }

    /**
     * Adds a URL publication to a stock item.
     *
     * @Route("/stock/item/{stockCode}/url-publication/",
     *  name="stock_publication_create")
     * @Template("stock/publication/create.html.twig")
     */
    public function createAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $pub = new UrlPublication($item);
        $form = $this->createForm(UrlPublicationType::class, $pub);
        $returnUri = $this->listUrl($item);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($pub);
            $this->dbm->flush();
            $this->logNotice("Added publication \"$pub\" to $item.");
            return $this->redirect($returnUri);
        }

        return [
            'item' => $item,
            'form' => $form->createView(),
            'cancelUri' => $returnUri,
        ];
    }

    private function listUrl(StockItem $item)
    {
        return $this->generateUrl('stock_publication_list', [
            'stockItem' => $item->getSku(),
        ]);
    }

    /**
     * Uploads a publication to a stock item.
     *
     * @Route("/stock/item/{stockCode}/upload-publication/",
     *  name="stock_publication_upload")
     * @Template("stock/publication/create.html.twig")
     */
    public function uploadAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $pub = new UploadPublication($item);
        $form = $this->createForm(UploadPublicationType::class, $pub);
        $returnUri = $this->listUrl($item);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->dbm->persist($pub);
                $this->dbm->flush();
                $this->filesystem->saveFile($pub);
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Added publication \"$pub\" to $item.");
            return $this->redirect($returnUri);
        }

        return [
            'item' => $item,
            'form' => $form->createView(),
            'cancelUri' => $returnUri,
        ];
    }

    /**
     * @Route("/stock/publication/{id}/",
     *   name="stock_publication_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Publication $pub)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $item = $pub->getStockItem();
        $this->dbm->beginTransaction();
        try {
            $this->dbm->remove($pub);
            $this->filesystem->deleteFile($pub);
            $this->dbm->flushAndCommit();
        } catch (Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
        $this->logNotice("Deleted publication \"$pub\" from $item.");
        return $this->redirect($this->listUrl($item));
    }

    /**
     * Downloads the publication.
     *
     * @Route("/stock/publication/{id}/content/",
     *   name="stock_publication_content")
     * @Method("GET")
     */
    public function contentAction(UploadPublication $pub)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        if (!$this->filesystem->hasFile($pub)) {
            $filename = $pub->getFilename();
            throw $this->notFound("The file '$filename' was not found'");
        }
        $data = $this->filesystem->getFileContents($pub);
        return FileResponse::fromData($data, $pub->getFilename(), $this->filesystem->getMimeType($pub));
    }
}

