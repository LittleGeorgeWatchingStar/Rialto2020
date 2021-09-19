<?php

namespace Rialto\Geppetto\Design;

use Rialto\Database\Orm\DbManager;
use Rialto\Measurement\Units;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\RoHS;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\StockItemTemplate;
use Rialto\Tax\Authority\TaxAuthority;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @deprecated
 * use @see DesignRevision2
 *
 * A Geppetto design.
 */
class Design extends DesignAbstract
{
    /**
     * @var string
     * @Assert\NotBlank(message="Design name is required.")
     * @Assert\Length(max=255,
     *   maxMessage="Design name cannot be longer than {{ limit }} characters.")
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank(message="Design description is required.")
     */
    public $description;

    /**
     * @var string
     * @Assert\Url(message="Invalid permalink URL '{{ value }}'.")
     */
    public $permalink;

    /**
     * This field is populated with the PCB by the DesignFactory.
     * @var StockItem
     */
    public $pcb = null;

    /**
     * This field is populated with the board by the DesignFactory.
     * @var StockItem
     */
    public $board = null;

    /**
     * This field is populated with the CAD by the DesignFactory.
     * @var StockItem
     */
    public $cad = null;

    /** @return StockItemTemplate */
    public function createPcbTemplate(DbManager $dbm)
    {
        $template = new StockItemTemplate();
        $template->pattern = 'PCB9' . str_repeat('#', 11);
        $template->mbFlag = StockItem::PURCHASED;
        $template->category = StockCategory::fetchPCB($dbm);
        $template->initialVersion = $this->createPcbVersionTemplate();
        $template->name = "PCB for ". $this->name;
        $template->longDescription = $this->description;
        $template->units = Units::each();
        $template->rohs = RoHS::COMPLIANT;
        $template->taxAuthority = TaxAuthority::fetchCaStateTax($dbm);
        return $template;
    }

    /** @return StockItemTemplate */
    public function createBoardTemplate(DbManager $dbm)
    {
        $template = new StockItemTemplate();
        $template->mbFlag = StockItem::MANUFACTURED;
        $template->category = StockCategory::fetchBoard($dbm);
        $template->initialVersion = $this->createBoardVersionTemplate();
        $template->name = $this->name;
        $template->longDescription = $this->description;
        $template->units = Units::each();
        $template->rohs = RoHS::COMPLIANT;
        $template->taxAuthority = TaxAuthority::fetchCaStateTax($dbm);
        return $template;
    }

    /** @return StockItemTemplate */
    public function createCadTemplate(DbManager $dbm)
    {
        $template = new StockItemTemplate();
        $template->mbFlag = StockItem::DUMMY; // TODO: mbFlag for virtual products (currently PURCHASES require weight).
        $template->category = StockCategory::fetchSoftware($dbm);
        $template->name = "CAD files for ". $this->name;
        $template->longDescription = $this->description;
        $template->units = Units::each();
        $template->rohs = RoHS::COMPLIANT; // TODO: confirm RoHS
        $template->taxAuthority = TaxAuthority::fetchCaStateTax($dbm);
        return $template;
    }
}
