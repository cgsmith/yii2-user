<?php

declare(strict_types=1);

namespace cgsmith\user\contracts;

use yii\web\IdentityInterface;

/**
 * Interface for User model.
 */
interface UserInterface extends IdentityInterface
{
    /**
     * Check if user is an administrator.
     */
    public function getIsAdmin(): bool;

    /**
     * Check if user is blocked.
     */
    public function getIsBlocked(): bool;

    /**
     * Check if user email is confirmed.
     */
    public function getIsConfirmed(): bool;

    /**
     * Get the user's profile.
     */
    public function getProfile(): mixed;
}
