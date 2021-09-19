<?php

namespace Rialto\Stock\Facility\Web;

use Rialto\Database\Orm\EntityList;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class FacilityController extends RialtoController
{
    /**
     * @var FacilityRepository
     */
    private $repo;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->getRepository(Facility::class);
    }

    /**
     * @Route("/stock/facility/", name="stock_facility_list")
     * @Method("GET")
     * @Template("stock/facility/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $form = $this->createForm(FacilityListFilterType::class);
        $form->submit($request->query->all());
        $filter = new EntityList($this->repo, $form->getData());
        return [
            'form' => $form->createView(),
            'facilities' => $filter,
        ];
    }

    /**
     * @Route("/Stock/Location/{id}/", name="Stock_Location_edit", options={"expose": true})
     * @Template("stock/facility/edit.html.twig")
     */
    public function editAction(Facility $location, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->createForm(FacilityType::class, $location);
        return $this->renderForm($form, $request);
    }

    /**
     * @Route("/Stock/Location/", name="Stock_Location_create")
     * @Template("stock/facility/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->createForm(CreateLocationType::class);
        return $this->renderForm($form, $request, 'created');
    }

    private function renderForm(FormInterface $form, Request $request, $updated = 'updated')
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $facility = $form->getData();
            $this->dbm->persist($facility);
            $this->dbm->flush();
            $this->logNotice("Stock location $facility $updated successfully.");
            return $this->redirectToRoute('stock_facility_list', [
                'facility' => $facility->getId(),
            ]);
        }

        return [
            'form' => $form->createView(),
            'location' => $form->getData(),
            'cancelUri' => $this->generateUrl('stock_facility_list'),
        ];
    }
}
