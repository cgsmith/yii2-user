<?php

declare(strict_types=1);

namespace cgsmith\user\services;

use cgsmith\user\models\Token;
use cgsmith\user\models\User;
use cgsmith\user\Module;

/**
 * Token management service.
 */
class TokenService
{
    public function __construct(
        protected Module $module
    ) {}

    /**
     * Create a confirmation token.
     */
    public function createConfirmationToken(User $user): ?Token
    {
        // Delete existing confirmation tokens
        Token::deleteAllForUser($user->id, Token::TYPE_CONFIRMATION);

        $token = Token::createConfirmationToken($user);

        return $token->save() ? $token : null;
    }

    /**
     * Create a recovery token.
     */
    public function createRecoveryToken(User $user): ?Token
    {
        // Delete existing recovery tokens
        Token::deleteAllForUser($user->id, Token::TYPE_RECOVERY);

        $token = Token::createRecoveryToken($user);

        return $token->save() ? $token : null;
    }

    /**
     * Create an email change token.
     */
    public function createEmailChangeToken(User $user, string $newEmail): ?Token
    {
        // Delete existing email change tokens
        Token::deleteAllForUser($user->id, Token::TYPE_EMAIL_CHANGE);

        $token = Token::createEmailChangeToken($user, $newEmail);

        return $token->save() ? $token : null;
    }

    /**
     * Find and validate a confirmation token.
     */
    public function findConfirmationToken(string $tokenString): ?Token
    {
        return Token::findByToken($tokenString, Token::TYPE_CONFIRMATION);
    }

    /**
     * Find and validate a recovery token.
     */
    public function findRecoveryToken(string $tokenString): ?Token
    {
        return Token::findByToken($tokenString, Token::TYPE_RECOVERY);
    }

    /**
     * Find and validate an email change token.
     */
    public function findEmailChangeToken(string $tokenString): ?Token
    {
        return Token::findByToken($tokenString, Token::TYPE_EMAIL_CHANGE);
    }

    /**
     * Delete a token.
     */
    public function deleteToken(Token $token): bool
    {
        return $token->delete() !== false;
    }

    /**
     * Delete all tokens for a user.
     */
    public function deleteAllUserTokens(User $user): int
    {
        return Token::deleteAllForUser($user->id);
    }

    /**
     * Cleanup expired tokens.
     */
    public function cleanupExpiredTokens(): int
    {
        return Token::deleteExpired();
    }
}
