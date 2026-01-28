<?php

declare(strict_types=1);

namespace cgsmith\user\validators;

use cgsmith\user\services\CaptchaService;
use Yii;
use yii\validators\Validator;

/**
 * Validator for Google reCAPTCHA v2 and v3.
 */
class ReCaptchaValidator extends Validator
{
    public bool $skipOnEmpty = false;

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value): ?array
    {
        if (empty($value)) {
            return [Yii::t('user', 'Please complete the CAPTCHA verification.'), []];
        }

        /** @var CaptchaService $captchaService */
        $captchaService = Yii::$container->get(CaptchaService::class);

        if (!$captchaService->verifyReCaptcha($value)) {
            return [Yii::t('user', 'CAPTCHA verification failed. Please try again.'), []];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view): ?string
    {
        return null;
    }
}
