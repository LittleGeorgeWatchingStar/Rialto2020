<?php

namespace Rialto\Stock\ChangeNotice\Web;

use Rialto\Database\Orm\EntityList;
use Rialto\Security\Role\Role;
use Rialto\Stock\ChangeNotice\ChangeNotice;
use Rialto\Stock\ChangeNotice\Orm\ChangeNoticeRepository;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;

/**
 * For managing change notices.
 *
 * @see ChangeNotice
 */
class ChangeNoticeController extends RialtoController
{
    /**
     * @var ChangeNoticeRepository
     */
    private $repo;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->getRepository(ChangeNotice::class);
    }

    /**
     * @Route("/stock/change-notice/", name="change_notice_list")
     * @Method("GET")
     * @Template("stock/changeNotice/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $form = $this->createForm(ChangeNoticeListFilterType::class);
        $form->submit($request->query->all());
        $list = new EntityList($this->repo, $form->getData());
        return [
            'form' => $form->createView(),
            'changeNotice' => $list,
        ];
    }

    /**
     * @Route("/stock/change-notice/{id}/", name="change_notice_view")
     * @Method("GET")
     * @Template("stock/changeNotice/view.html.twig")
     */
    public function viewAction(ChangeNotice $notice)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        return ['entity' => $notice];
    }

    /**
     * @Route("/stock/change-notice/{id}/", name="change_notice_edit")
     * @Template("core/form/dialogForm.html.twig")
     */
    public function editAction(ChangeNotice $notice, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->createFormBuilder($notice)
            ->add('description', TextareaType::class)
            ->getForm();

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->dbm->flush();
                $this->logNotice(ucfirst("$notice updated successfully."));
                $uri = $this->generateUrl('change_notice_view', [
                    'id' => $notice->getId(),
                ]);
                return JsonResponse::javascriptRedirect($uri);
            } else {
                return JsonResponse::fromInvalidForm($form);
            }
        }

        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
        ];
    }
}
