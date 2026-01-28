<?php

declare(strict_types=1);

namespace cgsmith\user\widgets;

use cgsmith\user\Module;
use cgsmith\user\services\CaptchaService;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Universal CAPTCHA widget that renders the appropriate CAPTCHA type.
 *
 * Usage:
 * ```php
 * <?= Captcha::widget(['form' => $form, 'model' => $model, 'attribute' => 'captcha', 'formType' => 'login']) ?>
 * ```
 */
class Captcha extends Widget
{
    public $form;
    public $model;
    public string $attribute = 'captcha';
    public string $formType = 'login';

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        if (!$module->enableCaptcha) {
            return '';
        }

        /** @var CaptchaService $captchaService */
        $captchaService = Yii::$container->get(CaptchaService::class);

        if (!$captchaService->isEnabledForForm($this->formType)) {
            return '';
        }

        return match ($module->captchaType) {
            CaptchaService::TYPE_YII => $this->renderYiiCaptcha(),
            CaptchaService::TYPE_RECAPTCHA_V2 => $this->renderReCaptchaV2(),
            CaptchaService::TYPE_RECAPTCHA_V3 => $this->renderReCaptchaV3(),
            CaptchaService::TYPE_HCAPTCHA => $this->renderHCaptcha(),
            default => '',
        };
    }

    /**
     * Render Yii's built-in CAPTCHA.
     */
    protected function renderYiiCaptcha(): string
    {
        if ($this->form === null) {
            return '';
        }

        return $this->form->field($this->model, $this->attribute)->widget(\yii\captcha\Captcha::class, [
            'captchaAction' => '/user/security/captcha',
        ]);
    }

    /**
     * Render Google reCAPTCHA v2 checkbox.
     */
    protected function renderReCaptchaV2(): string
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        if (empty($module->reCaptchaSiteKey)) {
            return '';
        }

        $this->registerReCaptchaScript();

        $html = '<div class="form-group">';
        $html .= '<div class="g-recaptcha" data-sitekey="' . Html::encode($module->reCaptchaSiteKey) . '"></div>';
        $html .= Html::activeHiddenInput($this->model, $this->attribute, ['id' => Html::getInputId($this->model, $this->attribute)]);
        $html .= '</div>';

        $this->registerReCaptchaV2Callback();

        return $html;
    }

    /**
     * Render Google reCAPTCHA v3 (invisible).
     */
    protected function renderReCaptchaV3(): string
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        if (empty($module->reCaptchaSiteKey)) {
            return '';
        }

        $this->registerReCaptchaV3Script();

        /** @var CaptchaService $captchaService */
        $captchaService = Yii::$container->get(CaptchaService::class);
        $action = $captchaService->getReCaptchaAction($this->formType);

        $html = Html::activeHiddenInput($this->model, $this->attribute, [
            'id' => Html::getInputId($this->model, $this->attribute),
        ]);

        $this->registerReCaptchaV3Callback($action);

        return $html;
    }

    /**
     * Render hCaptcha.
     */
    protected function renderHCaptcha(): string
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        if (empty($module->hCaptchaSiteKey)) {
            return '';
        }

        $this->registerHCaptchaScript();

        $html = '<div class="form-group">';
        $html .= '<div class="h-captcha" data-sitekey="' . Html::encode($module->hCaptchaSiteKey) . '"></div>';
        $html .= Html::activeHiddenInput($this->model, $this->attribute, ['id' => Html::getInputId($this->model, $this->attribute)]);
        $html .= '</div>';

        $this->registerHCaptchaCallback();

        return $html;
    }

    /**
     * Register reCAPTCHA v2 script.
     */
    protected function registerReCaptchaScript(): void
    {
        $this->view->registerJsFile('https://www.google.com/recaptcha/api.js', [
            'async' => true,
            'defer' => true,
        ]);
    }

    /**
     * Register reCAPTCHA v2 callback.
     */
    protected function registerReCaptchaV2Callback(): void
    {
        $inputId = Html::getInputId($this->model, $this->attribute);
        $js = <<<JS
window.recaptchaCallback = function(response) {
    document.getElementById('{$inputId}').value = response;
};
JS;
        $this->view->registerJs($js);
    }

    /**
     * Register reCAPTCHA v3 script.
     */
    protected function registerReCaptchaV3Script(): void
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        $this->view->registerJsFile(
            'https://www.google.com/recaptcha/api.js?render=' . Html::encode($module->reCaptchaSiteKey),
            ['async' => true, 'defer' => true]
        );
    }

    /**
     * Register reCAPTCHA v3 callback.
     */
    protected function registerReCaptchaV3Callback(string $action): void
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        $siteKey = Html::encode($module->reCaptchaSiteKey);
        $inputId = Html::getInputId($this->model, $this->attribute);

        $js = <<<JS
grecaptcha.ready(function() {
    grecaptcha.execute('{$siteKey}', {action: '{$action}'}).then(function(token) {
        document.getElementById('{$inputId}').value = token;
    });
});
JS;
        $this->view->registerJs($js);
    }

    /**
     * Register hCaptcha script.
     */
    protected function registerHCaptchaScript(): void
    {
        $this->view->registerJsFile('https://js.hcaptcha.com/1/api.js', [
            'async' => true,
            'defer' => true,
        ]);
    }

    /**
     * Register hCaptcha callback.
     */
    protected function registerHCaptchaCallback(): void
    {
        $inputId = Html::getInputId($this->model, $this->attribute);
        $js = <<<JS
window.hcaptchaCallback = function(response) {
    document.getElementById('{$inputId}').value = response;
};
JS;
        $this->view->registerJs($js);
    }
}
