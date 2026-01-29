<?php

declare(strict_types=1);

namespace tests\unit\services;

use Codeception\Test\Unit;
use cgsmith\user\Module;
use cgsmith\user\services\CaptchaService;

class CaptchaServiceTest extends Unit
{
    private Module $module;
    private CaptchaService $service;

    protected function _before(): void
    {
        $this->module = new Module('user');
        $this->service = new CaptchaService($this->module);
    }

    public function testIsEnabledForFormReturnsFalseWhenCaptchaDisabled(): void
    {
        $this->module->enableCaptcha = false;

        $this->assertFalse($this->service->isEnabledForForm('login'));
        $this->assertFalse($this->service->isEnabledForForm('register'));
        $this->assertFalse($this->service->isEnabledForForm('recovery'));
    }

    public function testIsEnabledForFormReturnsTrueWhenFormInList(): void
    {
        $this->module->enableCaptcha = true;
        $this->module->captchaForms = ['login', 'register'];

        $this->assertTrue($this->service->isEnabledForForm('login'));
        $this->assertTrue($this->service->isEnabledForForm('register'));
        $this->assertFalse($this->service->isEnabledForForm('recovery'));
    }

    public function testGetCaptchaTypeReturnsConfiguredType(): void
    {
        $this->module->captchaType = CaptchaService::TYPE_RECAPTCHA_V2;

        $this->assertEquals(CaptchaService::TYPE_RECAPTCHA_V2, $this->service->getCaptchaType());
    }

    public function testGetSiteKeyReturnsNullForYiiType(): void
    {
        $this->module->captchaType = CaptchaService::TYPE_YII;

        $this->assertNull($this->service->getSiteKey());
    }

    public function testGetSiteKeyReturnsReCaptchaKey(): void
    {
        $this->module->captchaType = CaptchaService::TYPE_RECAPTCHA_V2;
        $this->module->reCaptchaSiteKey = 'test-site-key';

        $this->assertEquals('test-site-key', $this->service->getSiteKey());
    }

    public function testGetSiteKeyReturnsReCaptchaV3Key(): void
    {
        $this->module->captchaType = CaptchaService::TYPE_RECAPTCHA_V3;
        $this->module->reCaptchaSiteKey = 'test-v3-site-key';

        $this->assertEquals('test-v3-site-key', $this->service->getSiteKey());
    }

    public function testGetSiteKeyReturnsHCaptchaKey(): void
    {
        $this->module->captchaType = CaptchaService::TYPE_HCAPTCHA;
        $this->module->hCaptchaSiteKey = 'hcaptcha-site-key';

        $this->assertEquals('hcaptcha-site-key', $this->service->getSiteKey());
    }

    public function testGetReCaptchaActionReturnsCorrectActions(): void
    {
        $this->assertEquals('login', $this->service->getReCaptchaAction('login'));
        $this->assertEquals('register', $this->service->getReCaptchaAction('register'));
        $this->assertEquals('recovery', $this->service->getReCaptchaAction('recovery'));
        $this->assertEquals('submit', $this->service->getReCaptchaAction('unknown'));
    }

    public function testVerifyReCaptchaReturnsFalseWithoutSecretKey(): void
    {
        $this->module->reCaptchaSecretKey = null;

        $this->assertFalse($this->service->verifyReCaptcha('test-response'));
    }

    public function testVerifyHCaptchaReturnsFalseWithoutSecretKey(): void
    {
        $this->module->hCaptchaSecretKey = null;

        $this->assertFalse($this->service->verifyHCaptcha('test-response'));
    }

    public function testCaptchaTypeConstants(): void
    {
        $this->assertEquals('yii', CaptchaService::TYPE_YII);
        $this->assertEquals('recaptcha-v2', CaptchaService::TYPE_RECAPTCHA_V2);
        $this->assertEquals('recaptcha-v3', CaptchaService::TYPE_RECAPTCHA_V3);
        $this->assertEquals('hcaptcha', CaptchaService::TYPE_HCAPTCHA);
    }
}
