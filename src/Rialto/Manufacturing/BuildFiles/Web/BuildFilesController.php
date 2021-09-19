<?php

namespace Rialto\Manufacturing\BuildFiles\Web;

use FOS\RestBundle\View\View;
use Gumstix\Storage\FileStorage;
use InvalidArgumentException;
use Rialto\Manufacturing\BuildFiles\BuildFiles;
use Rialto\Manufacturing\BuildFiles\BuildFilesEvent;
use Rialto\Manufacturing\BuildFiles\PcbBuildFiles;
use Rialto\Manufacturing\BuildFiles\PcbBuildFileVoter;
use Rialto\Manufacturing\ManufacturingEvents;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles requests to upload the engineering files that are required to
 * build products.
 */
class BuildFilesController extends RialtoController
{
    /**
     * Saves uploaded build/manufacturing files to the filesystem, where
     * they can be used for creating work and purchase orders.
     *
     * @Route("/api/v2/manufacturing/item/{stockCode}/version/{version}/buildfiles/")
     * @Method("POST")
     *
     * @api for Geppetto Client
     */
    public function postAction(StockItem $item, $version, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $version = $this->getVersion($item, new Version($version));
        $files = $this->getBuildFiles($item, $version);

        $form = $this->createForm($this->getFormType($files), $files);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $files->saveFiles();
            $this->notifyOfUpload($files);
        } else {
            return JsonResponse::fromInvalidForm($form);
        }

        return View::create([], Response::HTTP_NO_CONTENT);
    }

    private function getVersion(StockItem $item, Version $version)
    {
        if (! $version->isSpecified()) {
            throw $this->badRequest("Version must be specified");
        }
        if (! $item->isVersioned()) {
            throw $this->badRequest("$item is not versioned");
        }
        if (! $item->hasVersion($version)) {
            throw $this->notFound("$item has no such version $version");
        }
        return $item->getVersion($version);
    }

    private function getBuildFiles(StockItem $item, Version $version): BuildFiles
    {
        $storage = $this->getFileStorage();
        try {
            return BuildFiles::create($item, $version, $storage);
        } catch (InvalidArgumentException $ex) {
            throw $this->badRequest($ex->getMessage());
        }
    }

    private function getFileStorage(): FileStorage
    {
        return $this->get(FileStorage::class);
    }

    private function getFormType(BuildFiles $files): string
    {
        if ($files instanceof PcbBuildFiles) {
            return PcbBuildFilesType::class;
        }
        $class = get_class($files);
        throw new \UnexpectedValueException("Unknown BuildFiles subclass $class");
    }

    private function notifyOfUpload(BuildFiles $files)
    {
        $event = new BuildFilesEvent($files);
        $this->dispatchEvent(ManufacturingEvents::onBuildFilesUpload, $event);
    }

    /**
     * Renders and processes the form for uploading build files.
     *
     * @Route("/Manufacturing/StockItem/{stockCode}/version/{version}/buildFiles",
     *   name="Manufacturing_BuildFiles_upload")
     * @Template("manufacturing/buildFiles/upload.html.twig")
     */
    public function uploadAction(StockItem $item, $version, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        if ($version) {
            $version = $this->getVersion($item, new Version($version));
        } else {
            $version = $item->getAutoBuildVersion();
        }

        $files = $this->getBuildFiles($item, $version);
        $form = $this->createForm($this->getFormType($files), $files);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $files->saveFiles();
                $this->notifyOfUpload($files);
                $this->logNotice("Build files uploaded successfully.");
                $uri = $this->itemUrl($item);
                return $this->redirect($uri);
            }
        }

        return [
            'form' => $form->createView(),
            'item' => $item,
            'version' => $version,
            'files' => $files,
            'cancelUri' => $this->itemUrl($item),
        ];
    }

    private function itemUrl(Item $item)
    {
        return $this->generateUrl('stock_item_view', [
            'item' => $item->getSku(),
        ]);
    }

    /**
     * Downloads the specified build file.
     *
     * @Route("/Manufacturing/StockItem/{stockCode}/version/{version}/buildFiles/{filename}",
     *     name="build_files_download")
     */
    public function getAction(StockItem $item, $version, $filename)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::SUPPLIER_ADVANCED]);

        if (!$this->isGranted(PcbBuildFileVoter::VIEW, $filename)) {
            throw $this->createAccessDeniedException();
        }

        $version = $this->getVersion($item, new Version($version));
        $files = $this->getBuildFiles($item, $version);

        if (! $files->exists($filename)) {
            throw $this->notFound(
                "No such file \"$filename\" for {$version->getFullSku()}");
        }
        $content = $files->getContents($filename);
        $mimetype = $files->getMimeType($filename);
        $basename = $files->getBasename($filename);
        return FileResponse::fromData($content, $basename, $mimetype);
    }
}
