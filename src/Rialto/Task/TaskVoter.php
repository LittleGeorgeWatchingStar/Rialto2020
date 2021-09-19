<?php

namespace Rialto\Task;

use Rialto\Security\Role\RoleBasedVoter;

/**
 * Determines whether the current user can perform a Task.
 *
 * @see Task
 */
class TaskVoter extends RoleBasedVoter
{
    /**
     * The user can click on the task link and perform the action.
     */
    const ACCESS = 'access';

    /**
     * The attributes that this voter supports.
     * @return string[]
     */
    protected function getSupportedAttributes()
    {
        return [self::ACCESS];
    }

    /**
     * True if access is granted to $task.
     *
     * @param $task Task
     * @return boolean
     */
    protected function isGranted($attribute, $task, $user = null)
    {
        foreach ( $task->getRoles() as $role ) {
            if ( $this->hasRole($role, $user) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass.
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return [Task::class];
    }

}
