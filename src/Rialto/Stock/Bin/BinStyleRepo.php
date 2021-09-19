<?php

namespace Rialto\Stock\Bin;

use Rialto\Database\Orm\FilteringRepositoryAbstract;


class BinStyleRepo
extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('s');
        return $builder->buildQuery($params);
    }

    /**
     * @param string $newStyle
     * @return BinStyle
     */
    public function findMatching($newStyle)
    {
        foreach ($this->findAll() as $style) {
            /** @var $style BinStyle */
            if (regex_match("/" . $style->getId() . "/i", $newStyle)) {
                return $style;
            }
        }
        return $this->find(BinStyle::DEFAULT_STYLE);
    }
}
