<?php

namespace Rialto\Accounting\Terms\Web;


use Rialto\Accounting\Terms\PaymentTerms;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;

class PaymentTermsController extends RialtoController
{
    /**
     * @Route("/accounting/payment-terms/", name="payment_terms_edit")
     * @Template("accounting/payterms/edit.html.twig")
     */
    public function editAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $data = [
            'terms' => $this->getRepository(PaymentTerms::class)->findAll(),
            'newTerms' => null,
        ];

        $form = $this->createFormBuilder($data)
            ->add('terms', CollectionType::class, [
                'entry_type' => PaymentTermsType::class,
                'entry_options' => ['label' => false],
                'label' => false,
            ])
            ->add('newTerms', AddPaymentTermsType::class, [
                'required' => false,
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newTerms = $form->get('newTerms')->getData();
            if ($newTerms) {
                $this->dbm->persist($newTerms);
            }
            $this->dbm->flush();
            $this->logNotice("Payment terms updated successfully.");
            return $this->redirectToRoute('payment_terms_edit');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
