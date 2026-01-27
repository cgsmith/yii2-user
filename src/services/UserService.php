<?php

declare(strict_types=1);

namespace cgsmith\user\services;

use cgsmith\user\controllers\AdminController;
use cgsmith\user\helpers\Password;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use Yii;
use yii\base\InvalidCallException;

/**
 * User management service.
 */
class UserService
{
    public function __construct(
        protected Module $module
    ) {}

    /**
     * Create a new user (admin creation).
     */
    public function create(string $email, string $password, bool $confirmed = true): ?User
    {
        $user = new User();
        $user->email = $email;
        $user->password = $password;

        if ($confirmed) {
            $user->status = User::STATUS_ACTIVE;
            $user->email_confirmed_at = date('Y-m-d H:i:s');
        }

        if (!$user->save()) {
            Yii::error('Failed to create user: ' . json_encode($user->errors), __METHOD__);
            return null;
        }

        return $user;
    }

    /**
     * Update user.
     */
    public function update(User $user, array $attributes): bool
    {
        $user->setAttributes($attributes);

        if (!$user->save()) {
            Yii::error('Failed to update user: ' . json_encode($user->errors), __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * Delete user.
     */
    public function delete(User $user): bool
    {
        return $user->delete() !== false;
    }

    /**
     * Block user.
     */
    public function block(User $user): bool
    {
        return $user->block();
    }

    /**
     * Unblock user.
     */
    public function unblock(User $user): bool
    {
        return $user->unblock();
    }

    /**
     * Confirm user email.
     */
    public function confirm(User $user): bool
    {
        return $user->confirm();
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user, string $password): bool
    {
        return $user->resetPassword($password);
    }

    /**
     * Generate a new password and send it to the user.
     *
     * @throws InvalidCallException if user is an admin
     */
    public function resendPassword(User $user, MailerService $mailer): bool
    {
        if ($user->getIsAdmin()) {
            throw new InvalidCallException(Yii::t('user', 'Password generation is not allowed for admin users.'));
        }

        $password = Password::generate($this->module->minPasswordLength);

        if (!$user->resetPassword($password)) {
            return false;
        }

        return $mailer->sendGeneratedPasswordMessage($user, $password);
    }

    /**
     * Find user by ID.
     */
    public function findById(int $id): ?User
    {
        return User::findOne($id);
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return User::findByEmail($email);
    }

    /**
     * Find user by username.
     */
    public function findByUsername(string $username): ?User
    {
        return User::findByUsername($username);
    }

    /**
     * Check if user can be impersonated by current user.
     */
    public function canImpersonate(User $targetUser): bool
    {
        if (!$this->module->enableImpersonation) {
            return false;
        }

        $currentUser = Yii::$app->user->identity;

        if (!$currentUser instanceof User) {
            return false;
        }

        // Can't impersonate yourself
        if ($currentUser->id === $targetUser->id) {
            return false;
        }

        // Check admin permission
        if (!$currentUser->getIsAdmin()) {
            return false;
        }

        // Check impersonate permission if configured
        if ($this->module->impersonatePermission !== null && Yii::$app->authManager !== null) {
            return Yii::$app->authManager->checkAccess($currentUser->id, $this->module->impersonatePermission);
        }

        return true;
    }

    /**
     * Impersonate a user.
     *
     * @return string|null Previous user auth key for reverting, or null on failure
     */
    public function impersonate(User $targetUser): ?string
    {
        if (!$this->canImpersonate($targetUser)) {
            return null;
        }

        $currentUser = Yii::$app->user->identity;
        $previousAuthKey = $currentUser->auth_key;

        // Store original user for reverting
        Yii::$app->session->set(AdminController::ORIGINAL_USER_SESSION_KEY, $currentUser->id);

        // Login as target user
        Yii::$app->user->login($targetUser);

        return $previousAuthKey;
    }

    /**
     * Stop impersonating and return to original user.
     */
    public function stopImpersonation(): bool
    {
        $originalUserId = Yii::$app->session->get(AdminController::ORIGINAL_USER_SESSION_KEY);

        if ($originalUserId === null) {
            return false;
        }

        $originalUser = $this->findById($originalUserId);

        if ($originalUser === null) {
            return false;
        }

        Yii::$app->session->remove(AdminController::ORIGINAL_USER_SESSION_KEY);
        Yii::$app->user->login($originalUser);

        return true;
    }

    /**
     * Check if current user is impersonating.
     */
    public function isImpersonating(): bool
    {
        return Yii::$app->session->has(AdminController::ORIGINAL_USER_SESSION_KEY);
    }
}
