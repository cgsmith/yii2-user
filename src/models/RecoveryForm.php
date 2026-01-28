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
 * Password recovery request form.
 */
class RecoveryForm extends Model
{
    public ?string $email = null;
    public ?string $captcha = null;

    private ?User $_user = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $module = $this->getModule();

        $rules = [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'validateEmail'],
            ['captcha', 'safe'],
        ];

        if ($module->enableCaptcha && in_array('recovery', $module->captchaForms, true)) {
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
            CaptchaService::TYPE_YII => ['captcha', 'captcha', 'captchaAction' => '/user/recovery/captcha'],
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
            'email' => Yii::t('user', 'Email'),
        ];
    }

    /**
     * Validate that the email exists.
     */
    public function validateEmail(string $attribute): void
    {
        $user = $this->getUser();

        if ($user === null) {
            // Don't reveal that the email doesn't exist (security)
            return;
        }

        if ($user->getIsBlocked()) {
            $this->addError($attribute, Yii::t('user', 'Your account has been blocked.'));
        }
    }

    /**
     * Get the user by email.
     */
    public function getUser(): ?User
    {
        if ($this->_user === null && $this->email !== null) {
            $this->_user = User::findByEmail($this->email);
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
