<?php

declare(strict_types=1);

namespace cgsmith\user\models\query;

use cgsmith\user\models\Token;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Token query class.
 *
 * @method Token|null one($db = null)
 * @method Token[] all($db = null)
 */
class TokenQuery extends ActiveQuery
{
    /**
     * Filter by token type.
     */
    public function byType(string $type): static
    {
        return $this->andWhere(['type' => $type]);
    }

    /**
     * Filter by user ID.
     */
    public function byUserId(int $userId): static
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    /**
     * Filter by token string.
     */
    public function byToken(string $token): static
    {
        return $this->andWhere(['token' => $token]);
    }

    /**
     * Filter tokens that are not expired.
     */
    public function notExpired(): static
    {
        return $this->andWhere(['>', 'expires_at', new Expression('NOW()')]);
    }

    /**
     * Filter tokens that are expired.
     */
    public function expired(): static
    {
        return $this->andWhere(['<', 'expires_at', new Expression('NOW()')]);
    }

    /**
     * Filter confirmation tokens.
     */
    public function confirmation(): static
    {
        return $this->byType(Token::TYPE_CONFIRMATION);
    }

    /**
     * Filter recovery tokens.
     */
    public function recovery(): static
    {
        return $this->byType(Token::TYPE_RECOVERY);
    }

    /**
     * Filter email change tokens.
     */
    public function emailChange(): static
    {
        return $this->byType(Token::TYPE_EMAIL_CHANGE);
    }
}
