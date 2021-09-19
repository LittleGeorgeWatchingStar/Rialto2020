<?php

namespace Rialto\Summary\Menu\Main;


use Rialto\Database\Orm\DbManager;
use Rialto\Summary\Menu\Summary;
use Rialto\Summary\Menu\SummaryNode;
use Symfony\Component\Routing\RouterInterface;

/**
 * A basic SummaryNode implementation with a router and ObjectManager.
 */
abstract class OrmNodeAbstract implements SummaryNode
{
    /** @var DbManager */
    protected $dbm;

    /** @var RouterInterface */
    protected $router;

    /** @var Summary[] */
    private $children = null;

    public function __construct(DbManager $dbm, RouterInterface $router)
    {
        $this->dbm = $dbm;
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
    protected abstract function loadChildren(): array;
}
