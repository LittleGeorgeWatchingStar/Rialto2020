<?php

namespace Rialto\Summary\Menu\Main;

use Doctrine\DBAL\Connection;
use Rialto\Summary\Menu\Summary;
use Rialto\Summary\Menu\SummaryNode;
use Symfony\Component\Routing\RouterInterface;

/**
 * A basic SummaryNode implementation with a router and DB connection.
 */
abstract class DBNodeAbstract implements SummaryNode
{
    /** @var Connection */
    protected $db;

    /** @var RouterInterface */
    protected $router;

    /** @var Summary[] */
    private $children = null;

    public function __construct(Connection $db, RouterInterface $router)
    {
        $this->db = $db;
        $this->router = $router;
    }

    public function getChildren(): array
    {
        if (null === $this->children) {
            $this->children = $this->loadChildren();
        }
        return $this->children;
    }

    /**
     * @return Summary[]
     */
    protected abstract function loadChildren();
}
