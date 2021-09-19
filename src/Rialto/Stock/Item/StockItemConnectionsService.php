<?php

namespace Rialto\Stock\Item;

use Symfony\Component\Validator\Constraints as Assert;

class StockItemConnectionsService
{
    /**
     * @var StockItem
     */
    private $connectFrom;

    /**
     * @var StockItem
     * @Assert\NotBlank(message="Connections cannot be blank.")
     */
    private $connectTo = null;

    public function __construct(StockItem $connectFrom)
    {
        $this->connectFrom = $connectFrom;
    }

    public function getStockItem()
    {
        return $this->connectFrom;
    }

    public function getConnector()
    {
        return $this->connectTo;
    }

    public function setConnector(StockItem $connectTo = null)
    {
        if ( ! $connectTo ) return;
        $this->connectTo = $connectTo;
    }

    public function deleteConnector(StockItem $item)
    {
        $this->connectFrom->deleteConnector($item);
    }

    public function addConnector()
    {
        if($this->connectTo){
            $this->connectFrom->addConnector($this->connectTo);
        }
    }
}
