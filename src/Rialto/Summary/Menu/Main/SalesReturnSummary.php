<?php

namespace Rialto\Summary\Menu\Main;

use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Returns\SalesReturn;
use Rialto\Sales\Returns\SalesReturnRepository;
use Rialto\Security\Role\Role;
use Rialto\Summary\Menu\SummaryLink;
use Symfony\Component\Routing\RouterInterface;

/**
 * Summary of sales returns.
 */
class SalesReturnSummary extends OrmNodeAbstract
{
    /** @var  SalesReturnRepository  */
    private $repo;

    public function __construct(DbManager $dbm, RouterInterface $router)
    {
        parent::__construct($dbm, $router);
        $this->repo = $dbm->getRepository(SalesReturn::class);
    }

    public function getId(): string
    {
        return 'SalesReturns';
    }

    public function getLabel(): string
    {
        return 'Sales returns';
    }

    public function getAllowedRoles(): array
    {
        return [
            Role::CUSTOMER_SERVICE,
            Role::STOCK,
            Role::WAREHOUSE,
        ];
    }

    protected function loadChildren(): array
    {
        $links = [
            $this->createReceiveLink(),
            $this->createTestLink(),
            $this->createTestedLink(),
        ];
        return $links;
    }

    private function createReceiveLink()
    {
        $count = $this->repo->countUnreceived();
        $id = 'receive';
        $text = "Receive returned products ($count)";
        return $this->createLink($id, $text);
    }

    private function createLink($id, $text)
    {
        $uri = $this->router->generate('sales_return_list', [
            'status' => $id
        ]);

        return new SummaryLink($id, $uri, $text);
    }

    private function createTestLink()
    {
        $count = $this->repo->countNeedTesting();
        $id = 'test';
        $text = "Test returned products ($count)";
        return $this->createLink($id, $text);
    }

    private function createTestedLink()
    {
        $count = $this->repo->countTested();
        $id = 'tested';
        $text = "Show tested returns ($count)";
        return $this->createLink($id, $text);
    }

}
