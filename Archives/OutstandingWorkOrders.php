<?php

use Rialto\AllocationBundle\Entity\StockAllocation;
use Rialto\ManufacturingBundle\Entity\WorkOrder;
use Rialto\ManufacturingBundle\Service\ManufacturingFilesystem;
use Rialto\StockBundle\Entity\Location;
use Rialto\StockBundle\Entity\StockItem;
use Doctrine\ORM\EntityNotFoundException;

$PageSecurity = 11;

require_once 'includes/session.inc';
require_once 'gumstix/erp/tools/PageAbstract.php';

class OutstandingWorkOrdersPage
extends PageAbstract
{
    const DIJIT_THEME = 'tundra';

    /** @var WorkOrderMapper */
    private $woMapper;

    /** @var string */
    private $title = 'Work orders';

    /** @var array */
    private $allLocations;

    /** @var Location */
    private $location = null;

    /** @var StockItem */
    private $item = null;

    /** @var array */
    private $orders = array();

    public function getTitle()
    {
        return $this->title;
    }

    protected function init(array $get)
    {
        $this->useDojo();
        $this->appendStylesheet('pages/OutstandingWorkOrders.css');

        $this->woMapper = $this->dbm->getMapper('manufacturing\WorkOrder');

        $slm = $this->dbm->getMapper('stock\Location');
        $this->allLocations = $slm->findActive();

        if (! empty($get['workOrder']) ) {
            $match = $this->woMapper->find($get['workOrder']);
            if (! $match ) {
                $this->logError("No such work order {$get['workOrder']}.");
                return;
            }
            $this->location = $match->getLocation();
            $this->item = $match->getStockItem();
            $this->orders[] = $match;
            return;
        }

        if (! empty( $get['location'] ) ) {
            $this->location = $slm->find($get['location']);
            if (! $this->location ) $this->logError('Invalid request');
        }

        if (! empty($get['stockItem']) ) {
            $this->item = $this->dbm->find('stock\StockItem', $get['stockItem']);
        }

        if ( $this->location || $this->item ) {
            if ( empty($get['_limit']) ) {
                $get['_limit'] = 50;
            }
            $this->orders = $this->woMapper->findByFilters($get);
        }
    }

    private function getCsvForm(array $request)
    {
        $location = isset($request['Location']) ? $request['Location'] : null;

        require_once 'gumstix/erp/forms/WeberpForm.php';
        $form = new WeberpForm();
        $currentUrl = $this->getCurrentUri();
        $form->setMethod('GET');
        $form->setAttrib('target', "$currentUrl");
        $form->setAttrib('class', 'standard inline');
        $form->setAttrib('id', 'csvForm');

        $form->addElement('hidden', 'Location')
             ->setValue($location);

        $form->addElement('Submit', 'GenerateCSVFile')
             ->setLabel("Generate CSV File");

        return $form;
    }

    private function getForm(array $request)
    {
        require_once 'gumstix/erp/forms/WeberpForm.php';
        $form = new WeberpForm();
        $form->setMethod('GET');
        $form->setAttrib('class', 'standard inline');
        $form->setAttrib('id', 'filterForm');
        foreach ( $this->allLocations as $location ) {
            $name = sprintf('Loc_%s', $location->getId());
            $uri = $this->getUri(array(
                'location' => $location->getId(),
            ));
            $locBtn = $form->addElement('Button', $name)
                ->setLabel($location->getName())
                ->setAttrib('onclick', "window.location='$uri';");
            if ( $location == $this->location ) {
                $locBtn->setAttrib('disabled', true);
            }
        }

        require_once 'gumstix/erp/forms/elements/HtmlChunk.php';
        $form->addElement( new HtmlChunk('breakButtons', '<br/>') );

        $form->addElement('Text', 'workOrder')
            ->setLabel('Order #');
        $locSel = $form->addElement('Select', 'location')
            ->setLabel('at location')
            ->addMultiOption('', '-- select --');
        foreach ( $this->allLocations as $loc ) {
            $locSel->addMultiOption($loc->getId(), $loc->getName());
        }
        require_once 'gumstix/erp/forms/elements/SelectStockItemElement.php';
        $form->addElement( new SelectStockItemElement('stockItem', $form) )
            ->setLabel('for item');
        $form->addElement('Select', 'orderBy')
            ->setLabel('order by')
            ->addMultiOptions(array(
                'status' => 'status',
                'StockID' => 'item',
                'WORef' => 'order #'
            ));
        $form->addElement( new HtmlChunk('breakCheckboxes', '<br/>') );
        $form->addElement('Checkbox', 'closed')
            ->setLabel('show completed');
        $form->addElement('Checkbox', 'overdue')
            ->setLabel('only overdue');
        $form->addElement('Checkbox', 'parents')
            ->setLabel('show parents');
        $form->addElement('Checkbox', 'rework')
            ->setLabel('rework orders');
        $form->addElement('Submit', 'Search');

        $form->populate($request);
        return $form;
    }

    private function processPost(array $post)
    {
        $this->dbm->beginTransaction();
        try {
            if (! empty($post['OpenForAllocation']) ) {
                $this->updateOpenForAllocation($post);
            }
            if (! empty($post['Parent']) ) {
                $this->updateParent($post);
            }
            $this->dbm->commit();
            $this->logMessage('Work orders updated successfully.');
        }
        catch ( Exception $ex ) {
            $this->dbm->rollBack();
            $this->logException($ex);
        }
    }

    private function updateOpenForAllocation($post)
    {
        foreach ( $post['OpenForAllocation'] as $woId => $isOpen ) {
            $wo = $this->woMapper->need($woId);
            $wo->setOpenForAllocation( $isOpen );
        }
    }

    private function updateParent($post)
    {
        foreach ( $post['Parent'] as $woId => $parentId ) {
            if (! $parentId ) continue;
            $wo = $this->woMapper->need($woId);
            $parent = $this->woMapper->need($parentId);
            $wo->setParent( $parent );
        }
    }

    protected function getBodyClass()
    {
        return self::DIJIT_THEME;
    }

    protected function renderBody()
    {
        if ( $this->isPost() ) $this->processPost($_POST);

        $view = $this->getView();

        $this->useDojo();
        $view->dojo()->addStylesheetModule('dijit.themes.'. self::DIJIT_THEME)
            ->setDjConfigOption('parseOnLoad', true)
            ->requireModule('dijit.form.DateTextBox');

        $form = $this->getForm($_GET);
        $csvForm = $this->getCsvForm($_GET);

        if (isset($_GET['GenerateCSVFile'])) {
            $location = $_GET['Location'];
            $currentUrl = $this->getUri(array('Location' => $location));

            // $currentUrl = $this->getCurrentUri(array("GenerateCSVFile" => null));
            logDebug($currentUrl, "form processingUrl");
            $this->renderCsv();
            exit;
        }

        if ( empty($this->orders) ) {
            return $form->render($view);
        }

        $view->orders = array();
        require_once 'gumstix/erp/filesystems/IconFilesystem.php';
        $icons = new IconFilesystem(
            IconFilesystem::THEME_OXYGEN,
            IconFilesystem::SIZE_16
        );
        $manFs = new ManufacturingFilesystem(RIALTO_FS_ROOT);
        require_once 'gumstix/erp/tools/ErpUri.php';
        foreach ( $this->orders as $order ) {
            $woView = $this->getView();
            $woView->wo = $order;
            try {
                $woView->item = $order->getStockItem();
            }
            catch ( EntityNotFoundException $ex ) {
                $this->logWarning(sprintf(
                    'Order %s has an invalid stock item: %s',
                    $order->getId(), $ex->getKey()
                ));
                continue;
            }

            /* Parent work order */
            if ( $order->hasParent() ) {
                $woView->parent = $order->getParent();
            }
            elseif ( $order->isReleased() ) {
                $woView->eligibleParents = array();
                $parents = $this->woMapper->findEligibleParents($order);
                if ( count($parents) > 0 ) {
                    $woView->eligibleParents[''] = '';
                    foreach ( $parents as $parent ) {
                        $woView->eligibleParents[ $parent->getId() ] = $parent->getId();
                    }
                }
            }

            /* Build instructions */
            $woView->insIcon = $icons->getUri('mimetypes/application-pdf.png');
            $woView->insUri = new ErpUri(
                "/index.php/Manufacturing/WorkOrder/{$order->getId()}/instructions"
            );


            /* Release */
            if (! $order->isReleased() ) {
                $woView->releaseUri = new ErpUri("/index.php/record/Manufacturing/WorkOrder/{$order->getId()}");
            }

            /* Purchase order */
            $woLocation = $order->getLocation();
            if ( $order->hasPurchaseOrder() ) {
                try {
                    $woView->purchOrder = $order->getPurchaseOrder();
                }
                catch ( EntityNotFoundException $ex ) {
                    $this->logException($ex);
                    continue;
                }
            }
            elseif (! $woLocation->hasSupplier() ) {
                if ( $order->isIssued() ) {
                    $woView->poText = 'sent';
                    $woView->poUri = null;
                }
                elseif ( $manFs->hasBuildInstructions($order) )
                {
                    $woView->poText = 'send';
                    $woView->poUri = new ErpUri(
                        "/index.php/Manufacturing/WorkOrder/{$order->getId()}/send"
                    );
                }
            }
            elseif ( ! $order->hasChild() ) {
                $woView->poText = 'new';
                $woView->poUri = new ErpUri(
                    "/index.php/Manufacturing/WorkOrder/{$order->getId()}/createPurchaseOrder"
                );
            }

            /* Allocations */
            if ( $order->hasRequestedAllocations() ) {
                $woView->allocList = new ErpUri('/index.php/record/Allocation/StockAllocation', array(
                    'workOrder' => $order->getId()
                ));
            }
            if ( $order->canAllocate() ) {
                $woView->allocHq = new ErpUri(
                    "/index.php/Manufacturing/WorkOrder/{$order->getId()}/allocate"
                );
                $woView->allocCm = new ErpUri(
                    "/index.php/Manufacturing/WorkOrder/{$order->getId()}/allocate",
                    array('fromCM' => 1)
                );
            }

            /* Issue */
            if ( $order->canBeIssued() && (! $order->hasChild()) ) {
                $woView->issueUri = new ErpUri('IssueWorkOrder.php', array(
                    'WORef' => $order->getId()
                ));
            }

            /* Receive */
            if ( $order->canBeReceived() ) {
                if ( $order->hasPurchaseOrder() ) {
                    $poID = $order->getPurchaseOrderNumber();
                    $woView->recvUri = new ErpUri(
                        "/index.php/Purchasing/PurchaseOrder/$poID/receive");
                }
                elseif ( $woLocation->isHeadquarters() ) {
                    $woView->recvUri = new ErpUri(
                        "/index.php/Manufacturing/WorkOrder/{$order->getId()}/receipt"
                    );
                }
            }

            $view->orders[] = $woView;
        }
        $view->searchForm = $form;
        $view->csvForm = $csvForm;

        return $view->render('OutstandingWorkOrdersView.php');
    }

    private function renderCsv()
    {
        $commaSeparatedOrders = $this->getWorkOrdersAsCsv();

        header('Expires: 0');
        header('Cache-control: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-disposition: attachment; filename="OutstandingWorkOrders.csv"');

        echo $commaSeparatedOrders;
    }

    private function getWorkOrdersAsCsv()
    {
        $csvHeaders = array("Reference", "Item", "Version", "Description", "Package",
            "Ordered", "Order", "Commitment", "Issued", "Built");

        $ordersList = array();
        $ordersList[] = implode(",", $csvHeaders);

        foreach ($this->orders as $order) {
            $stockItem = $order->getStockItem();
            $row = array();
            $row[] = $order->getId();
            $row[] = $order->getStockCode();
            $row[] = $order->getVersion();
            $row[] = $stockItem->getDescription();
            $row[] = $order->getParentId();
            $row[] = $order->getQtyOrdered();
            $row[] = $order->getPurchaseOrderNumber();
            $row[] = $order->getCommitmentDate();
            $row[] = $order->getQtyIssued();
            $row[] = $order->getQtyReceived();

            $ordersList[] = implode(",", $row);
        }

        return implode("\r\n", $ordersList);
    }
}

$page = new OutstandingWorkOrdersPage();
echo $page->render();
