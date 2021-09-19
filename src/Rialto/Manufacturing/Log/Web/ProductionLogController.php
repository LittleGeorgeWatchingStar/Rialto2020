<?php

namespace Rialto\Manufacturing\Log\Web;

use MongoDB\BSON\Regex;
use MongoDB\Database;
use Rialto\Database\Mongo\DocumentList;
use Rialto\Security\Role\Role;
use Rialto\Security\User\Orm\UserRepository;
use Rialto\Security\User\User;
use Rialto\Time\Web\DateType;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allow users to view and query the log of production-related events.
 */
class ProductionLogController extends RialtoController
{
    /**
     * @Route("/production/log/", name="production_log")
     * @Template("manufacturing/production/log/view.html.twig")
     */
    public function viewAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        /** @var $db Database */
        $db = $this->get(Database::class);

        $options = ['csrf_protection' => false, 'method' => 'get'];
        $form = $this->createNamedBuilder(null, null, $options)
            ->add('user', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $repo) {
                    return $repo->queryActive();
                },
                'required' => false,
                'placeholder' => '-- all users --',
                'choice_label' => 'name',
            ])
            ->add('po', SearchType::class, [
                'required' => false,
                'label' => 'PO',
            ])
            ->add('stockItem', SearchType::class, ['required' => false])
            ->add('since', DateType::class, [
                'input' => 'string',
                'format' => 'yyyy-MM-dd',
                'required' => false
            ])
            ->add('_limit', IntegerType::class, [
                'required' => false,
            ])
            ->add('filter', SubmitType::class)
            ->getForm();

        $form->submit($request->query->all());
        $filters = $this->getFilters($form);
        $filters['_sort'] = ['datetime' => -1];
        $list = new DocumentList($db->production, $filters);

        return [
            'form' => $form->createView(),
            'events' => $list,
        ];
    }

    private function getFilters(FormInterface $form)
    {
        /** @var User $user */
        $user = $form->get('user')->getData();
        $since = $form->get('since')->getData();
        $stockCode = $form->get('stockItem')->getData();
        $po = trim($form->get('po')->getData());
        $filters = array_filter([
            'user' => $user ? $user->getUsername() : null,
            'datetime' => $since ? ['$gte' => $since] : null,
            'context.tags.po' => $po ? ['$in' => [$po]] : null,
            'context.tags.stockItem' => $stockCode ?
                new Regex($stockCode, "i") : null,
            '_limit' => $form->get('_limit')->getData(),
        ]);

        return $filters;
    }
}
