<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\Module;
use cgsmith\user\services\CaptchaService;
use cgsmith\user\validators\HCaptchaValidator;
use cgsmith\user\validators\ReCaptchaValidator;
use Yii;
use yii\base\Model;

/**
 * Login form model.
 */
class LoginForm extends Model
{
    public ?string $login = null;
    public ?string $password = null;
    public bool $rememberMe = false;
    public ?string $captcha = null;

    private ?User $_user = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $module = $this->getModule();

        $rules = [
            [['login', 'password'], 'required'],
            [['login'], 'string'],
            [['password'], 'string'],
            [['rememberMe'], 'boolean'],
            [['password'], 'validatePassword'],
            [['captcha'], 'safe'],
        ];

        if ($module->enableCaptcha && in_array('login', $module->captchaForms, true)) {
            $rules[] = $this->getCaptchaRule($module);
        }

        return $rules;
    }

    /**
     * Get CAPTCHA validation rule based on type.
     */
    protected function getCaptchaRule(Module $module): array
    {
        return match ($module->captchaType) {
            CaptchaService::TYPE_YII => ['captcha', 'captcha', 'captchaAction' => '/user/security/captcha'],
            CaptchaService::TYPE_RECAPTCHA_V2, CaptchaService::TYPE_RECAPTCHA_V3 => ['captcha', ReCaptchaValidator::class],
            CaptchaService::TYPE_HCAPTCHA => ['captcha', HCaptchaValidator::class],
            default => ['captcha', 'safe'],
        };
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'login' => Yii::t('user', 'Email or Username'),
            'password' => Yii::t('user', 'Password'),
            'rememberMe' => Yii::t('user', 'Remember me'),
        ];
    }

    /**
     * Validate the password.
     */
    public function validatePassword(string $attribute): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();

        if ($user === null) {
            $this->addError($attribute, Yii::t('user', 'Invalid login or password.'));
            return;
        }

        if ($user->getIsBlocked()) {
            $this->addError($attribute, Yii::t('user', 'Your account has been blocked.'));
            return;
        }

        $module = $this->getModule();
        if (!$module->enableUnconfirmedLogin && !$user->getIsConfirmed()) {
            $this->addError($attribute, Yii::t('user', 'You need to confirm your email address.'));
            return;
        }

        if (!$user->validatePassword($this->password)) {
            $this->addError($attribute, Yii::t('user', 'Invalid login or password.'));
        }
    }

    /**
     * Attempt to log in the user.
     */
    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->getUser();
        $module = $this->getModule();

        $duration = $this->rememberMe ? $module->rememberFor : 0;

        if (Yii::$app->user->login($user, $duration)) {
            $user->updateLastLogin();
            return true;
        }

        return false;
    }

    /**
     * Get the user by login (email or username).
     */
    public function getUser(): ?User
    {
        if ($this->_user === null && $this->login !== null) {
            $this->_user = User::findByEmailOrUsername($this->login);
        }

        return $this->_user;
    }

    /**
     * Get the user module.
     */
    protected function getModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        return $module;
    }
}
