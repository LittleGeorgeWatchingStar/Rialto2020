<?php

namespace Rialto\Database\Orm;

/**
 * @author Ian Phillips <ian@gumstix.com>
 */
class DbKeyException extends \Exception
{
    private $keyVals;

    public function __construct($tableName, $keyVals)
    {
        $this->setKey($keyVals);

        parent::__construct(sprintf("No such row '%s' in table %s",
            $this->getKey(), $tableName
        ));
    }

    private function setKey($keyVals)
    {
        if (! is_array($keyVals) ) {
            $keyVals = [$keyVals];
        }
        $this->keyVals = $keyVals;
    }

    public function getKey()
    {
        return implode('-', $this->keyVals);
    }
}
