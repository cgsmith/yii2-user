<?php

declare(strict_types=1);

namespace cgsmith\user\models\query;

use cgsmith\user\models\TwoFactor;
use yii\db\ActiveQuery;

/**
 * Query class for TwoFactor model.
 *
 * @method TwoFactor|null one($db = null)
 * @method TwoFactor[] all($db = null)
 */
class TwoFactorQuery extends ActiveQuery
{
    /**
     * Filter by user ID.
     */
    public function byUser(int $userId): self
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    /**
     * Filter enabled only.
     */
    public function enabled(): self
    {
        return $this->andWhere(['not', ['enabled_at' => null]]);
    }
}
