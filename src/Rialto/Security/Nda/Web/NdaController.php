<?php

namespace Rialto\Security\Nda\Web;


use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Presents a non-disclosure agreement that users outside of the company
 * must accept before using this system.
 */
class NdaController extends RialtoController
{
    /**
     * @Route("/login/nda-form/", name="nda_form")
     * @Template("security/nda/nda-form.html.twig")
     */
    public function formAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('nda_form'))
            ->add('nda_accepted', CheckboxType::class, [
                'label' => 'I accept',
                'required' => false,
            ])
            ->add('next', HiddenType::class, [
                'data' => $request->get('next'),
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            if ($data['nda_accepted']) {
                $session = $request->getSession();
                $session->set('nda_accepted', true);

                $defaultUrl = $this->generateUrl('index');
                return $this->redirect($request->get('next', $defaultUrl));
            } else {
                return $this->redirectToRoute('logout');
            }
        }

        $user = $this->getCurrentUser();
        $supplier = $user->getSupplier();

        return [
            'user' => $user,
            'companyName' => $supplier ? $supplier->getName() : $user->getName(),
            'form' => $form->createView(),
        ];

    }
}
