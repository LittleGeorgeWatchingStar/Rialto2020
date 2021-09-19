<?php

namespace Rialto\Manufacturing\Bom\Web;

use Exception;
use FOS\RestBundle\View\View;
use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Manufacturing\Bom\BomEvent;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Manufacturing\ManufacturingEvents;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\VersionedItemSummary;
use Rialto\Stock\Item\Version\Web\ItemVersionSelectorType;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller for creating, updating Bills of Material (BOMs) for manufactured
 * items.
 */
class BomController extends RialtoController
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var ValidatorInterface */
    private $validator;

    protected function init(ContainerInterface $container)
    {
        $this->formFactory = $this->get(FormFactoryInterface::class);
        $this->validator = $this->get(ValidatorInterface::class);
    }

    /**
     * Creates a new BOM from an uploaded csv file.
     *
     * @Route("/Manufacturing/Bom/{stockCode}/version/{version}/upload/",
     *   name="Manufacturing_Bom_upload",
     *   requirements={"version" = "[^/]*"},
     *   defaults={"version" = ""})
     * @Template("manufacturing/bom/upload.html.twig")
     */
    public function uploadAction(StockItem $parent, $version, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::STOCK]);
        $parentVersion = $this->getVersion($parent, $version);
        $uploader = new BomCsvFileUpload($parentVersion);
        $form = $this->createForm(BomCsvFileType::class, $uploader);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $uploader->updateBomFromCsv($this->dbm);
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            return $this->redirectToVersion($parentVersion);
        }
        return [
            'form' => $form->createView(),
            'version' => $parentVersion,
            'requiredHeadings' => BomCsvFile::getRequiredHeadings(),
            'optionalHeadings' => BomCsvFile::getOptionalHeadings(),
        ];
    }

    /**
     * REST API for creating/updating a BOM.
     *
     * @Route("/api/v2/manufacturing/item/{stockCode}/version/{version}/bom/")
     * @Method("PUT")
     *
     * @api for Geppetto Client
     */
    public function putAction(StockItem $item, $version, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::STOCK]);
        $parent = $this->getVersion($item, $version);
        $this->dbm->beginTransaction();
        try {
            $parent->clearBom();
            $this->dbm->flush();

            $form = $this->createForm(BomApiType::class, $parent);
            $form->submit($request->get($form->getName()));
            if ($form->isValid()) {
                $event = new BomEvent($parent);
                $dispatcher = $this->dispatcher();
                $dispatcher->dispatch(ManufacturingEvents::NEW_BOM, $event);
                $this->dbm->flushAndCommit();
            } else {
                $this->dbm->rollBack();
                return View::create($form);
            }
            return View::create(new VersionedItemSummary($parent))
                ->setFormat('json');
        } catch (Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
    }

    /** @return ItemVersion */
    private function getVersion(StockItem $item, $version)
    {
        if (! $item->hasVersion($version)) {
            throw $this->notFound("$item has no such version $version");
        }
        return $item->getVersion($version);
    }

    /**
     * REST API for getting a BOM.
     *
     * @Route("/api/v2/manufacturing/item/{item}/version/{version}/bom/")
     * @Method("GET")
     *
     * @api for Geppetto Client
     */
    public function getAction(StockItem $item, $version)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::STOCK]);
        $parent = $this->getVersion($item, $version);
        $bom = $parent->getBomItems();
        return View::create(array_map(function (BomItem $i) {
            return [
                'sku' => $i->getSku(),
                'version' => (string) $i->getVersion(),
                'quantity' => $i->getQuantity(),
                'designators' => $i->getDesignators(),
                'package' => $i->getPackage(),
                'partValue' => $i->getPartValue(),
                'value' => $i->getPartValue(),
            ];
        }, $bom));
    }

    /**
     * @Route("/Manufacturing/Bom/{stockCode}/version/{version}/pdf",
     *   name="Manufacturing_Bom_pdf",
     *   requirements={"version" = "[^/]*"})
     * @Method("GET")
     */
    public function pdfAction(StockItem $item, $version)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::STOCK_VIEW]);
        $parent = $this->getVersion($item, $version);
        $pdf = $this->get(PdfGenerator::class);
        $pdfData = $pdf->render('manufacturing/bom/pdf.tex.twig', [
            'parent' => $parent
        ]);
        $filename = $parent->getFullSku();
        return PdfResponse::create($pdfData, $filename);
    }

    /**
     * Renders the BOM as a CSV file.
     *
     * @Route("/Manufacturing/Bom/{stockCode}/version/{version}/csv",
     *   name="Manufacturing_Bom_csv",
     *   requirements={"version" = "[^/]*"})
     * @Method({"GET"})
     */
    public function csvAction(StockItem $item, $version)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::STOCK_VIEW]);
        $parent = $this->getVersion($item, $version);

        /** @var PurchasingDataRepository $purchasingDataRepository */
        $purchasingDataRepository = $this->dbm->getRepository(PurchasingData::class);
        $csv = BomCsvFile::fromComponents($parent->getBomItems(), $purchasingDataRepository);
        $csv->useWindowsNewline();
        $filename = sprintf("BOM_%s.csv", $parent->getFullSku());
        return FileResponse::fromData($csv->toString(), $filename, 'text/csv');
    }


    /**
     * @Route("/Manufacturing/Bom/{stockCode}/version/{version}/copy",
     *   name="Manufacturing_Bom_copy",
     *   requirements={"version" = "[^/]*"})
     * @Method("POST")
     */
    public function copyAction(StockItem $item, $version, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::STOCK]);
        $toVersion = $this->getVersion($item, $version);

        $form = $this->formFactory->createNamed('fromVersion', ItemVersionSelectorType::class, null, [
            'csrf_protection' => false,
        ]);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            $this->logError("Please select a version to copy from.");
            return $this->redirectToVersion($toVersion);
        }
        if (!$form->isValid()) {
            $fromVersion = $request->get('fromVersion');
            $this->logError("Problems found with $fromVersion:");
            $this->logErrors($form->getErrors(true));
            return $this->redirectToVersion($toVersion);
        }
        /* @var $fromVersion ItemVersion */
        $fromVersion = $form->getData();

        $error = $this->validateSourceVersion($fromVersion, $toVersion);
        if ($error) {
            $this->logError($error);
            return $this->redirectToVersion($toVersion);
        }

        $this->dbm->beginTransaction();
        try {
            foreach ($toVersion->getBomItems() as $oldItem) {
                $this->dbm->remove($oldItem);
                $toVersion->removeBomItem($oldItem);
            }
            $this->dbm->flush();

            foreach ($fromVersion->getBomItems() as $bomItem) {
                $newItem = $bomItem->copyTo($toVersion);
                $this->dbm->persist($newItem);
            }
            $this->dbm->flushAndCommit();
        } catch (Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        $this->logNotice(sprintf('BOM copied from %s successfully.',
            $fromVersion->getFullSku()
        ));
        return $this->redirectToVersion($toVersion);
    }

    private function redirectToVersion(ItemVersion $version)
    {
        return $this->redirectToRoute('item_version_edit', [
            'item' => $version->getSku(),
            'version' => $version->getVersionCode(),
        ]);
    }

    private function validateSourceVersion(ItemVersion $fromVersion, ItemVersion $toVersion)
    {
        $desc = $fromVersion->getFullSku();
        if ($fromVersion === $toVersion) {
            return "Cannot copy BOM from $desc to itself.";
        } elseif (! $fromVersion->hasBomItems()) {
            return "$desc has no BOM.";
        } else {
            $errors = $this->validator->validate($fromVersion);
            if (count($errors) > 0) {
                return "$desc is invalid: $errors";
            }
        }
        return null;
    }
}
