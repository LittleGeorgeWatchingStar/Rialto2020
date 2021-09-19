<?php

namespace Rialto\Company\Orm;

use Rialto\Company\Company;
use Rialto\Database\Orm\RialtoRepositoryAbstract;

class CompanyRepository extends RialtoRepositoryAbstract
{
    /** @return Company */
    public function findDefault()
    {
        return $this->need(Company::DEFAULT_ID);
    }
}
