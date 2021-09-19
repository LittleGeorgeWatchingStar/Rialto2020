<?php

namespace Rialto\Stock\Shelf\Rack\Web;

use Doctrine\ORM\EntityRepository;
use Rialto\Security\Role\Role;
use Rialto\Stock\Shelf\Rack;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * For managing stock racks.
 *
 * @see Rack
 */
class RackController extends RialtoController
{
    /** @var EntityRepository */
    private $rackRepo;

    protected function init(ContainerInterface $container)
    {
        $this->rackRepo = $this->dbm->getRepository(Rack::class);
    }

    /**
     * @Route("/stock/rack/", name="rack_list")
     * @Method("GET")
     * @Template("stock/shelf/rack-list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $racks = $this->rackRepo->findAll();
        return [
            'racks' => $racks,
            'hq' => $this->getHeadquarters(),
        ];
    }

    /**
     * @Route("/stock/create-rack/", name="rack_create")
     * @Method({"GET", "POST"})
     * @Template("stock/shelf/rack-create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $maker = new RackMaker();
        $maker->facility = $this->getHeadquarters();
        $form = $this->createForm(RackMakerForm::class, $maker);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $rack = $maker->makeRack();
            $this->dbm->persist($rack);
            $this->dbm->flush();
            $url = $this->generateUrl('rack_list');
            return JsonResponse::javascriptRedirect($url);
        }
        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/stock/rack/{rack}/", name="rack_edit")
     * @Method({"GET", "POST"})
     * @Template("stock/shelf/rack-edit.html.twig")
     */
    public function editAction(Rack $rack, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $editor = new RackEdit($rack);
        $editor->setAddRemove($request->get('addRemove'));
        $form = $this->createForm(RackEditForm::class, $editor);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $editor->applyChanges();
            $this->dbm->flush();
            $this->logNotice(ucfirst("$rack updated successfully."));
            return $editor->isAddRemove()
                ? $this->redirect($this->getCurrentUri())
                : $this->redirectToRoute('rack_list');
        }
        return [
            'rack' => $rack,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/stock/rack/{rack}/", name="rack_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Rack $rack, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $this->checkCsrf('rack_delete', $request);
        if ($rack->isOccupied()) {
            throw $this->badRequest("Cannot delete an occupied rack");
        }

        $msg = "Deleted $rack successfully.";
        $this->dbm->remove($rack);
        $this->dbm->flush();
        $this->logNotice($msg);
        return $this->redirectToRoute('rack_list');
    }
}
