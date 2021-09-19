<?php

namespace Rialto\Email\Subscription\Web;

use Rialto\Email\Subscription\SubscriptionManager;
use Rialto\Security\Privilege;
use Rialto\Security\User\User;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Managers user subscriptions to email topics.
 */
class UserSubscriptionController extends RialtoController
{
    /**
     * @Route("/security/user/{id}/subscriptions/",
     *   name="email_subscription_edit")
     * @Template("email/userSubscription/subscription-edit.html.twig")
     */
    public function editAction(User $user, Request $request)
    {
        $this->denyAccessUnlessGranted(Privilege::EDIT, $user);

        $returnUri = $this->generateUrl('user_edit', ['id' => $user->getId()]);

        $mgr = new SubscriptionManager($user, $this->dbm);
        $form = $this->createFormBuilder($mgr)
            ->add('topics', CollectionType::class, [
                'entry_type' => TextType::class,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            $this->logNotice("Subscriptions updated successfully");
            return $this->redirect($returnUri);
        }

        return [
            'user' => $user,
            'form' => $form->createView(),
            'cancelUri' => $returnUri,
        ];
    }
}
