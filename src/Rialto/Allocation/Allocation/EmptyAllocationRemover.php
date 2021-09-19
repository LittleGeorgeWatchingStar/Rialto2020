<?php

namespace Rialto\Allocation\Allocation;

use Doctrine\ORM\EntityManager;
use Rialto\Allocation\Allocation\Orm\StockAllocationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Removes any allocations that were emptied during the request.
 */
class EmptyAllocationRemover
{
    /** @var StockAllocationRepository */
    private $repo;

    public function __construct(EntityManager $em)
    {
        $this->repo = $em->getRepository(StockAllocation::class);
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        $request = $event->getRequest();
        if ( $this->isReadOnly($request) ) {
            return;
        }

        $this->repo->deleteEmptyAllocations();
    }

    private function isReadOnly(Request $request)
    {
        $method = strtolower($request->getMethod());
        return in_array($method, ['get', 'head']);
    }
}
