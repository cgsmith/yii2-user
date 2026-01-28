<?php

declare(strict_types=1);

namespace cgsmith\user\services;

use cgsmith\user\events\GdprEvent;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use Yii;
use yii\db\Expression;

/**
 * Service for GDPR consent management.
 */
class GdprService
{
    public const EVENT_BEFORE_CONSENT = 'beforeGdprConsent';
    public const EVENT_AFTER_CONSENT = 'afterGdprConsent';
    public const EVENT_BEFORE_WITHDRAW = 'beforeGdprWithdraw';
    public const EVENT_AFTER_WITHDRAW = 'afterGdprWithdraw';

    public function __construct(
        private readonly Module $module
    ) {
    }

    /**
     * Record user consent.
     */
    public function recordConsent(User $user, bool $marketingConsent = false): bool
    {
        $event = new GdprEvent([
            'user' => $user,
            'type' => GdprEvent::TYPE_CONSENT,
            'consentVersion' => $this->module->gdprConsentVersion,
            'marketingConsent' => $marketingConsent,
        ]);

        $this->module->trigger(self::EVENT_BEFORE_CONSENT, $event);

        $user->gdpr_consent_at = new Expression('NOW()');
        $user->gdpr_consent_version = $this->module->gdprConsentVersion;

        if ($marketingConsent) {
            $user->gdpr_marketing_consent_at = new Expression('NOW()');
        } else {
            $user->gdpr_marketing_consent_at = null;
        }

        $result = $user->save(false, ['gdpr_consent_at', 'gdpr_consent_version', 'gdpr_marketing_consent_at']);

        if ($result) {
            $this->module->trigger(self::EVENT_AFTER_CONSENT, $event);
        }

        return $result;
    }

    /**
     * Withdraw user consent.
     */
    public function withdrawConsent(User $user): bool
    {
        $event = new GdprEvent([
            'user' => $user,
            'type' => GdprEvent::TYPE_WITHDRAW,
        ]);

        $this->module->trigger(self::EVENT_BEFORE_WITHDRAW, $event);

        $user->gdpr_consent_at = null;
        $user->gdpr_consent_version = null;
        $user->gdpr_marketing_consent_at = null;

        $result = $user->save(false, ['gdpr_consent_at', 'gdpr_consent_version', 'gdpr_marketing_consent_at']);

        if ($result) {
            $this->module->trigger(self::EVENT_AFTER_WITHDRAW, $event);
        }

        return $result;
    }

    /**
     * Check if user has given consent to the current version.
     */
    public function hasValidConsent(User $user): bool
    {
        if (!$this->module->enableGdprConsent) {
            return true;
        }

        if ($user->gdpr_consent_at === null) {
            return false;
        }

        if ($this->module->gdprConsentVersion === null) {
            return true;
        }

        return $user->gdpr_consent_version === $this->module->gdprConsentVersion;
    }

    /**
     * Check if user needs to update their consent.
     */
    public function needsConsentUpdate(User $user): bool
    {
        if (!$this->module->enableGdprConsent) {
            return false;
        }

        return !$this->hasValidConsent($user);
    }

    /**
     * Check if a route is exempt from GDPR consent requirement.
     */
    public function isRouteExempt(string $route): bool
    {
        $exemptRoutes = array_merge(
            $this->module->gdprExemptRoutes,
            [
                'user/security/login',
                'user/security/logout',
                'user/gdpr/consent',
                'user/gdpr/index',
                'user/gdpr/export',
                'user/gdpr/delete',
            ]
        );

        foreach ($exemptRoutes as $exempt) {
            if ($exempt === $route) {
                return true;
            }
            if (str_ends_with($exempt, '*') && str_starts_with($route, rtrim($exempt, '*'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get user's current marketing consent status.
     */
    public function hasMarketingConsent(User $user): bool
    {
        return $user->gdpr_marketing_consent_at !== null;
    }

    /**
     * Update marketing consent only.
     */
    public function updateMarketingConsent(User $user, bool $consent): bool
    {
        if ($consent) {
            $user->gdpr_marketing_consent_at = new Expression('NOW()');
        } else {
            $user->gdpr_marketing_consent_at = null;
        }

        return $user->save(false, ['gdpr_marketing_consent_at']);
    }
}
