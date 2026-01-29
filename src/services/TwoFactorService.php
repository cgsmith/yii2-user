<?php

declare(strict_types=1);

namespace cgsmith\user\services;

use cgsmith\user\events\TwoFactorEvent;
use cgsmith\user\models\TwoFactor;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use Yii;
use yii\db\Expression;

/**
 * Service for two-factor authentication management.
 */
class TwoFactorService
{
    public const EVENT_BEFORE_ENABLE = 'beforeTwoFactorEnable';
    public const EVENT_AFTER_ENABLE = 'afterTwoFactorEnable';
    public const EVENT_BEFORE_DISABLE = 'beforeTwoFactorDisable';
    public const EVENT_AFTER_DISABLE = 'afterTwoFactorDisable';
    public const EVENT_VERIFIED = 'twoFactorVerified';
    public const EVENT_BACKUP_USED = 'twoFactorBackupUsed';

    private const SESSION_2FA_USER_ID = '__2fa_user_id';
    private const SESSION_2FA_REMEMBER = '__2fa_remember';

    public function __construct(
        private readonly Module $module
    ) {
    }

    /**
     * Generate a new secret key for TOTP.
     */
    public function generateSecret(): string
    {
        if (!class_exists('\PragmaRX\Google2FA\Google2FA')) {
            throw new \RuntimeException('pragmarx/google2fa package is required for 2FA support');
        }

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        return $google2fa->generateSecretKey();
    }

    /**
     * Generate QR code URL for authenticator app.
     */
    public function getQrCodeUrl(User $user, string $secret): string
    {
        if (!class_exists('\PragmaRX\Google2FA\Google2FA')) {
            throw new \RuntimeException('pragmarx/google2fa package is required for 2FA support');
        }

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $issuer = $this->module->twoFactorIssuer ?: Yii::$app->name;
        $holder = $user->email;

        return $google2fa->getQRCodeUrl($issuer, $holder, $secret);
    }

    /**
     * Generate QR code as data URI (base64 PNG).
     */
    public function getQrCodeDataUri(User $user, string $secret): string
    {
        $qrCodeUrl = $this->getQrCodeUrl($user, $secret);

        if (!class_exists('\BaconQrCode\Renderer\ImageRenderer')) {
            return '';
        }

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);

        $svg = $writer->writeString($qrCodeUrl);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Verify a TOTP code.
     */
    public function verifyCode(string $secret, string $code): bool
    {
        if (!class_exists('\PragmaRX\Google2FA\Google2FA')) {
            return false;
        }

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        return $google2fa->verifyKey($secret, $code);
    }

    /**
     * Enable 2FA for a user.
     */
    public function enable(User $user, string $secret, string $code): bool
    {
        if (!$this->verifyCode($secret, $code)) {
            return false;
        }

        $event = new TwoFactorEvent([
            'user' => $user,
            'type' => TwoFactorEvent::TYPE_ENABLED,
        ]);
        $this->module->trigger(self::EVENT_BEFORE_ENABLE, $event);

        $twoFactor = TwoFactor::find()->byUser($user->id)->one();
        if ($twoFactor === null) {
            $twoFactor = new TwoFactor();
            $twoFactor->user_id = $user->id;
        }

        $twoFactor->secret = $secret;
        $twoFactor->enabled_at = new Expression('NOW()');
        $twoFactor->backup_codes = $this->generateBackupCodes();

        if ($twoFactor->save()) {
            $this->module->trigger(self::EVENT_AFTER_ENABLE, $event);
            return true;
        }

        return false;
    }

    /**
     * Disable 2FA for a user.
     */
    public function disable(User $user): bool
    {
        $twoFactor = TwoFactor::find()->byUser($user->id)->one();
        if ($twoFactor === null) {
            return true;
        }

        $event = new TwoFactorEvent([
            'user' => $user,
            'type' => TwoFactorEvent::TYPE_DISABLED,
        ]);
        $this->module->trigger(self::EVENT_BEFORE_DISABLE, $event);

        if ($twoFactor->delete()) {
            $this->module->trigger(self::EVENT_AFTER_DISABLE, $event);
            return true;
        }

        return false;
    }

