<?php

namespace Rialto\Geppetto\Design;

use Doctrine\ORM\EntityManagerInterface;
use Rialto\Measurement\Dimensions;
use Rialto\Measurement\Units;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\RoHS;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\StockItemTemplate;
use Rialto\Stock\Item\Version\ItemVersionTemplate;
use Rialto\Tax\Authority\TaxAuthority;

/**
 * Service to help create stock record templates for Geppetto design revisions.
 */
class DesignStockItemTemplateFactory
{
    /** @var EntityManagerInterface  */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createPcbTemplate(DesignRevision2 $designRevision): StockItemTemplate
    {
        $template = new StockItemTemplate();
        $template->pattern = 'PCB9' . str_repeat('#', 11);
        $template->mbFlag = StockItem::PURCHASED;
        $template->category = StockCategory::fetchPCB($this->em);
        $template->initialVersion = $this->createPcbVersionTemplate($designRevision);
        $template->name = "PCB for ". $designRevision->getDesignName();
        $template->longDescription = $designRevision->getDesignDescription();
        $template->units = Units::each();
        $template->rohs = RoHS::COMPLIANT;
        $template->taxAuthority = TaxAuthority::fetchCaStateTax($this->em);
        return $template;
    }

    public function createBoardTemplate(DesignRevision2 $designRevision): StockItemTemplate
    {
        $template = new StockItemTemplate();
        $template->mbFlag = StockItem::MANUFACTURED;
        $template->category = StockCategory::fetchBoard($this->em);
        $template->initialVersion = $this->createBoardVersionTemplate($designRevision);
        $template->name = $designRevision->getDesignName();
        $template->longDescription = $designRevision->getDesignDescription();
        $template->units = Units::each();
        $template->rohs = RoHS::COMPLIANT;
        $template->taxAuthority = TaxAuthority::fetchCaStateTax($this->em);
        return $template;
    }

    /**
     * @deprecated CAD stock item no longer used
     */
    public function createCadTemplate(DesignRevision2 $designRevision): StockItemTemplate
    {
        $template = new StockItemTemplate();
        $template->mbFlag = StockItem::DUMMY; // TODO: mbFlag for virtual products (currently PURCHASES require weight).
        $template->category = StockCategory::fetchSoftware($this->em);
        $template->name = "CAD files for ". $designRevision->getDesignName();
        $template->longDescription = $designRevision->getDesignDescription();
        $template->units = Units::each();
        $template->rohs = RoHS::COMPLIANT; // TODO: confirm RoHS
        $template->taxAuthority = TaxAuthority::fetchCaStateTax($this->em);
        return $template;
    }

    public function createPcbVersionTemplate(DesignRevision2 $designRevision): ItemVersionTemplate
    {
        $vt = new ItemVersionTemplate();
        $vt->setVersionCode($designRevision->getVersionCode());
        $vt->setDimensions($designRevision->getPcbDimensions());
        $vt->setWeight($designRevision->getPcbWeight());
        $vt->setAutoBuildVersion(true);
        $vt->setShippingVersion(true);
        return $vt;
    }

    public function createBoardVersionTemplate(DesignRevision2 $designRevision): ItemVersionTemplate
    {
        $vt = new ItemVersionTemplate();
        $vt->setVersionCode($designRevision->getVersionCode());
        $vt->setDimensions($designRevision->getBoardDimensions());
        $vt->setAutoBuildVersion(true);
        $vt->setShippingVersion(true);
        return $vt;
    }

    /**
     * @deprecated CAD stock item no longer used
     */
    protected function createCadVersionTemplate(DesignRevision2 $designRevision): ItemVersionTemplate
    {
        $vt = new ItemVersionTemplate();
        $vt->setVersionCode($designRevision->getVersionCode());
        $vt->setDimensions(new Dimensions(0, 0, 0));
//        $vt->setAutoBuildVersion(true); // TODO: confirm this.
//        $vt->setShippingVersion(true); // TODO: confirm this.
        return $vt;
    }
}