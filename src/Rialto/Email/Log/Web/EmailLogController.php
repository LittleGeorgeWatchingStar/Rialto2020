<?php

namespace Rialto\Email\Log\Web;

use MongoDB\Database;
use Rialto\Database\Mongo\DocumentList;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class EmailLogController extends RialtoController
{
    /**
     * @Route("/email/log/", name="email_log")
     * @Template("email/emailLog/log-view.html.twig")
     */
    public function viewAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        /** @var $db Database */
        $db = $this->get(Database::class);

        $form = $this->createForm(EmailLogFilterType::class);

        $form->submit($request->query->all());
        $filters = $form->getData();
        $filters['_sort'] = ['datetime' => -1]; // sort by date desc.
        $results = new DocumentList($db->email, $filters);

        return [
            'form' => $form->createView(),
            'cursor' => $results,
        ];
    }
}
