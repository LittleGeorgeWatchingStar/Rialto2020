<?php

namespace Rialto\Sales\Salesman\Web;

use Rialto\Sales\Salesman\Salesman;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * For managing instances of Salesman.
 */
class SalesmanController extends RialtoController
{
    /**
     * List and edit all salespeople.
     *
     * @Route("/Sales/Salesman/", name="Sales_Salesman_edit")
     * @Template("sales/salesman/edit.html.twig")
     */
    public function editAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $existing = $this->dbm->getRepository(Salesman::class)
            ->findAll();

        $container = [
            'existing' => $existing,
            'new' => null,
        ];

        $form = $this->createFormBuilder($container)
            ->setAction($this->getCurrentUri())
            ->add('existing', CollectionType::class, [
                'entry_type' => SalesmanType::class,
            ])
            ->add('new', SalesmanCreateType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newSalesman = $form->get('new')->getData();
            if ($newSalesman) {
                $this->dbm->persist($newSalesman);
            }
            $this->dbm->flush();
            return $this->redirect($this->getCurrentUri());
        }

        return ['form' => $form->createView()];
    }
}
