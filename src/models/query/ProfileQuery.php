<?php

declare(strict_types=1);

namespace cgsmith\user\models\query;

use cgsmith\user\models\Profile;
use yii\db\ActiveQuery;

/**
 * Profile query class.
 *
 * @method Profile|null one($db = null)
 * @method Profile[] all($db = null)
 */
class ProfileQuery extends ActiveQuery
{
    /**
     * Filter by user ID.
     */
    public function byUserId(int $userId): static
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    /**
     * Filter profiles with avatar.
     */
    public function withAvatar(): static
    {
        return $this->andWhere(['not', ['avatar_path' => null]]);
    }

    /**
     * Filter profiles using gravatar.
     */
    public function usingGravatar(): static
    {
        return $this->andWhere(['use_gravatar' => true]);
    }
}
