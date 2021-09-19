<?php

namespace Rialto\Manufacturing\WorkOrder\Issue\Web;

use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssue;
use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssuer;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Controller for managing WorkOrderIssue records.
 */
class WorkOrderIssueController extends RialtoController
{
    /**
     * @Route("/manufacturing/issue/{id}/", name="workorder_issue_view")
     * @Method("GET")
     * @Template("manufacturing/order/issue/view.html.twig")
     */
    public function viewAction(WorkOrderIssue $issue)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        return ['entity' => $issue];
    }

    /**
     * Allows the administrator to roll-back an issuance transaction.
     *
     * @Route("/Manufacturing/WorkOrderIssue/{id}/reverseIssue",
     *   name="Manufacturing_WorkOrderIssue_reverse")
     */
    public function reverseIssueAction(WorkOrderIssue $issue, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $error = $this->validateReverseIssue($issue->getWorkOrder());
        if ($error) {
            throw $this->badRequest("Work order is $error");
        }

        $form = $this->createFormBuilder()
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantity to reverse',
                'constraints' => new Assert\Range([
                    'min' => 1,
                    'max' => $issue->getQtyIssued() - $issue->getQtyReceived()])
            ])
            ->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $qty = $data['quantity'];
                /** @var WorkOrderIssuer $issuer */
                $issuer = $this->get(WorkOrderIssuer::class);
                $this->dbm->beginTransaction();
                try {
                    $issuer->reverseIssue($issue, $qty);
                    $this->dbm->flushAndCommit();
                } catch (\Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }

                $this->logNotice("Reversed $qty issued units.");
                $uri = $this->generateUrl('workorder_issue_view', [
                    'id' => $issue->getId(),
                ]);
                return JsonResponse::javascriptRedirect($uri);
            } else {
                return JsonResponse::fromInvalidForm($form);
            }
        }

        return $this->render(
            'core/form/dialogForm.html.twig', [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
        ]);
    }

    private function validateReverseIssue(WorkOrder $wo)
    {
        if ($wo->isClosed()) return 'already closed';
        if (! $wo->isIssued()) return 'not issued yet';
        if ($wo->getQtyReceived() >= $wo->getQtyIssued()) return 'already received';
        return null;
    }
}
