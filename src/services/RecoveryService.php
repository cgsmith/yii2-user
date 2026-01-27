<?php

declare(strict_types=1);

namespace cgsmith\user\services;

use cgsmith\user\models\RecoveryForm;
use cgsmith\user\models\Token;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use Yii;

/**
 * Password recovery service.
 */
class RecoveryService
{
    public function __construct(
        protected Module $module
    ) {}

    /**
     * Send recovery email.
     *
     * @return bool Always returns true to prevent email enumeration attacks
     */
    public function sendRecoveryMessage(RecoveryForm $form): bool
    {
        if (!$form->validate()) {
            return true; // Don't reveal validation errors for security
        }

        $user = $form->getUser();

        if ($user === null || $user->getIsBlocked()) {
            // User not found or blocked - return true to prevent enumeration
            return true;
        }

        $tokenService = $this->getTokenService();
        $token = $tokenService->createRecoveryToken($user);

        if ($token === null) {
            Yii::error('Failed to create recovery token for user: ' . $user->id, __METHOD__);
            return true;
        }

        $mailer = $this->getMailerService();
        $mailer->sendRecoveryMessage($user, $token);

        return true;
    }

    /**
     * Reset password with token.
     */
    public function resetPassword(User $user, string $tokenString, string $password): bool
    {
        $tokenService = $this->getTokenService();
        $token = $tokenService->findRecoveryToken($tokenString);

        if ($token === null || $token->user_id !== $user->id) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Reset password
            if (!$user->resetPassword($password)) {
                $transaction->rollBack();
                return false;
            }

            // Delete token
            $tokenService->deleteToken($token);

            // Delete all other recovery tokens for this user
            Token::deleteAllForUser($user->id, Token::TYPE_RECOVERY);

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Password reset failed: ' . $e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * Validate recovery token.
     */
    public function validateToken(User $user, string $tokenString): bool
    {
        $tokenService = $this->getTokenService();
        $token = $tokenService->findRecoveryToken($tokenString);

        return $token !== null && $token->user_id === $user->id;
    }

    /**
     * Get mailer service.
     */
    protected function getMailerService(): MailerService
    {
        return Yii::$container->get(MailerService::class);
    }

    /**
     * Get token service.
     */
    protected function getTokenService(): TokenService
    {
        return Yii::$container->get(TokenService::class);
    }
}
