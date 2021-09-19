<?php

namespace Rialto\Payment\PaymentMethod\Web;

use Rialto\Payment\PaymentMethod\PaymentMethod;
use Rialto\Payment\PaymentMethod\PaymentMethodGroup;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class PaymentMethodController extends RialtoController
{
    /**
     * List and edit all payment methods and groups.
     *
     * @Route("/payment/method/", name="payment_method_list")
     * @Template("payment/PaymentMethod/paymentMethod-list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $em = $this->getDoctrine()->getManager();
        $groups = $em->getRepository(PaymentMethodGroup::class)
            ->findAll();
        $methods = $em->getRepository(PaymentMethod::class)
            ->findAll();

        $container = [
            'groups' => $groups,
            'newGroup' => null,
            'methods' => $methods,
            'newMethod' => null,
        ];
        $form = $this->createFormBuilder($container)
            ->add('groups', CollectionType::class, [
                'entry_type' => EditGroupType::class,
            ])
            ->add('newGroup', CreateGroupType::class, [
            ])
            ->add('methods', CollectionType::class, [
                'entry_type' => EditMethodType::class,
            ])
            ->add('newMethod', CreateMethodType::class, [
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit all',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid() ) {
            $newGroup = $form->get('newGroup')->getData();
            $newMethod = $form->get('newMethod')->getData();
            if ( $newGroup ) {
                $em->persist($newGroup);
            }
            if ( $newMethod ) {
                $em->persist($newMethod);
            }
            $em->flush();
            $this->logNotice("Payment methods updated successfully.");
            return $this->redirect($this->getCurrentUri());
        }

        return [
            'form' => $form->createView(),
            'returnUri' => $this->getReturnUri(null),
        ];
    }
}
