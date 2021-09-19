<?php

namespace Rialto\Sales;

use Psr\Log\LoggerInterface;
use Rialto\Sales\Customer\CustomerBranch;

/**
 * Logs sales events.
 */
class SalesLogger
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return string The log message.
     */
    public function deniedPartyExemption(CustomerBranch $branch)
    {
        $msg = "Exemption removed from branch \"%s\".";
        $context = [
            'customer' => $branch->getCustomerId(),
            'branchId' => $branch->getId(),
        ];

        if ( $branch->isDeniedPartyExempt() ) {
            $msg = "Branch \"%s\" is now DPS exempt.";
            $context['reason'] = $branch->getDeniedPartyExemption();
        }

        $msg = sprintf($msg, $branch->getBranchName());
        $this->logger->notice($msg, $context);
        return $msg;
    }

}
