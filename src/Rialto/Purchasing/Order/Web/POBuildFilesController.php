<?php

namespace Rialto\Purchasing\Order\Web;

use Gumstix\Storage\FileStorage;
use InvalidArgumentException;
use Rialto\Purchasing\Order\Web\PurchasingOrderBuildFilesType;
use Rialto\Purchasing\Order\POBuildFiles;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Order\PurchasingOrderBuildFiles;
use Rialto\Purchasing\PurchasingEvents;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles requests to upload the engineering files that are required to
 * build products.
 */
class POBuildFilesController extends RialtoController
{
    /**
     * Renders and processes the form for uploading build files.
     *
     * @Route("/purchasing/order/{order}/buildFiles",
     *   name="purchasing_order_buildFiles_upload")
     * @Template("purchasing/order/buildFiles/upload.html.twig")
     */
    public function uploadAction(PurchaseOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);

        $files = $this->getBuildFiles($order);
        $form = $this->createForm($this->getFormType($files), $files);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $files->saveFiles();
                $this->notifyOfUpload($files);
                $this->logNotice("Build files uploaded successfully.");
                $uri = $this->itemUrl($order);
                return $this->redirect($uri);
            }
        }

        return [
            'form' => $form->createView(),
            'po' => $order,
            'files' => $files,
            'cancelUri' => $this->itemUrl($order),
        ];
    }

    private function itemUrl(PurchaseOrder $purchaseOrder)
    {
        return $this->generateUrl('purchase_order_view', [
            'order' => $purchaseOrder->getId(),
        ]);
    }

    /**
     * Downloads the specified build file.
     *
     * @Route("/purchasing/order/{order}/buildFiles/{filename}",
     *     name="po_build_files_download")
     */
    public function getAction(PurchaseOrder $order, $filename)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $files = $this->getBuildFiles($order);

        if (! $files->exists($filename)) {
            throw $this->notFound(
                "No such file \"$filename\" for {$order->getId()}");
        }
        $content = $files->getContents($filename);
        $mimetype = $files->getMimeType($filename);
        $basename = $files->getBasename($filename);
        return FileResponse::fromData($content, $basename, $mimetype);
    }

    private function getBuildFiles(PurchaseOrder $purchaseOrder): POBuildFiles
    {
        $storage = $this->getFileStorage();
        try {
            return POBuildFiles::create($purchaseOrder, $storage);
        } catch (InvalidArgumentException $ex) {
            throw $this->badRequest($ex->getMessage());
        }
    }

    private function getFileStorage(): FileStorage
    {
        return $this->get(FileStorage::class);
    }

    private function getFormType(POBuildFiles $files): string
    {
        if ($files instanceof PurchasingOrderBuildFiles) {
            return PurchasingOrderBuildFilesType::class;
        }
        $class = get_class($files);
        throw new \UnexpectedValueException("Unknown BuildFiles subclass $class");
    }

    private function notifyOfUpload(POBuildFiles $files)
    {
        $event = new POBuildFilesEvent($files);
        $this->dispatchEvent(PurchasingEvents::onPOBuildFilesUpload, $event);
    }
}
