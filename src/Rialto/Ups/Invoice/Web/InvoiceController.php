<?php

namespace Rialto\Ups\Invoice\Web;

use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Invoice\SupplierInvoiceFilesystem;
use Rialto\Security\Role\Role;
use Rialto\Ups\Invoice\InvoiceLoader;
use Rialto\Ups\Invoice\InvoiceParseException;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class InvoiceController extends RialtoController
{
    /**
     * List UPS invoices.
     *
     * @Route("/ups/invoice/", name="ups_invoice_list")
     * @Method("GET")
     * @Template("ups/invoice/list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $files = [];
        try {
            $loader = $this->getLoader();
            $files = $loader->listFiles();
        } catch (\RuntimeException $ex) {
            $this->logException($ex);
        } catch (\ErrorException $ex) {
            $this->logException($ex);
        }
        return [
            'files' => $files,
        ];
    }

    /** @return InvoiceLoader|object */
    private function getLoader()
    {
        return $this->get(InvoiceLoader::class);
    }

    /**
     * @Route("/ups/invoice/{filename}/", name="ups_invoice_parse")
     * @Method("GET")
     * @Template("ups/invoice/parse.html.twig")
     */
    public function parseAction($filename)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $loader = $this->getLoader();
        $xmlstring = $loader->getInvoice($filename);
        $parser = $loader->getParser($filename);
        try {
            $invoices = $parser->parseInvoices($xmlstring);
            $shipments = $parser->parseShipments($xmlstring);
        } catch (InvoiceParseException $ex) {
            $this->logException($ex);
            return $this->redirectToRoute('ups_invoice_list');
        }
        return [
            'filename' => $filename,
            'invoices' => $invoices,
            'shipments' => $shipments,
        ];
    }

    /**
     * @Route("/ups/invoice/{filename}/{invoiceNo}/", name="ups_invoice_save")
     * @Method("POST")
     */
    public function saveAction($filename, $invoiceNo)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $loader = $this->getLoader();
        $xmlstring = $loader->getInvoice($filename);
        $parser = $loader->getParser($filename);
        $invoices = $parser->parseInvoices($xmlstring);
        $invoice = $invoices[$invoiceNo];
        $this->dbm->beginTransaction();
        try {
            $invoice->prepare();
            $this->saveInvoiceFile($invoice, $filename, $xmlstring);
            $this->dbm->persist($invoice);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
        return $this->redirectToRoute('supplier_invoice_view', [
            'id' => $invoice->getId(),
        ]);
    }

    private function saveInvoiceFile(SupplierInvoice $invoice, $filename, $xmlstring)
    {
        /** @var $fs SupplierInvoiceFilesystem */
        $fs = $this->get(SupplierInvoiceFilesystem::class);
        $fileinfo = $fs->saveInvoice($invoice->getSupplier(), $filename, $xmlstring);
        $invoice->setFilename($fileinfo->getBasename());
    }
}
