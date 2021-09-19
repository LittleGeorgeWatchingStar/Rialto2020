<?php

namespace Rialto\Logging\Web;

use MongoDB\BSON\Regex;
use MongoDB\Database;
use Rialto\Database\Mongo\DocumentList;
use Rialto\Security\Role\Role;
use Rialto\Time\Web\DateType;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class AutomationLogController extends RialtoController
{
    /**
     * @Route("/automation/log/", name="automation_log")
     * @Template("core/automation-log/view.html.twig")
     */
    public function viewAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        /** @var $db Database */
        $db = $this->get(Database::class);

        $options = ['csrf_protection' => false, 'method' => 'get'];
        $form = $this->createNamedBuilder(null, null, $options)
            ->add('since', DateType::class, [
                'input' => 'string',
                'format' => 'yyyy-MM-dd',
                'required' => false
            ])
            ->add('command', SearchType::class, [
                'required' => false,
            ])
            ->add('message', SearchType::class, [
                'required' => false,
            ])
            ->add('username', SearchType::class, [
                'required' => false,
            ])
            ->add('_limit', IntegerType::class, [
                'required' => false,
            ])
            ->getForm();

        $form->submit($request->query->all());
        $list = new DocumentList($db->automation, $this->getFilters($form));

        return [
            'form' => $form->createView(),
            'cursor' => $list,
        ];
    }

    private function getFilters(FormInterface $form)
    {
        $since = $form->get('since')->getData();
        $command = $form->get('command')->getData();
        $message = $form->get('message')->getData();
        $username = $form->get('username')->getData();
        $filters = array_filter([
            'datetime' => $since ? ['$gte' => $since] : null,
            'context.command' => $command ?
                new Regex($command, "i") : null,
            'message' => $message ?
                new Regex($message, "i") : null,
            'context.username' => $username ?
                new Regex($username, "i") : null,
            '_limit' => $form->get('_limit')->getData(),
            '_sort' => ['datetime' => -1],
        ]);

        return $filters;
    }
}
