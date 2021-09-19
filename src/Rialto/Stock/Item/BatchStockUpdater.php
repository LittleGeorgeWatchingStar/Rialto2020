<?php

namespace Rialto\Stock\Item;

use InvalidArgumentException;
use LogicException;
use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Item\Orm\StockItemRepository;

/**
 * Service that updates many stock items at once.
 */
class BatchStockUpdater
{
    /** @var DbManager */
    private $dbm;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
    }

    /**
     * @return string[]
     *  A list of fields from the StockItem class that this class can update.
     */
    public static function getFields()
    {
        return [
            // label => value
            'ECCN code' => 'EccnCode',
            'Harmonization code' => 'HarmonizationCode',
            'Weight (g)' => 'Weight',
            'Package' => 'Package',
            'Discontinued' => 'Discontinued',
        ];
    }

    public function getInitialValues($searchString, $fieldToUpdate)
    {
        if (! trim($searchString) ) return [];
        /** @var $repo StockItemRepository */
        $repo = $this->dbm->getRepository(StockItem::class);
        $items = $repo->findMatchingItems($searchString);
        $data = [];
        foreach ( $items as $item ) {
            $method = "get$fieldToUpdate";
            $value = $item->$method();
            $data[ $item->getSku() ] = $value;
        }
        return $data;
    }

    /**
     * @param string $fieldToUpdate
     *  The name of the field to update.
     * @param string[] $values
     *  An array of new values for that field, keyed by stock ID.
     */
    public function update($fieldToUpdate, array $values)
    {
        $validFields = $this->getFields();
        if (! isset($validFields[$fieldToUpdate]) ) {
            throw new InvalidArgumentException("$fieldToUpdate is not a valid field");
        }
        foreach ( $values as $stockId => $value ) {
            $item = $this->dbm->need(StockItem::class, $stockId);
            $setMethod = "set$fieldToUpdate";
            if (! method_exists($item, $setMethod)) {
                throw new LogicException(sprintf(
                    "Class '%s' has no method %s",
                    get_class($item),
                    $setMethod
                ));
            }
            $item->$setMethod($value);
        }
    }
}
