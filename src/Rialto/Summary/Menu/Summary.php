<?php

namespace Rialto\Summary\Menu;

use Rialto\Security\Role\Role;

/**
 * Interface implemented by all classes that provide the data model for the
 * summary menu.
 */
interface Summary
{
    public function getId(): string;

    public function getLabel(): string;

    /**
     * @return Role[]
     */
    public function getAllowedRoles(): array;
}
