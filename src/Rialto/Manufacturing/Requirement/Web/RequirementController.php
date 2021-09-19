<?php

namespace Rialto\Manufacturing\Requirement\Web;

use Exception;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Producer\DependencyUpdater;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;


/**
 * Controller for creating, editing, and deleting Requirement records.
 *
 * @see Requirement
 */
class RequirementController extends RialtoController
{
    /**
     * @Route("/Manufacturing/WorkOrder/{id}/requirement/",
     *   name="Manufacturing_Requirement_create")
     * @Template("manufacturing/requirement/requirement-create.html.twig")
     */
    public function createAction(WorkOrder $workOrder, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        $template = new CreateRequirement($workOrder);
        $options = ['action' => $this->getCurrentUri()];
        $form = $this->createForm(CreateRequirementType::class, $template, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $requirement = $workOrder->createRequirement(
                    $template->component,
                    $template->unitQty,
                    $template->workType);
                $requirement->setScrapCount($template->scrapCount);
                $this->updateDependencies($workOrder);
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Added {$requirement->getSku()} to the requirements.");
            $uri = $this->workOrderUrl($workOrder);
            return JsonResponse::javascriptRedirect($uri);
        }

        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
            'requirement' => $template,
            'workOrder' => $workOrder,
        ];
    }

    private function workOrderUrl(WorkOrder $workOrder)
    {
        return $this->generateUrl('work_order_view', [
            'order' => $workOrder->getId(),
        ]);
    }

    /**
     * @Route("/Manufacturing/Requirement/{id}",
     *   name="Manufacturing_Requirement_edit")
     * @Template("manufacturing/requirement/requirement-edit.html.twig")
     */
    public function editAction(Requirement $requirement, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        $options = ['action' => $this->getCurrentUri()];
        $form = $this->createForm(EditRequirementType::class, $requirement, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->updateDependencies($requirement->getWorkOrder());
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("{$requirement->getSku()} updated successfully.");
            $uri = $this->workOrderUrl($requirement->getWorkOrder());
            return JsonResponse::javascriptRedirect($uri);
        }

        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
            'requirement' => $requirement,
        ];
    }

    /**
     * @Route("/record/Manufacturing/Requirement/{id}",
     *   name="Manufacturing_Requirement_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Requirement $requirement)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        $workOrder = $requirement->getWorkOrder();
        if (count($workOrder->getRequirements()) == 1) {
            throw $this->badRequest('A work order must have at least one requirement');
        }
        $this->dbm->beginTransaction();
        try {
            $workOrder->removeRequirement($requirement);
            $this->updateDependencies($workOrder);
            $this->dbm->flushAndCommit();
        } catch (Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
        $this->logNotice("{$requirement->getSku()} has been removed from the work order requirements.");
        $uri = $this->workOrderUrl($workOrder);
        return $this->redirect($uri);
    }

    private function updateDependencies(WorkOrder $wo)
    {
        $updater = $this->get(DependencyUpdater::class);
        $updater->updateDependencies($wo);
    }

}
