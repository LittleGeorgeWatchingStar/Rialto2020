<?php

namespace Rialto\Purchasing\Catalog\Web;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use FOS\RestBundle\View\View;
use Gumstix\Filetype\CsvFile;
use Rialto\Database\Orm\EntityList;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Catalog\PurchasingDataException;
use Rialto\Purchasing\Catalog\PurchasingDataSynchronizer;
use Rialto\Purchasing\Catalog\Remote\Orm\SupplierApiRepository;
use Rialto\Purchasing\Catalog\Remote\SupplierApi;
use Rialto\Purchasing\Quotation\QuotationCsvMapping;
use Rialto\Purchasing\Quotation\Web\QuotationCsvMappingType;
use Rialto\Purchasing\Quotation\Web\QuotationCsvUploadType;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\PurchasedStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controls pages for creating and editing PurchasingData records.
 *
 * @see PurchasingData
 */
class PurchasingDataController extends RialtoController
{
    /**
     * @Route("/purchasing/data/", name="purchasing_data_list")
     * @Template("purchasing/purchdata/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING_DATA);
        $form = $this->createForm(ListFilterType::class);
        $repo = $this->getRepo();
        $form->submit($request->query->all());
        $list = new EntityList($repo, $form->getData());
        if ($form->get('csv')->isClicked()) {
            try {
                $csv = PurchasingDataCsv::generate($list);
            } catch (PurchasingDataException $ex) {
                $this->logError($ex->getMessage());
                return $this->redirectToRoute('purchasing_data_list', [
                    'stockItem' => $ex->getStockCode(),
                    '_limit' => 0,
                ]);
            }
            return FileResponse::fromData($csv->toString(), 'purchasing_data.csv', 'text/csv');
        }

        $stockItem = $form->get('stockItem')->getData();
        $isPurchasedStockItem = false;
        $isManufacturedStockItem = false;
        if ($stockItem) {
            $isPurchasedStockItem = $stockItem instanceof PurchasedStockItem;
            $isManufacturedStockItem = $stockItem instanceof ManufacturedStockItem;
        }

        return [
            'form' => $form->createView(),
            'list' => $list,
            'isPurchasedStockItem' => $isPurchasedStockItem,
            'isManufacturedStockItem' => $isManufacturedStockItem,
        ];
    }

    /** @return PurchasingDataRepository|EntityRepository */
    private function getRepo()
    {
        return $this->getRepository(PurchasingData::class);
    }

