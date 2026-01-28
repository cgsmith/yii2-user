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
 * Registration form model.
 */
class RegistrationForm extends Model
{
    public ?string $email = null;
    public ?string $username = null;
    public ?string $password = null;
    public bool $gdprConsent = false;
    public bool $gdprMarketingConsent = false;
    public ?string $captcha = null;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $module = $this->getModule();

        $rules = [
            // Email
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'message' => Yii::t('user', 'This email address has already been taken.')],

            // Username (optional)
            ['username', 'trim'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'match', 'pattern' => '/^[-a-zA-Z0-9_\.]+$/', 'message' => Yii::t('user', 'Username can only contain alphanumeric characters, underscores, hyphens, and dots.')],
            ['username', 'unique', 'targetClass' => User::class, 'message' => Yii::t('user', 'This username has already been taken.')],

            // GDPR consent
            ['gdprConsent', 'boolean'],
            ['gdprMarketingConsent', 'boolean'],

            // Captcha
            ['captcha', 'safe'],
        ];

        // Password rules (unless generated)
        if (!$module->enableGeneratedPassword) {
            $rules[] = ['password', 'required'];
            $rules[] = ['password', 'string', 'min' => $module->minPasswordLength, 'max' => $module->maxPasswordLength];
        }

        // GDPR consent required if enabled
        if ($module->enableGdprConsent && $module->requireGdprConsentBeforeRegistration) {
            $rules[] = ['gdprConsent', 'required'];
            $rules[] = ['gdprConsent', 'compare', 'compareValue' => true, 'message' => Yii::t('user', 'You must accept the privacy policy to register.')];
        }

        // CAPTCHA required if enabled
        if ($module->enableCaptcha && in_array('register', $module->captchaForms, true)) {
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
            CaptchaService::TYPE_YII => ['captcha', 'captcha', 'captchaAction' => '/user/registration/captcha'],
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
            'username' => Yii::t('user', 'Username'),
            'password' => Yii::t('user', 'Password'),
            'gdprConsent' => Yii::t('user', 'I have read and accept the privacy policy'),
            'gdprMarketingConsent' => Yii::t('user', 'I agree to receive marketing communications'),
        ];
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
