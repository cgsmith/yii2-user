<?php

declare(strict_types=1);

namespace tests\unit\services;

use Codeception\Test\Unit;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use cgsmith\user\services\GdprService;

class GdprServiceTest extends Unit
{
    private Module $module;
    private GdprService $service;

    protected function _before(): void
    {
        $this->module = new Module('user');
        $this->service = new GdprService($this->module);
    }

    public function testHasValidConsentReturnsTrueWhenGdprDisabled(): void
    {
        $this->module->enableGdprConsent = false;
        $user = new User();

        $this->assertTrue($this->service->hasValidConsent($user));
    }

    public function testHasValidConsentReturnsFalseWhenConsentAtNull(): void
    {
        $this->module->enableGdprConsent = true;
        $user = new User();
        $user->gdpr_consent_at = null;

        $this->assertFalse($this->service->hasValidConsent($user));
    }

    public function testHasValidConsentReturnsTrueWhenVersionMatches(): void
    {
        $this->module->enableGdprConsent = true;
        $this->module->gdprConsentVersion = '2.0';
        $user = new User();
        $user->gdpr_consent_at = '2025-01-01 00:00:00';
        $user->gdpr_consent_version = '2.0';

        $this->assertTrue($this->service->hasValidConsent($user));
    }

    public function testHasValidConsentReturnsFalseWhenVersionMismatches(): void
    {
        $this->module->enableGdprConsent = true;
        $this->module->gdprConsentVersion = '2.0';
        $user = new User();
        $user->gdpr_consent_at = '2025-01-01 00:00:00';
        $user->gdpr_consent_version = '1.0';

        $this->assertFalse($this->service->hasValidConsent($user));
    }

    public function testHasValidConsentReturnsTrueWhenNoVersionConfigured(): void
    {
        $this->module->enableGdprConsent = true;
        $this->module->gdprConsentVersion = null;
        $user = new User();
        $user->gdpr_consent_at = '2025-01-01 00:00:00';

        $this->assertTrue($this->service->hasValidConsent($user));
    }

    public function testNeedsConsentUpdateReturnsFalseWhenDisabled(): void
    {
        $this->module->enableGdprConsent = false;
        $user = new User();

        $this->assertFalse($this->service->needsConsentUpdate($user));
    }

    public function testNeedsConsentUpdateReturnsTrueWhenConsentInvalid(): void
    {
        $this->module->enableGdprConsent = true;
        $this->module->gdprConsentVersion = '2.0';
        $user = new User();
        $user->gdpr_consent_at = '2025-01-01 00:00:00';
        $user->gdpr_consent_version = '1.0';

        $this->assertTrue($this->service->needsConsentUpdate($user));
    }

    public function testIsRouteExemptForBuiltInRoutes(): void
    {
        $this->assertTrue($this->service->isRouteExempt('user/security/login'));
        $this->assertTrue($this->service->isRouteExempt('user/security/logout'));
        $this->assertTrue($this->service->isRouteExempt('user/gdpr/consent'));
        $this->assertTrue($this->service->isRouteExempt('user/gdpr/index'));
        $this->assertTrue($this->service->isRouteExempt('user/gdpr/export'));
        $this->assertTrue($this->service->isRouteExempt('user/gdpr/delete'));
    }

    public function testIsRouteExemptForCustomRoutes(): void
    {
        $this->module->gdprExemptRoutes = ['site/privacy', 'site/terms'];

        $this->assertTrue($this->service->isRouteExempt('site/privacy'));
        $this->assertTrue($this->service->isRouteExempt('site/terms'));
    }

    public function testIsRouteExemptWildcardMatching(): void
    {
        $this->module->gdprExemptRoutes = ['api/*'];

        $this->assertTrue($this->service->isRouteExempt('api/users'));
        $this->assertTrue($this->service->isRouteExempt('api/products'));
    }

    public function testIsRouteExemptReturnsFalseForNonExemptRoute(): void
    {
        $this->module->gdprExemptRoutes = [];

        $this->assertFalse($this->service->isRouteExempt('site/index'));
        $this->assertFalse($this->service->isRouteExempt('user/settings/account'));
    }

    public function testHasMarketingConsentChecksField(): void
    {
        $user = new User();
        $user->gdpr_marketing_consent_at = null;
        $this->assertFalse($this->service->hasMarketingConsent($user));

        $user->gdpr_marketing_consent_at = '2025-01-01 00:00:00';
        $this->assertTrue($this->service->hasMarketingConsent($user));
    }
}
