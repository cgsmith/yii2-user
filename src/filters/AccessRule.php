<?php

declare(strict_types=1);

namespace cgsmith\user\filters;

use cgsmith\user\contracts\UserInterface;
use yii\filters\AccessRule as BaseAccessRule;

/**
 * Access rule that supports the 'admin' role.
 *
 * This rule extends Yii's AccessRule to add support for checking
 * if a user is an admin via the UserInterface::getIsAdmin() method.
 */
class AccessRule extends BaseAccessRule
{
    /**
     * {@inheritdoc}
     */
    protected function matchRole($user): bool
    {
        if (empty($this->roles)) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role === '?') {
                if ($user->getIsGuest()) {
                    return true;
                }
            } elseif ($role === '@') {
                if (!$user->getIsGuest()) {
                    return true;
                }
            } elseif ($role === 'admin') {
                // Check if user is admin via UserInterface
                if (!$user->getIsGuest()) {
                    $identity = $user->identity;
                    if ($identity instanceof UserInterface && $identity->getIsAdmin()) {
                        return true;
                    }
                }
            } elseif (!$user->getIsGuest() && $user->can($role)) {
                return true;
            }
        }

        return false;
    }
}