    /**
     * Creates a new purchasing data record via the API.
     *
     * @Route("/api/v2/purchasing/purchasingdata/")
     * @Method("POST")
     *
     * @api for Geppetto Client
     */
    public function postAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $form = $this->createForm(PurchasingDataApiType::class);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                /* @var $purchData PurchasingData */
                $purchData = $form->getData();
                $this->dbm->persist($purchData);
                foreach ($purchData->getCostBreaks() as $costBreak) {
                    $costBreak->setPurchasingData($purchData);
                    $this->dbm->persist($costBreak);
                }
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            return View::create(null, Response::HTTP_CREATED);
        } else {
            return JsonResponse::fromInvalidForm($form);
        }
    }

    /**
     * @Route("/Purchasing/PurchasingData", name="Purchasing_PurchasingData_create")
     * @Template("purchasing/purchdata/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING_DATA);
        /* @var $stockItem StockItem */
        $stockItem = $this->needEntityFromRequest(StockItem::class, 'stockItem');

        $purchData = new PurchasingData($stockItem);
        return $this->processForm($purchData, $request, 'created');
    }

    /**
     * @Route("/purchasing/data/{id}/", name="purchasing_data_edit")
     * @Route("/Purchasing/PurchasingData/{id}", name="Purchasing_PurchasingData_edit", options={"expose": true})
     * @Template("purchasing/purchdata/edit.html.twig")
     */
    public function editAction(PurchasingData $purchData, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING_DATA);
        return $this->processForm($purchData, $request, 'updated');
    }

    private function processForm(
        PurchasingData $purchData,
        Request $request,
        $updated)
    {
        $returnUri = $this->generateUrl('purchasing_data_list', [
            'stockItem' => $purchData->getSku(),
        ]);

        $form = $this->createForm(EditType::class, $purchData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->get('syncLevel') || $request->get('syncAll')) {
                /** @var $sync PurchasingDataSynchronizer */
                $sync = $this->get(PurchasingDataSynchronizer::class);
                $error = $request->get('syncAll')
                    ? $sync->updateAllFields($purchData)
                    : $sync->updateStockLevel($purchData);
                if ($error) {
                    $this->logWarning($error);
                }
                $returnUri = $this->generateUrl('purchasing_data_edit', [
                    'id' => $purchData->getId(),
                ]);
            }
            $this->dbm->beginTransaction();
            try {
                $this->dbm->persist($purchData);
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Purchasing data $updated successfully.");
            return $this->redirect($returnUri);
        }

        /** @var $apiRepo SupplierApiRepository */
        $apiRepo = $this->getRepository(SupplierApi::class);

        return [
            'purchData' => $purchData,
            'form' => $form->createView(),
            'cancelUri' => $returnUri,
            'canSync' => $apiRepo->canSyncViaApi($purchData),
        ];
    }

    /**
     * @Route("/purchasing/data/{id}/preferred/", name="purchasing_data_preferred")
     * @Method("PUT")
     */
    public function setPreferredAction(PurchasingData $pd)
    {
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::ENGINEER]);
        /** @var $validator ValidatorInterface */
        $validator = $this->get(ValidatorInterface::class);
        $errors = $validator->validate($pd);
        if (count($errors) > 0) {
            $this->logErrors($errors);
        } else {
            $repo = $this->getRepo();
            $repo->setPreferred($pd);
        }

        return $this->redirectToRoute('purchasing_data_list', [
            'stockItem' => $pd->getSku(),
        ]);
    }

    /**
     * @Route("/Purchasing/Supplier/{id}/uploadQuotation", name="Purchasing_PurchasingData_upload")
     */
    public function uploadAction(Supplier $supplier, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $cancelUri = $this->generateUrl('supplier_view', [
            'supplier' => $supplier->getId(),
        ]);

        $uploadForm = $this->createForm(QuotationCsvUploadType::class);

        if ($request->isMethod('POST')) {
            if ($request->get("Confirm")) {
                $mapping = new QuotationCsvMapping($supplier);
                $mappingForm = $this->createForm(QuotationCsvMappingType::class, $mapping);
                $mappingForm->handleRequest($request);
                if ($mappingForm->isValid()) {
                    $this->dbm->beginTransaction();
                    try {
                        $purchDataRecords = $mapping->createPurchasingData($this->dbm);
                        foreach ($purchDataRecords as $purchData) {
                            $this->dbm->persist($purchData);
                        }
                        $this->dbm->flushAndCommit();
                        $this->logWarnings($mapping->getWarnings());
                    } catch (Exception $ex) {
                        $this->dbm->rollBack();
                        throw $ex;
                    }
                    $this->logNotice("Quotation uploaded successfully.");
                    return $this->render('purchasing/purchdata/csv-confirm.html.twig', [
                        'list' => $purchDataRecords,
                        'supplier' => $supplier,
                        'quoteNo' => $mapping->getQuotationNumber(),
                    ]);
                } else {
                    return $this->render('purchasing/purchdata/csv-map.html.twig', [
                        'form' => $mappingForm->createView(),
                        'cancelUri' => $cancelUri,
                        'supplier' => $supplier,
                        'quoteNo' => $mapping->getQuotationNumber(),
                    ]);
                }
            } else {
                $uploadForm->handleRequest($request);
                if ($uploadForm->isValid()) {
                    $data = $uploadForm->getData();
                    $csvFile = new CsvFile();
                    $csvFile->setDelimiter($data['delimiter']);
                    $csvFile->parseFile($data['file']);
                    $mapping = new QuotationCsvMapping($supplier);
                    $mapping->setQuotationNumber($data['quotationNumber']);
                    $mapping->setCsvFile($csvFile);

                    $mappingForm = $this->createForm(QuotationCsvMappingType::class, $mapping);
                    return $this->render('purchasing/purchdata/csv-map.html.twig', [
                        'form' => $mappingForm->createView(),
                        'cancelUri' => $cancelUri,
                        'supplier' => $supplier,
                        'quoteNo' => $mapping->getQuotationNumber(),
                    ]);
                }
            }
        }

        return $this->render('purchasing/purchdata/csv-upload.html.twig', [
            'form' => $uploadForm->createView(),
            'heading' => sprintf('Upload %s quotation', $supplier->getName()),
            'cancelUri' => $cancelUri,
        ]);
    }

    /**
     * Display/load the latest stock level data from the supplier's API,
     * if possible.
     *
     * @Route("/purchasing/data/{id}/stocklevel/",
     *     name="purchasing_data_stocklevel")
     * @Template("purchasing/purchdata/stockLevel.html.twig")
     */
    public function stockLevelAction(PurchasingData $purchData, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING_DATA);
        $formAction = $this->generateUrl('purchasing_data_stocklevel', [
            'id' => $purchData->getId(),
        ]);
        $error = null;
        if ($request->isMethod('post')) {
            /** @var $sync PurchasingDataSynchronizer */
            $sync = $this->get(PurchasingDataSynchronizer::class);
            $error = $sync->forceUpdateStockLevel($purchData);
            $this->dbm->flush();
        }
        return [
            'purchData' => $purchData,
            'formAction' => $formAction,
            'error' => $error,
        ];
    }

    /**
     * @Route("/Purchasing/PurchasingData/{id}/clone", name="Purchasing_PurchasingData_clone")
     * @Method("POST")
     */
    public function cloneAction(PurchasingData $original, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING_DATA);
        $new = clone $original;
        if ($original->isManufactured()) {
            $location = $this->needEntityFromRequest(Facility::class, 'location');
            $new->setBuildLocation($location);
        } else {
            $supplier = $this->needEntityFromRequest(Supplier::class, 'supplier');
            $new->setSupplier($supplier);
        }

        $this->dbm->beginTransaction();
        try {
            $this->dbm->persist($new);
            $this->dbm->flushAndCommit();
        } catch (Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        $this->logNotice(sprintf("%s catalog item %s successfully cloned from %s.",
            $new->getSupplierName(),
            $new->getCatalogNumber(),
            $original->getSupplierName()
        ));
        $uri = $this->generateUrl('purchasing_data_edit', [
            'id' => $new->getId(),
        ]);
        return JsonResponse::javascriptRedirect($uri);
    }

    /**
     * @Route("/record/Purchasing/PurchasingData/{id}/",
     *   name="Purchasing_PurchasingData_delete")
     * @Method("DELETE")
     */
    public function deleteAction(PurchasingData $purchData)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $repo = $this->getRepo();
        if ($repo->hasBeenUsed($purchData)) {
            $msg = "Cannot delete $purchData because it has been used." .
                " You can set the end-of-life date instead.";
            $this->logError($msg);
            $editUrl = $this->generateUrl('Purchasing_PurchasingData_edit', [
                'id' => $purchData->getId(),
            ]);
            return $this->redirect($editUrl);
        }

        $stockCode = $purchData->getSku();
        $this->dbm->remove($purchData);
        $this->dbm->flush();
        $this->logNotice("Deleted $purchData successfully.");
        return $this->redirectToRoute('purchasing_data_list', [
            'stockItem' => $stockCode,
        ]);
    }
}
