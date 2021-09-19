<?php

namespace Rialto\Sales\Discount\Web;

use Rialto\Database\Orm\EntityList;
use Rialto\Sales\Discount\DiscountGroup;
use Rialto\Sales\Discount\Orm\DiscountGroupRepository;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DiscountGroupController extends RialtoController
{
    /**
     * @Route("/sales/discount-group/", name="discount_group_list")
     * @Method("GET")
     * @Template("sales/discount/group-list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $filters = $request->query->all();
        /** @var DiscountGroupRepository $repo */
        $repo = $this->getRepository(DiscountGroup::class);
        $list = new EntityList($repo, $filters);
        return ['list' => $list];
    }

    /**
     * @Route("/Sales/DiscountGroup/", name="Sales_DiscountGroup_create")
     * @Template("sales/discount/group-edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $group = new DiscountGroup();
        return $this->processForm($group, 'created', $request);
    }

    /**
     * @Route("/Sales/DiscountGroup/{id}/", name="Sales_DiscountGroup_edit")
     * @Template("sales/discount/group-edit.html.twig")
     */
    public function editAction(DiscountGroup $group, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        return $this->processForm($group, 'updated', $request);
    }

    private function processForm(DiscountGroup $group, $updated, Request $request)
    {
        $form = $this->createForm(DiscountGroupType::class, $group);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($group);
            $this->dbm->flush();
            $this->logNotice("The discount schedule has been $updated successfully.");
            $url = $this->generateUrl('Sales_DiscountGroup_edit', [
                'id' => $group->getId(),
            ]);
            return $this->redirect($url);
        }

        return [
            'group' => $group,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/record/Sales/DiscountGroup/{id}/", name="Sales_DiscountGroup_delete")
     * @Method("DELETE")
     */
    public function deleteAction(DiscountGroup $group)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $this->dbm->remove($group);
        $this->dbm->flush();
        $this->logNotice("The discount group has been deleted successfully.");
        $url = $this->generateUrl('discount_group_list');
        return $this->redirect($url);
    }
}
