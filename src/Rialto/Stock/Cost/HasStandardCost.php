<?php


namespace Rialto\Stock\Cost;

interface HasStandardCost
{
    /** @return float */
    public function getUnitStandardCost();

    /** @return float */
    public function getExtendedStandardCost();
}
