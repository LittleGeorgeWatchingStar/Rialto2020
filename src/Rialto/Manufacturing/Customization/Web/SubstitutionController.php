<?php

namespace Rialto\Manufacturing\Customization\Web;

use Rialto\Database\Orm\EntityList;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Orm\CustomizationRepository;
use Rialto\Manufacturing\Customization\Substitution;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for maintaining the component substitutions that comprise
 * a manufacturing customization.
 */
class SubstitutionController extends RialtoController
{
    /**
     * @Route("/manufacturing/substitutions/", name="substitution_list")
     * @Template("manufacturing/substitution/Substitution/substitution-list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        $form = $this->createForm(SubstitutionListFilterType::class);
        $form->submit($request->query->all());
        $filters = $form->getData();
        $repo = $this->getRepository(Substitution::class);
        $list = new EntityList($repo, $filters);
        return [
            'list' => $list,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/manufacturing/new-substitution/",
     *   name="substitution_create")
     * @Template("manufacturing/substitution/Substitution/substitution-create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        $sub = new Substitution();
        return $this->processForm($sub, $request);
    }

    private function processForm(Substitution $sub, Request $request)
    {
        $form = $this->createForm(SubstitutionType::class, $sub);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $updated = 'updated';
            if (!$sub->getId()) {
                $this->dbm->persist($sub);
                $updated = 'created';
            }
            $this->dbm->flush();
            $this->logNotice(ucfirst("$sub $updated successfully."));
            return $this->redirectToRoute('substitution_list');
        }

        return [
            'sub' => $sub,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/manufacturing/substitutions/{id}/",
     *   name="substitution_edit")
     * @Template("manufacturing/substitution/Substitution/substitution-edit.html.twig")
     */
    public function editAction(Substitution $sub, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        return $this->processForm($sub, $request);
    }

    /**
     * @Route("/record/Manufacturing/Substitution/{id}/",
     *   name="Manufacturing_Substitution_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Substitution $sub)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        /** @var $cmzRepo CustomizationRepository */
        $cmzRepo = $this->dbm->getRepository(Customization::class);
        $count = $cmzRepo->createBuilder()
            ->bySubstitution($sub)
            ->getRecordCount();
        if ($count > 0) {
            $msg = "Cannot delete a substitution that is used in customizations.";
            $this->logError($msg);
        } else {
            $msg = "Substitution $sub deleted successfully.";
            $this->dbm->remove($sub);
            $this->dbm->flush();
            $this->logNotice($msg);
        }
        return $this->redirectToRoute('substitution_list');
    }

}
