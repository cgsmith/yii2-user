<?php

declare(strict_types=1);

namespace cgsmith\user\services;

use cgsmith\user\Module;
use Yii;

/**
 * Service for CAPTCHA management and verification.
 */
class CaptchaService
{
    public const TYPE_YII = 'yii';
    public const TYPE_RECAPTCHA_V2 = 'recaptcha-v2';
    public const TYPE_RECAPTCHA_V3 = 'recaptcha-v3';
    public const TYPE_HCAPTCHA = 'hcaptcha';

    public function __construct(
        private readonly Module $module
    ) {
    }

    /**
     * Check if CAPTCHA is enabled for a specific form.
     */
    public function isEnabledForForm(string $formType): bool
    {
        if (!$this->module->enableCaptcha) {
            return false;
        }

        return in_array($formType, $this->module->captchaForms, true);
    }

    /**
     * Get the current CAPTCHA type.
     */
    public function getCaptchaType(): string
    {
        return $this->module->captchaType;
    }

    /**
     * Verify reCAPTCHA response.
     */
    public function verifyReCaptcha(string $response, ?string $remoteIp = null): bool
    {
        if (empty($this->module->reCaptchaSecretKey)) {
            Yii::warning('reCAPTCHA secret key is not configured', __METHOD__);
            return false;
        }

        if (!class_exists('\ReCaptcha\ReCaptcha')) {
            Yii::warning('google/recaptcha package is not installed', __METHOD__);
            return false;
        }

        $recaptcha = new \ReCaptcha\ReCaptcha($this->module->reCaptchaSecretKey);

        if ($remoteIp === null && Yii::$app->request instanceof \yii\web\Request) {
            $remoteIp = Yii::$app->request->userIP;
        }

        $result = $recaptcha->verify($response, $remoteIp);

        if ($this->module->captchaType === self::TYPE_RECAPTCHA_V3) {
            return $result->isSuccess() && $result->getScore() >= $this->module->reCaptchaV3Threshold;
        }

        return $result->isSuccess();
    }

    /**
     * Verify hCaptcha response.
     */
    public function verifyHCaptcha(string $response, ?string $remoteIp = null): bool
    {
        if (empty($this->module->hCaptchaSecretKey)) {
            Yii::warning('hCaptcha secret key is not configured', __METHOD__);
            return false;
        }

        $data = [
            'secret' => $this->module->hCaptchaSecretKey,
            'response' => $response,
        ];

        if ($remoteIp !== null) {
            $data['remoteip'] = $remoteIp;
        } elseif (Yii::$app->request instanceof \yii\web\Request) {
            $data['remoteip'] = Yii::$app->request->userIP;
        }

        $ch = curl_init('https://hcaptcha.com/siteverify');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            Yii::warning('hCaptcha verification request failed', __METHOD__);
            return false;
        }

        $json = json_decode($result, true);

        return isset($json['success']) && $json['success'] === true;
    }

    /**
     * Get the site key for the current CAPTCHA type.
     */
    public function getSiteKey(): ?string
    {
        return match ($this->module->captchaType) {
            self::TYPE_RECAPTCHA_V2, self::TYPE_RECAPTCHA_V3 => $this->module->reCaptchaSiteKey,
            self::TYPE_HCAPTCHA => $this->module->hCaptchaSiteKey,
            default => null,
        };
    }

    /**
     * Get the reCAPTCHA v3 action name for a form.
     */
    public function getReCaptchaAction(string $formType): string
    {
        return match ($formType) {
            'login' => 'login',
            'register' => 'register',
            'recovery' => 'recovery',
            default => 'submit',
        };
    }
}
