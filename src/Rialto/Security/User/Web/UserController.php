<?php

namespace Rialto\Security\User\Web;

use Doctrine\ORM\EntityRepository;
use Rialto\Database\Orm\EntityList;
use Rialto\Security\Privilege;
use Rialto\Security\Role\Role;
use Rialto\Security\User\SsoLink;
use Rialto\Security\User\User;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;

class UserController extends RialtoController
{
    /**
     * @Route("/security/user/", name="user_list")
     * @Template("security/user/user-list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);

        $this->setReturnUri($this->getCurrentUri());

        $form = $this->createForm(ListFilterType::class);
        $form->submit($request->query->all());
        $repo = $this->getRepository(User::class);
        $list = new EntityList($repo, $form->getData());
        if ($request->get('csv')) {
            $csv = UserCsv::create($list);
            return FileResponse::fromData($csv->toString(), 'users.csv', 'text/csv');
        }
        return [
            'form' => $form->createView(),
            'users' => $list,
            'sso_url' => $this->container->getParameter('gumstix_sso.service.server'),
        ];
    }

    /**
     * @Route("/security/new-user/", name="user_create")
     * @Template("security/user/user-create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->createForm(CreateUserType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $this->dbm->persist($user);
            $this->dbm->flush();

            $this->logNotice("User $user created successfully.");
            return $this->redirectToRoute('user_edit', [
                'id' => $user->getId(),
            ]);
        }

        return [
            'form' => $form->createView(),
            'cancelUri' => $this->generateUrl('user_list', [
                'active' => 'yes',
            ]),
        ];
    }

    /**
     * @Route("/security/user/{id}/", name="user_edit")
     * @Template("security/user/user-edit.html.twig")
     */
    public function editAction(User $user, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $this->denyAccessUnlessGranted(Privilege::EDIT, $user);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            $this->logNotice(sprintf(
                'User "%s" updated successfully.',
                $user->getName()
            ));
            return $this->redirectToRoute('user_edit', [
                'id' => $user->getId(),
            ]);
        }

        $cancelUri = $this->isGranted(Role::ADMIN)
            ? $this->generateUrl('user_list', ['active' => 'yes'])
            : $this->generateUrl('index');

        return [
            'form' => $form->createView(),
            'user' => $user,
            'cancelUri' => $cancelUri,
        ];
    }

    /**
     * @Route("/security/user/{id}/", name="user_disable")
     * @Method("DELETE")
     */
    public function disableAction(User $user)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $user->disable();
        $this->getDoctrine()
            ->getManager()
            ->flush();
        $this->logNotice("User $user has been blocked.");
        $url = $this->generateUrl('user_list', [
            'active' => 'yes',
        ]);
        $url = $this->getReturnUri($url);
        return $this->redirect($url);
    }

    /**
     * Allows admins to impersonate other users.
     *
     * Developers need this for testing privilege-specific features.
     *
     * @Route("/security/switch-user/", name="switch_user")
     */
    public function switchAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);

        $options = [
            'method' => 'get',
            'action' => $this->generateUrl('switch_user'),
            'attr' => ['class' => 'inline switch-user'],
        ];
        $form = $this->createFormBuilder(null, $options)
            ->add('ssoLink', EntityType::class, [
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('l')
                        ->select('l', 'u')
                        ->join('l.user', 'u')
                        ->join('u.roles', 'r')
                        ->orderBy('u.id');
                },
                'label' => false,
                'class' => SsoLink::class,
                'choice_label' => 'username',
                'placeholder' => 'impersonate...',
                'attr' => [
                    'onchange' => "this.form.submit();",
                ],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $ssoLink SsoLink */
            $ssoLink = $form->get('ssoLink')->getData();
            return $this->redirectToRoute('index', [
                '_switch_user' => $ssoLink->getUuid(),
            ]);
        }
        return $this->renderString('{{ form(form) }}', [
            'form' => $form->createView(),
        ]);
    }
}
