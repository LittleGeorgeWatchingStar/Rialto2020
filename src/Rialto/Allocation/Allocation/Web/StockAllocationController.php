<?php

namespace Rialto\Allocation\Allocation\Web;

use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SplObjectStorage;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for managing stock allocations.
 */
class StockAllocationController extends RialtoController
{
    /**
     * @Route("/allocation/stock-allocation/", name="allocation_list")
     * @Method("GET")
     * @Template("allocation/allocation/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->setReturnUri($this->getCurrentUri());

        $repo = $this->getRepository(StockAllocation::class);
        $allocs = $repo->findByFilters($request->query->all());
        $index = $this->indexByRequirement($allocs);

        return [
            'index' => $index,
        ];
    }

    /** @return SplObjectStorage */
    private function indexByRequirement(array $allocs)
    {
        $index = new SplObjectStorage();
        foreach ($allocs as $alloc) {
            /* @var $alloc StockAllocation */
            $requirement = $alloc->getRequirement();
            $list = isset($index[$requirement]) ? $index[$requirement] : [];
            $list[] = $alloc;
            $index[$requirement] = $list;
        }
        return $index;
    }

    /**
     * @Route("/allocation/stock-allocation/{id}/", name="stock_allocation_delete")
     * @Method("DELETE")
     */
    public function deleteAction(StockAllocation $alloc, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $id = $alloc->getId();
        $stockCode = $alloc->getSku();
        $alloc->close();
        $this->dbm->flush();
        $this->logNotice("Allocation $id for $stockCode deleted.");

        $uri = $request->headers->get('referer');
        if (!$uri) {
            $uri = $this->generateUrl('index');
        }
        return $this->redirect($uri);
    }

    /**
     * @Route("/record/Allocation/StockAllocation/",
     *   name="Allocation_StockAllocation_batchDelete")
     * @Method("DELETE")
     */
    public function batchDeleteAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $ids = $request->get('id');
        $repo = $this->getRepository(StockAllocation::class);
        $allocs = array_map(function ($id) use ($repo) {
            return $repo->find($id);
        }, $ids);

        $this->dbm->beginTransaction();
        try {
            foreach ($allocs as $alloc) {
                $alloc->close();
            }
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        $this->logNotice("Closed the following allocations: " . join(', ', $ids));
        $url = $this->getReturnUri($this->generateUrl('allocation_list'));
        return $this->redirect($url);
    }
}
