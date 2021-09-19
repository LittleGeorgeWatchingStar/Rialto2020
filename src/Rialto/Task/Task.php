<?php

namespace Rialto\Task;

use DateTime;

/**
 * A task which a user needs to perform at some point.
 */
abstract class Task
{
    const COMMITMENT_TASK_LABEL = 'Commitment';

    const DUE_TASK_LABEL = 'Due';
    /**
     * @var int
     */
    private $id;

    /** @var DateTime */
    private $dateCreated;

    /**
     * The user roles allowed to do this task.
     * @var string[]
     */
    private $roles = [];

    /**
     * The name of the task, for display purposes.
     * @var string
     */
    private $name;

    /**
     * The route name of the page where the user can do the task.
     * @var string
     */
    private $routeName;

    /**
     * The parameters of the route. @see $routeName
     * @var string[]
     */
    private $routeParams = [];

    /**
     * @var string
     */
    private $status = '';

    public static function create($name = null)
    {
        return new static($name);
    }

    public function __construct($name = null, $routeName = null, array $routeParams = [], array $roles = [])
    {
        $this->dateCreated = new DateTime();
        $this->setName($name);
        $this->setRoute($routeName, $routeParams);
        $this->addRoles($roles);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return clone $this->dateCreated;
    }

    /**
     * @param string[] $roles
     */
    public function addRoles(array $roles)
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }
        return $this;
    }

    /**
     * @param string $role
     */
    public function addRole($role)
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    /**
     * Get roles
     *
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * True if this task is assigned to any of the given roles.
     *
     * @param string|string[] $roles
     * @return bool
     */
    public function hasRole($roles)
    {
        $roles = is_array($roles) ? $roles : [$roles];
        foreach ($roles as $role) {
            if (in_array((string) $role, $this->roles)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = trim($name) ?: null;
        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @return string[]
     */
    public function getRouteParams()
    {
        return $this->routeParams;
    }

    public function setRoute($name, array $params = [])
    {
        $this->routeName = trim($name) ?: null;
        $this->routeParams = $params;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    protected function setStatus($status)
    {
        $this->status = trim($status);
        return $this;
    }
}
