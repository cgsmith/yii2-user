<?php

declare(strict_types=1);

namespace cgsmith\user\services;

use cgsmith\user\models\Token;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use Yii;
use yii\helpers\Url;
use yii\mail\MailerInterface;

/**
 * Email service for user-related emails.
 */
class MailerService
{
    public function __construct(
        protected Module $module
    ) {}

    /**
     * Send welcome/confirmation email.
     */
    public function sendWelcomeMessage(User $user, ?Token $token = null): bool
    {
        if ($token !== null) {
            $url = Url::to(['/user/registration/confirm', 'id' => $user->id, 'token' => $token->token], true);
        } else {
            $url = null;
        }

        return $this->sendMessage(
            $user->email,
            Yii::t('user', 'Welcome to {app}', ['app' => Yii::$app->name]),
            'welcome',
            [
                'user' => $user,
                'token' => $token,
                'url' => $url,
                'module' => $this->module,
            ]
        );
    }

    /**
     * Send confirmation email.
     */
    public function sendConfirmationMessage(User $user, Token $token): bool
    {
        $url = Url::to(['/user/registration/confirm', 'id' => $user->id, 'token' => $token->token], true);

        return $this->sendMessage(
            $user->email,
            Yii::t('user', 'Confirm your email on {app}', ['app' => Yii::$app->name]),
            'confirmation',
            [
                'user' => $user,
                'token' => $token,
                'url' => $url,
                'module' => $this->module,
            ]
        );
    }

    /**
     * Send password recovery email.
     */
    public function sendRecoveryMessage(User $user, Token $token): bool
    {
        $url = Url::to(['/user/recovery/reset', 'id' => $user->id, 'token' => $token->token], true);

        return $this->sendMessage(
            $user->email,
            Yii::t('user', 'Password recovery on {app}', ['app' => Yii::$app->name]),
            'recovery',
            [
                'user' => $user,
                'token' => $token,
                'url' => $url,
                'module' => $this->module,
            ]
        );
    }

    /**
     * Send email change confirmation.
     */
    public function sendEmailChangeMessage(User $user, Token $token, string $newEmail): bool
    {
        $url = Url::to(['/user/settings/confirm-email', 'id' => $user->id, 'token' => $token->token], true);

        return $this->sendMessage(
            $newEmail,
            Yii::t('user', 'Confirm email change on {app}', ['app' => Yii::$app->name]),
            'email_change',
            [
                'user' => $user,
                'token' => $token,
                'url' => $url,
                'newEmail' => $newEmail,
                'module' => $this->module,
            ]
        );
    }

    /**
     * Send generated password email.
     */
    public function sendGeneratedPasswordMessage(User $user, string $password): bool
    {
        return $this->sendMessage(
            $user->email,
            Yii::t('user', 'Your account on {app}', ['app' => Yii::$app->name]),
            'generated_password',
            [
                'user' => $user,
                'password' => $password,
                'module' => $this->module,
            ]
        );
    }

    /**
     * Send email message.
     */
    protected function sendMessage(string $to, string $subject, string $view, array $params = []): bool
    {
        $mailer = $this->getMailer();
        $sender = $this->module->getMailerSender();

        $viewPath = $this->module->mailer['viewPath'] ?? '@cgsmith/user/views/mail';

        $message = $mailer->compose([
            'html' => "{$viewPath}/{$view}",
            'text' => "{$viewPath}/{$view}-text",
        ], $params)
            ->setTo($to)
            ->setFrom($sender)
            ->setSubject($subject);

        return $message->send();
    }

    /**
     * Get the mailer component.
     */
    protected function getMailer(): MailerInterface
    {
        $mailerId = $this->module->mailer['mailer'] ?? 'mailer';

        return Yii::$app->get($mailerId);
    }
}
