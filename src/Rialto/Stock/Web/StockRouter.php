<?php

namespace Rialto\Stock\Web;

use Rialto\Accounting\Transaction\Transaction;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Transfer\Transfer;
use Symfony\Component\Routing\RouterInterface;


/**
 * Generates URLs for commonly-used stock entities.
 */
class StockRouter
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function itemView(Item $item)
    {
        return $this->router->generate('stock_item_view', [
            'item' => $item->getSku(),
        ]);
    }

    public function binView(StockBin $bin)
    {
        return $this->router->generate('stock_bin_view', [
            'bin' => $bin->getId()
        ]);
    }

    public function facilityView(Facility $facility)
    {
        return $this->router->generate('stock_facility_list', [
            'facility' => $facility->getId()
        ]);
    }

    public function transferView(Transfer $transfer)
    {
        return $this->router->generate('stock_transfer_view', [
            'transfer' => $transfer->getId()
        ]);
    }

    public function transactionView(Transaction $trans)
    {
        return $this->router->generate('transaction_view', [
            'trans' => $trans->getId(),
        ]);
    }
}
