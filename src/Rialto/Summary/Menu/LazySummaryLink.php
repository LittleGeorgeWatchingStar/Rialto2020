<?php

namespace Rialto\Summary\Menu;


/**
 * A summary link that lazily loads its status via Ajax.
 *
 * Useful when the status is slow to load (eg, number of IMAP mailbox messages).
 */
class LazySummaryLink extends SummaryLink
{
    /** @var string */
    private $route;

    /** @var string[] */
    private $routeParams;

    public function __construct(string $id,
                                string $uri,
                                string $label,
                                string $route,
                                array $routeParams = [])
    {
        parent::__construct($id, $uri, $label);
        $this->route = $route;
        $this->routeParams = $routeParams;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @return string[]
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }
}
