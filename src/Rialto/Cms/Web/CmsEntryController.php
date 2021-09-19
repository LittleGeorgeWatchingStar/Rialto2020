<?php

namespace Rialto\Cms\Web;

use Rialto\Cms\CmsEntry;
use Rialto\Database\Orm\EntityList;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for creating and managing entries in the internal
 * content management system (CMS).
 *
 * @see CmsEntry
 */
class CmsEntryController extends RialtoController
{
    /**
     * @Route("/util/cms-entries/", name="cms_entry_list")
     * @Method("GET")
     * @Template("util/cmsEntry/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $this->setReturnUri($this->getCurrentUri());

        $repo = $this->getRepository(CmsEntry::class);
        $list = new EntityList($repo, $request->query->all());
        return [
            'list' => $list,
        ];
    }

    /**
     * @Route("/util/new-cms-entry/", name="cms_entry_create")
     * @Template("util/cmsEntry/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $entry = new CmsEntry();
        return $this->processForm($entry, 'created', $request);
    }

    /**
     * @Route("/util/cms-entries/{id}/", name="cms_entry_edit")
     * @Template("util/cmsEntry/edit.html.twig")
     */
    public function editAction(CmsEntry $entry, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        return $this->processForm($entry, 'updated', $request);
    }

    private function processForm(CmsEntry $entry, $updated, Request $request)
    {
        $form = $this->createForm(CmsEntryType::class, $entry);
        $returnUri = $this->generateUrl('cms_entry_list');
        $returnUri = $this->getReturnUri($returnUri);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($entry);
            $this->dbm->flush();

            $this->logNotice("$entry $updated successfully.");
            return $this->redirect($returnUri);
        }

        return [
            'entry' => $entry,
            'form' => $form->createView(),
            'cancelUri' => $returnUri,
        ];
    }

}
