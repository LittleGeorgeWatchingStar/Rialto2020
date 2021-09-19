<?php

namespace Rialto\Summary\Menu;

use Rialto\Security\Role\Role;

/**
 * @author Ian Phillips <ian@gumstix.com>
 */
class SummaryLink implements Summary
{
    /** @var string */
    private $uri;

    /** @var string */
    private $label;

    /** @var string */
    private $id;

    public function __construct(string $id, string $uri, string $label)
    {
        $this->id = $id;
        $this->uri = $uri;
        $this->label = $label;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /** @return string */
    public function getUri()
    {
        return $this->uri;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getAllowedRoles(): array
    {
        return [
            Role::EMPLOYEE,
        ];
    }
}
