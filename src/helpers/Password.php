<?php

declare(strict_types=1);

namespace cgsmith\user\helpers;

use Yii;

/**
 * Password helper for hashing and validation.
 */
class Password
{
    /**
     * Hash a password.
     */
    public static function hash(string $password, int $cost = 12): string
    {
        return Yii::$app->security->generatePasswordHash($password, $cost);
    }

    /**
     * Validate a password against a hash.
     */
    public static function validate(string $password, string $hash): bool
    {
        return Yii::$app->security->validatePassword($password, $hash);
    }

    /**
     * Generate a random password.
     */
    public static function generate(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    /**
     * Check password strength.
     *
     * @return array Array with 'score' (0-4) and 'feedback' messages
     */
    public static function checkStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Length check
        $length = strlen($password);
        if ($length >= 8) $score++;
        if ($length >= 12) $score++;
        if ($length < 8) {
            $feedback[] = Yii::t('user', 'Password should be at least 8 characters.');
        }

        // Lowercase
        if (preg_match('/[a-z]/', $password)) {
            $score += 0.5;
        } else {
            $feedback[] = Yii::t('user', 'Add lowercase letters.');
        }

        // Uppercase
        if (preg_match('/[A-Z]/', $password)) {
            $score += 0.5;
        } else {
            $feedback[] = Yii::t('user', 'Add uppercase letters.');
        }

        // Numbers
        if (preg_match('/[0-9]/', $password)) {
            $score += 0.5;
        } else {
            $feedback[] = Yii::t('user', 'Add numbers.');
        }

        // Special characters
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 0.5;
        } else {
            $feedback[] = Yii::t('user', 'Add special characters.');
        }

        return [
            'score' => min(4, (int) $score),
            'feedback' => $feedback,
        ];
    }
}
