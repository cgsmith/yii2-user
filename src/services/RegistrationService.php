<?php

declare(strict_types=1);

namespace cgsmith\user\services;

use cgsmith\user\events\RegistrationEvent;
use cgsmith\user\helpers\Password;
use cgsmith\user\models\RegistrationForm;
use cgsmith\user\models\Token;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use Yii;
use yii\db\Transaction;

/**
 * Registration service.
 */
class RegistrationService
{
    public const EVENT_BEFORE_REGISTER = 'beforeRegister';
    public const EVENT_AFTER_REGISTER = 'afterRegister';
    public const EVENT_BEFORE_CONFIRM = 'beforeConfirm';
    public const EVENT_AFTER_CONFIRM = 'afterConfirm';

    public function __construct(
        protected Module $module
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegistrationForm $form): ?User
    {
        if (!$form->validate()) {
            return null;
        }

        $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);

        try {
            $user = new User();
            $user->email = $form->email;
            $user->username = $form->username;

            // Handle password
            if ($this->module->enableGeneratedPassword) {
                $password = Password::generate();
                $user->password = $password;
            } else {
                $user->password = $form->password;
            }

            // Set confirmation status
            if (!$this->module->enableConfirmation) {
                $user->status = User::STATUS_ACTIVE;
                $user->email_confirmed_at = date('Y-m-d H:i:s');
            }

            // Trigger before event
            $event = new RegistrationEvent(['user' => $user, 'form' => $form]);
            $this->module->trigger(self::EVENT_BEFORE_REGISTER, $event);

            if (!$user->save()) {
                $transaction->rollBack();
                Yii::error('Failed to save user: ' . json_encode($user->errors), __METHOD__);
                return null;
            }

            // Create confirmation token if needed
            $token = null;
            if ($this->module->enableConfirmation) {
                $token = Token::createConfirmationToken($user);
                if (!$token->save()) {
                    $transaction->rollBack();
                    Yii::error('Failed to save confirmation token: ' . json_encode($token->errors), __METHOD__);
                    return null;
                }
            }

            // Send welcome email
            $mailer = $this->getMailerService();
            if ($this->module->enableGeneratedPassword) {
                $mailer->sendGeneratedPasswordMessage($user, $password);
            } else {
                $mailer->sendWelcomeMessage($user, $token);
            }

            // Trigger after event
            $event = new RegistrationEvent(['user' => $user, 'form' => $form, 'token' => $token]);
            $this->module->trigger(self::EVENT_AFTER_REGISTER, $event);

            $transaction->commit();

            return $user;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Registration failed: ' . $e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * Confirm user email with token.
     */
    public function confirm(User $user, string $tokenString): bool
    {
        $tokenService = $this->getTokenService();
        $token = $tokenService->findConfirmationToken($tokenString);

        if ($token === null || $token->user_id !== $user->id) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Trigger before event
            $event = new RegistrationEvent(['user' => $user, 'token' => $token]);
            $this->module->trigger(self::EVENT_BEFORE_CONFIRM, $event);

            // Confirm user
            if (!$user->confirm()) {
                $transaction->rollBack();
                return false;
            }

            // Delete token
            $tokenService->deleteToken($token);

            // Trigger after event
            $event = new RegistrationEvent(['user' => $user]);
            $this->module->trigger(self::EVENT_AFTER_CONFIRM, $event);

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Confirmation failed: ' . $e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * Resend confirmation email.
     */
    public function resendConfirmation(User $user): bool
    {
        if ($user->getIsConfirmed()) {
            return false;
        }

        $tokenService = $this->getTokenService();
        $token = $tokenService->createConfirmationToken($user);

        if ($token === null) {
            return false;
        }

        $mailer = $this->getMailerService();

        return $mailer->sendConfirmationMessage($user, $token);
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