    /**
     * Check if user has 2FA enabled.
     */
    public function isEnabled(User $user): bool
    {
        $twoFactor = TwoFactor::find()->byUser($user->id)->enabled()->one();
        return $twoFactor !== null;
    }

    /**
     * Check if user requires 2FA.
     */
    public function isRequired(User $user): bool
    {
        if (!$this->module->enableTwoFactor) {
            return false;
        }

        if ($this->module->twoFactorRequireForAdmins && $user->getIsAdmin()) {
            return true;
        }

        return $this->isEnabled($user);
    }

    /**
     * Verify 2FA code or backup code.
     */
    public function verify(User $user, string $code): bool
    {
        $twoFactor = TwoFactor::find()->byUser($user->id)->enabled()->one();
        if ($twoFactor === null) {
            return false;
        }

        if ($this->verifyCode($twoFactor->secret, $code)) {
            $event = new TwoFactorEvent([
                'user' => $user,
                'type' => TwoFactorEvent::TYPE_VERIFIED,
            ]);
            $this->module->trigger(self::EVENT_VERIFIED, $event);
            return true;
        }

        if ($this->verifyBackupCode($twoFactor, $code)) {
            $event = new TwoFactorEvent([
                'user' => $user,
                'type' => TwoFactorEvent::TYPE_BACKUP_USED,
            ]);
            $this->module->trigger(self::EVENT_BACKUP_USED, $event);
            return true;
        }

        return false;
    }

    /**
     * Verify and consume a backup code.
     */
    protected function verifyBackupCode(TwoFactor $twoFactor, string $code): bool
    {
        $backupCodes = $twoFactor->backup_codes ?? [];

        $normalizedCode = strtoupper(str_replace(['-', ' '], '', $code));

        $index = array_search($normalizedCode, array_map('strtoupper', $backupCodes), true);
        if ($index === false) {
            return false;
        }

        unset($backupCodes[$index]);
        $twoFactor->backup_codes = array_values($backupCodes);
        $twoFactor->save(false, ['backup_codes']);

        return true;
    }

    /**
     * Generate backup codes.
     */
    public function generateBackupCodes(): array
    {
        $codes = [];
        $count = $this->module->twoFactorBackupCodesCount;

        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Yii::$app->security->generateRandomString(8));
        }

        return $codes;
    }

    /**
     * Regenerate backup codes for a user.
     */
    public function regenerateBackupCodes(User $user): ?array
    {
        $twoFactor = TwoFactor::find()->byUser($user->id)->enabled()->one();
        if ($twoFactor === null) {
            return null;
        }

        $codes = $this->generateBackupCodes();
        $twoFactor->backup_codes = $codes;

        if ($twoFactor->save(false, ['backup_codes'])) {
            return $codes;
        }

        return null;
    }

    /**
     * Get remaining backup codes count.
     */
    public function getBackupCodesCount(User $user): int
    {
        $twoFactor = TwoFactor::find()->byUser($user->id)->one();
        if ($twoFactor === null) {
            return 0;
        }

        return count($twoFactor->backup_codes ?? []);
    }

    /**
     * Store pending 2FA user ID in session.
     */
    public function storePending2FAUser(int $userId, bool $rememberMe = false): void
    {
        Yii::$app->session->set(self::SESSION_2FA_USER_ID, $userId);
        Yii::$app->session->set(self::SESSION_2FA_REMEMBER, $rememberMe);
    }

    /**
     * Get pending 2FA user ID from session.
     */
    public function getPending2FAUserId(): ?int
    {
        return Yii::$app->session->get(self::SESSION_2FA_USER_ID);
    }

    /**
     * Get pending 2FA remember me setting.
     */
    public function getPending2FARememberMe(): bool
    {
        return (bool) Yii::$app->session->get(self::SESSION_2FA_REMEMBER, false);
    }

    /**
     * Clear pending 2FA session data.
     */
    public function clearPending2FA(): void
    {
        Yii::$app->session->remove(self::SESSION_2FA_USER_ID);
        Yii::$app->session->remove(self::SESSION_2FA_REMEMBER);
    }

    /**
     * Get TwoFactor model for user.
     */
    public function getTwoFactor(User $user): ?TwoFactor
    {
        return TwoFactor::find()->byUser($user->id)->one();
    }
}
