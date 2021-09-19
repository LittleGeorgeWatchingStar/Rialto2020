<?php

namespace Rialto\Allocation\Requirement\Web;


use Exception;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Requirement\ManualAllocator;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class RequirementController extends RialtoController
{
    /**
     * Manually allocate to a requirement.
     *
     * @Route("/allocation/requirement/{id}/",
     *   name="allocation_requirement_manual", options={"expose": true})
     * @Template("allocation/requirement/allocate.html.twig")
     */
    public function allocateAction(Requirement $requirement, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::MANUFACTURING]);
        $allocator = new ManualAllocator($requirement);
        $allocator->setShareBins($request->get('shareBins'));
        $form = $this->createForm(ManualAllocatorType::class, $allocator);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                /** @var $factory AllocationFactory */
                $factory = $this->get(AllocationFactory::class);
                $allocs = $allocator->allocate($factory);
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            if (count($allocs) > 0) {
                $this->logNotice('Allocated successfully.');
            } else {
                $this->logWarning('Nothing allocated.');
            }
            return $this->redirect($this->getCurrentUri());
        }

        return [
            'form' => $form->createView(),
            'consumer' => $requirement->getConsumer(),
            'requirement' => $requirement,
            'status' => $requirement->getAllocationStatus(),
            'consumers' => $allocator,
        ];
    }

}
