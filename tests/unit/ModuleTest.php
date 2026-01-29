<?php

declare(strict_types=1);

namespace tests\unit;

use Codeception\Test\Unit;
use cgsmith\user\Module;

class ModuleTest extends Unit
{
    private Module $module;

    protected function _before(): void
    {
        $this->module = new Module('user');
    }

    public function testVersionReturnsString(): void
    {
        $this->assertIsString($this->module->getVersion());
        $this->assertEquals(Module::VERSION, $this->module->getVersion());
    }

    public function testDefaultModelMapReturnsUserClass(): void
    {
        $userClass = $this->module->getModelClass('User');
        $this->assertEquals('cgsmith\user\models\User', $userClass);
    }

    public function testDefaultModelMapReturnsProfileClass(): void
    {
        $profileClass = $this->module->getModelClass('Profile');
        $this->assertEquals('cgsmith\user\models\Profile', $profileClass);
    }

    public function testDefaultModelMapReturnsTokenClass(): void
    {
        $tokenClass = $this->module->getModelClass('Token');
        $this->assertEquals('cgsmith\user\models\Token', $tokenClass);
    }

    public function testDefaultModelMapReturnsLoginFormClass(): void
    {
        $loginFormClass = $this->module->getModelClass('LoginForm');
        $this->assertEquals('cgsmith\user\models\LoginForm', $loginFormClass);
    }

    public function testCustomModelMapOverridesDefault(): void
    {
        $this->module->modelMap = [
            'User' => 'app\models\CustomUser',
        ];

        $userClass = $this->module->getModelClass('User');
        $this->assertEquals('app\models\CustomUser', $userClass);
    }

    public function testGetModelClassThrowsExceptionForUnknownModel(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown model: NonExistent');

        $this->module->getModelClass('NonExistent');
    }

    public function testEmailChangeStrategyConstants(): void
    {
        $this->assertEquals(0, Module::EMAIL_CHANGE_INSECURE);
        $this->assertEquals(1, Module::EMAIL_CHANGE_DEFAULT);
        $this->assertEquals(2, Module::EMAIL_CHANGE_SECURE);
    }

    public function testDefaultConfiguration(): void
    {
        $this->assertTrue($this->module->enableRegistration);
        $this->assertTrue($this->module->enableConfirmation);
        $this->assertFalse($this->module->enableUnconfirmedLogin);
        $this->assertTrue($this->module->enablePasswordRecovery);
        $this->assertFalse($this->module->enableGdpr);
        $this->assertFalse($this->module->enableTwoFactor);
        $this->assertFalse($this->module->enableSocialAuth);
        $this->assertFalse($this->module->enableCaptcha);
        $this->assertFalse($this->module->enableSessionHistory);
    }

    public function testDefaultPasswordSettings(): void
    {
        $this->assertEquals(8, $this->module->minPasswordLength);
        $this->assertEquals(72, $this->module->maxPasswordLength);
        $this->assertEquals(12, $this->module->cost);
    }

    public function testDefaultTokenExpiration(): void
    {
        $this->assertEquals(86400, $this->module->confirmWithin);
        $this->assertEquals(21600, $this->module->recoverWithin);
        $this->assertEquals(1209600, $this->module->rememberFor);
    }

    public function testDefaultAvatarSettings(): void
    {
        $this->assertTrue($this->module->enableGravatar);
        $this->assertTrue($this->module->enableAvatarUpload);
        $this->assertEquals('@webroot/uploads/avatars', $this->module->avatarPath);
        $this->assertEquals('@web/uploads/avatars', $this->module->avatarUrl);
        $this->assertEquals(2097152, $this->module->maxAvatarSize);
        $this->assertEquals(['jpg', 'jpeg', 'png', 'gif', 'webp'], $this->module->avatarExtensions);
    }

    public function testDefaultUrlPrefix(): void
    {
        $this->assertEquals('user', $this->module->urlPrefix);
    }

    public function testMailerSenderWithDefault(): void
    {
        $sender = $this->module->getMailerSender();
        $this->assertIsArray($sender);
    }

    public function testMailerSenderWithCustomConfig(): void
    {
        $this->module->mailer = [
            'sender' => ['custom@example.com' => 'Custom Sender'],
        ];

        $sender = $this->module->getMailerSender();
        $this->assertEquals(['custom@example.com' => 'Custom Sender'], $sender);
    }

    public function testDefaultCaptchaSettings(): void
    {
        $this->assertEquals('yii', $this->module->captchaType);
        $this->assertEquals(['register'], $this->module->captchaForms);
        $this->assertEquals(0.5, $this->module->reCaptchaV3Threshold);
    }

    public function testDefaultTwoFactorSettings(): void
    {
        $this->assertEquals('', $this->module->twoFactorIssuer);
        $this->assertEquals(10, $this->module->twoFactorBackupCodesCount);
        $this->assertFalse($this->module->twoFactorRequireForAdmins);
    }

    public function testDefaultGdprSettings(): void
    {
        $this->assertEquals('1.0', $this->module->gdprConsentVersion);
        $this->assertNull($this->module->gdprConsentUrl);
        $this->assertEquals([], $this->module->gdprExemptRoutes);
        $this->assertTrue($this->module->requireGdprConsentBeforeRegistration);
    }

    public function testDefaultSessionSettings(): void
    {
        $this->assertEquals(10, $this->module->sessionHistoryLimit);
        $this->assertFalse($this->module->enableSessionSeparation);
        $this->assertEquals('BACKENDSESSID', $this->module->backendSessionName);
        $this->assertEquals('PHPSESSID', $this->module->frontendSessionName);
    }

    public function testDefaultSocialAuthSettings(): void
    {
        $this->assertTrue($this->module->enableSocialRegistration);
        $this->assertTrue($this->module->enableSocialConnect);
    }

    public function testDefaultRbacSettings(): void
    {
        $this->assertFalse($this->module->enableRbacManagement);
        $this->assertNull($this->module->rbacManagementPermission);
        $this->assertNull($this->module->adminPermission);
        $this->assertNull($this->module->impersonatePermission);
    }

    public function testDefaultActiveFormClass(): void
    {
        $this->assertEquals('yii\widgets\ActiveForm', $this->module->activeFormClass);
    }

    public function testAdminsArrayIsEmpty(): void
    {
        $this->assertEquals([], $this->module->admins);
    }
}
