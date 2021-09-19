<?php

namespace Rialto\Sales\Customer\Web;


use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Web\Serializer\ListableFacade;

class CustomerBranchSummary
{
    use ListableFacade;

    /** @var CustomerBranch */
    private $branch;

    public function __construct(CustomerBranch $branch)
    {
        $this->branch = $branch;
    }

    public function getId()
    {
        return $this->branch->getId();
    }

    public function getContactName()
    {
        return $this->branch->getContactName();
    }

    public function getBranchName()
    {
        return $this->branch->getBranchName();
    }
}
