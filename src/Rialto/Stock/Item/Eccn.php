<?php

namespace Rialto\Stock\Item;

final class Eccn
{
    const EAR99 = 'EAR99';
    const COMPUTERS = '4A003.C';
    const INFOSEC = '5A002.A1';
    const MASS_MARKET = '5A992.C';

    public static function getList()
    {
        return [
            self::EAR99,
            self::COMPUTERS,
            self::INFOSEC,
            self::MASS_MARKET,
        ];
    }
}
