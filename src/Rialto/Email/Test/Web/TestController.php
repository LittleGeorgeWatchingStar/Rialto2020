<?php

namespace Rialto\Email\Test\Web;

use Rialto\Email\Email;
use Rialto\Email\Mailable\Mailable;
use Rialto\Email\MailerInterface;
use Rialto\Security\Role\Role;
use Rialto\Security\User\Orm\UserRepository;
use Rialto\Security\User\User;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

/**
 * For testing the system email configuration by sending test emails.
 */
class TestController extends RialtoController
{
    /**
     * @Route("/Email/Test/", name="Email_Test")
     * @Template("email/test/test.html.twig")
     */
    public function testAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $email = new Email();
        $email->setFrom($this->getCurrentUser());
        $email->setSubject('test email from Rialto');
        $email->setBody('This is a test email from Rialto');
        $form = $this->createFormBuilder($email)
            ->add('to', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'emailLabel',
                'query_builder' => function (UserRepository $repo) {
                    return $repo->queryMailable();
                },
                'multiple' => true,
            ])
            ->add('subject', TextType::class)
            ->add('body', TextareaType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $mailer MailerInterface */
            $mailer = $this->get(MailerInterface::class);
            $mailer->send($email);
            $recipients = join(', ', array_map(function (Mailable $r) {
                return $r->getEmail();
            }, $email->getTo()));
            $this->logNotice("Test email sent to $recipients.");
            return $this->redirect($this->getCurrentUri());
        }

        return [
            'form' => $form->createView(),
        ];
    }

}
