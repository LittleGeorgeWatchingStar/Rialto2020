<?php

namespace Rialto\Sales\GLPosting\Web;

use Rialto\Sales\GLPosting\SalesGLPosting;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * For managing SalesGLPostings.
 */
class SalesGLPostingController extends RialtoController
{
    /**
     * List and edit all SalesGLPostings.
     *
     * @Route("/Sales/SalesGLPosting/", name="Sales_SalesGLPosting_edit")
     * @Template("sales/gl-posting/edit.html.twig")
     */
    public function editAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $existing = $this->dbm->getRepository(SalesGLPosting::class)
            ->findAll();
        $container = [
            'existing' => $existing,
            'new' => null,
        ];
        $form = $this->createFormBuilder($container)
            ->setAction($this->getCurrentUri())
            ->add('existing', CollectionType::class, [
                'entry_type' => SalesGLPostingType::class,
            ])
            ->add('new', SalesGLPostingCreateType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() ) {
            $newRecord = $form->get('new')->getData();
            logDebug($newRecord, 'new record');
            if ( $newRecord ) {
                $this->dbm->persist($newRecord);
            }
            $this->dbm->flush();
            return $this->redirect($this->getCurrentUri());
        }
        return [
            'form' => $form->createView(),
        ];
    }
}
