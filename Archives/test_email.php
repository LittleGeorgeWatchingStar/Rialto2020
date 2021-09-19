<?php
$_POST['IgnoreTitle'] = 1;
use Rialto\PurchasingBundle\Entity\Supplier;
use Rialto\PurchasingBundle\Entity\PurchaseOrder;
use Rialto\StockBundle\Entity\StockItem;
$PageSecurity = 10;

include ('includes/session.inc');

//
//	This should post data to some script named SuppInvoiceDetailsPost or posts/suppInvoiceDetails
//
//
//	take data from an invoice email and post it to and editable form
//
//	then posting it using xhr to the posts/suppInvoiceDetails will put it into the DB
//	--> both views/*** and posts/*** use the same object classes
//	--> the class needs to be able to create an object from either the email it reads[ i.e. the file] or from the form
//


function main(array $request)
{
    /* Load request data */
    if ( empty($request['SupplierNo']) ) die("Invalid request");
    if ( empty($request['FileName']) ) die("Invalid request");

    require_once 'gumstix/erp/mappers/purchasing/SupplierMapper.php';
    $supplierM = new SupplierMapper();
    $supplier = $supplierM->find($request['SupplierNo']);
    if (! $supplier ) die("No such supplier {$request['SupplierNo']}.");
    $file_name = $request['FileName'];

    /* Parse the selected invoice */
    require_once 'gumstix/erp/mappers/purchasing/SupplierInvoiceParserPdf.php';
    $invoiceParser = new SupplierInvoiceParserPdf($supplier);

    $rel_file_path = $invoiceParser->getInvoiceFilePath($file_name);
    if (! defined('SITE_FS_WEBERP_PUBLIC') ) {
        die("'SITE_FS_WEBERP_PUBLIC' is not defined");
    }
    $abs_file_path = SITE_FS_WEBERP_PUBLIC . $rel_file_path;
    if (! is_file($abs_file_path) ) die("No such invoice $abs_file_path");
    if (! $invoiceParser->isValidFile($abs_file_path) ) {
        die("$abs_file_path does not appear to be a valid PDF file.");
    }

    try {
        $invoiceParser->parse($abs_file_path);
    }
    catch ( SupplierInvoiceParserException $ex ) {
        die( $ex->getMessage() );
    }
    $purchOrders = $invoiceParser->getPurchaseOrders();

    /* Set up the view */
    require_once 'Zend/View.php';
    $view = new Zend_View();
    require_once 'Zend/Dojo.php';
    Zend_Dojo::enableView($view);

    $view->pdfUri = $rel_file_path;
    $view->csvUri = str_ireplace('.pdf', '.csv', $rel_file_path);

    $view->setScriptPath('Test/erp/includes/views/');
    $view->addHelperPath('gumstix/erp/views/helpers', 'Weberp_View_Helper');
    $view->doctype('HTML4_STRICT');

    $view->dojo()->enable();
    $view->dojo()->setDjConfigOption('parseOnLoad', true);
    $view->dojo()->requireModule('dojo.parser');

    $view->dojo()->requireModule("dijit.form.Button");
    $view->dojo()->requireModule("dijit.form.ComboBox");
    $view->dojo()->requireModule('dojo.number');
    $view->dojo()->requireModule("dojox.data.HtmlStore");
    $view->dojo()->requireModule("dojox.grid.DataGrid");

    $view->orders = array();
    foreach ( $purchOrders as $po ) {
        $orderView = new Zend_View();
        $orderView->order = $po;
        $orderView->oid = $po->getId();
        $form = getForm($supplier, $po);
        $orderView->form = $form;

        foreach ( $invoiceParser->getHeaderArray($po) as $key => $descriptors) {
            $form->addElement(
                'Text', 'inv_' . $key, array(
                'label' => $key,
                'value' => $descriptors,
                'autocomplete' => false
                )
            );
        }

        $orderView->grandTotal = 0;
        $invoice_table = array();
        foreach ( $invoiceParser->getLineItemArray($po) as $line_item) {
            $key = $line_item['LineNumber'];
            if (! $key ) {
                $desc = empty($line_item['Description'])
                    ? $line_item['StockItem']
                    : $line_item['Description'];
                die("Unable to determine line number for line item $desc.");
            }
            $invoice_table[$key] = $line_item;
            $invoice_table[$key]['json_key'] = $key;
            if ( isset($line_item['ExtendedCost']) ) {
                $orderView->grandTotal += $line_item['ExtendedCost'];
            }
        }
        $orderView->columns = $invoiceParser->getValidKeys();
        array_unshift($orderView->columns, 'json_key');

        require_once 'Zend/Dojo/Data.php';
        $tableObj = new Zend_Dojo_Data('json_key', $invoice_table);
        $tableObj->setLabel('json_key');
        $orderView->lineItems = $invoice_table;

        $form->addElement(
            'Button', 'InsertButton', array(
                'label' => 'Approved',
                'onclick' => sprintf('dojo.xhrPost({
                    form: dojo.byId("%s"),
                    load: function(data) { alert(data);},
                    handleAs: "text"
                })', $form->getAttrib('id')),
                'id' => 'InsertButton_'. $po->getId()
            )
        );

        $form->addElement(
            'Hidden', 'tbl_', array(
            'value' => $tableObj->toJson(),
            'autocomplete' => false
            )
        );

        $form->setView($view);
        $view->orders[] = $orderView;
    }
    return $view->render('test_email_view.php');
}


function getForm(Supplier $supplier, PurchaseOrder $purchOrder)
{
    require_once 'Zend/Dojo/Form.php';
    $form = new Zend_Dojo_Form();
    Zend_Dojo::enableForm($form);

    $form->setMethod('POST');
    $form->setAttribs(array(
        'id' => 'masterForm_'. $purchOrder->getId(),
        'name' => 'masterForm',
        'action' => '../../test_email_post.php'
    ));

    $form->addElement(
        'Text', 'supplier_no', array(
        'label' => 'supplier_no',
        'value' => $supplier->getId(),
        'autocomplete' => false
        )
    );

    $form->addElement(
        'Text', 'po_no', array(
        'label' => 'po_no',
        'value' => $purchOrder->getId(),
        'autocomplete' => false
        )
    );

    $form->setElementDecorators(
        array(
            'DijitElement',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
            array('Label', array('tag' => 'td')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
        )
    );
    $form->setDecorators(
        array(
            'FormElements',
            array(array('data' => 'HtmlTag'),
                array('tag' => 'table', 'cellspacing' => '4')),
            'DijitForm'
        )
    );
    return $form;
}

echo main($_GET);
