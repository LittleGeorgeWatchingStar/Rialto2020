<?php

namespace Rialto\Sales\Type\Web;

use Rialto\Sales\Type\SalesType;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * For managing sales types.
 */
class SalesTypeController extends RialtoController
{
    /**
     * List and edit all sales types.
     *
     * @Route("/Sales/SalesType/", name="Sales_SalesType_edit")
     * @Template("sales/type/edit.html.twig")
     */
    public function editAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $types = $this->dbm->getRepository(SalesType::class)
            ->findAll();

        $container = [
            'existing' => $types,
            'new' => null,
        ];

        $form = $this->createFormBuilder($container)
            ->setAction($this->getCurrentUri())
            ->add('existing', CollectionType::class, [
                'entry_type' => SalesTypeEditType::class,
            ])
            ->add('new', SalesTypeCreateType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newType = $form->get('new')->getData();
            if ($newType) {
                $this->dbm->persist($newType);
            }
            $this->dbm->flush();
            return $this->redirect($this->getCurrentUri());
        }

        return ['form' => $form->createView()];
    }
}
