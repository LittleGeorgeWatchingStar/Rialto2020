<?php

namespace Rialto\Panelization\Web;

use Gumstix\Storage\StorageException;
use Rialto\Panelization\IO\PanelizationStorage;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Manages the consolidated BOM, XY, and layout files for a purchase order.
 */
class AssetsController extends RialtoController
{
    /**
     * List consolidated files, if any, for the given PO.
     *
     * @Route("/panelization/assets/{id}/", name="panelization_assets")
     * @Method("GET")
     * @Template("panelization/assets/list.html.twig")
     */
    public function listAction(PurchaseOrder $po)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::PURCHASING]);
        $storage = $this->getStorage();
        $files = $storage->getFiles($po);

        return [
            'po' => $po,
            'files' => $files,
        ];
    }

    private function getStorage(): PanelizationStorage
    {
        return $this->get(PanelizationStorage::class);
    }

    /**
     * @Route("/panelization/assets/{po}/{filename}",
     *   name="panelization_download")
     * @Method("GET")
     */
    public function downloadAction(PurchaseOrder $po, $filename)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::PURCHASING]);
        $storage = $this->getStorage();
        try {
            $filedata = $storage->getFileContents($po, $filename);
        } catch (StorageException $ex) {
            throw $this->notFound($ex->getMessage(), $ex);
        }
        $mimeType = $storage->getMimeType($po, $filename);
        return FileResponse::fromData($filedata, $filename, $mimeType);
    }
}
