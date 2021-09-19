<?php

namespace Rialto\Util\Collection;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Sorts a list of objects according to the properties given to the constructor.
 */
class ObjectSorter
{
    /** @var string[] */
    private $fields = [];

    private $accessor;

    /**
     * @param string|string[] $fields
     */
    public function __construct($fields)
    {
        $this->initFields($fields);
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    private function initFields($fields)
    {
        if (! is_array($fields) ) {
            $fields = explode(',', $fields);
        }
        foreach ( $fields as $field ) {
            if (! $field ) continue;
            $this->initField($field);
        }
    }

    private function initField($field)
    {
        $initial = substr($field, 0, 1);
        logDebug("Initial of $field is $initial.");
        switch ( $initial ) {
            case "+":
            case " ": // "+" character in URL is replaced with space
                $field = substr($field, 1);
                $direction = 1;
                break;
            case "-":
                $field = substr($field, 1);
                $direction = -1;
                break;
            default:
                $direction = 1;
                break;
        }
        $this->fields[$field] = $direction;
    }

    public function __invoke($a, $b)
    {
        return $this->compare($a, $b);
    }

    public function compare($a, $b)
    {
        $result = 0;
        foreach ( $this->fields as $field => $direction ) {
            if (! $field ) continue;
            $aVal = $this->accessor->getValue($a, $field);
            $bVal = $this->accessor->getValue($b, $field);
            $result = ( $aVal < $bVal ) ? -1 :
                ( ($aVal > $bVal) ? 1 : 0 );
            $result *= $direction;
            if ( $result != 0 ) break;
        }
        return $result;
    }
}
