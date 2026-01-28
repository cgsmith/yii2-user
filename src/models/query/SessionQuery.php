<?php

declare(strict_types=1);

namespace cgsmith\user\models\query;

use cgsmith\user\models\Session;
use yii\db\ActiveQuery;

/**
 * Query class for Session model.
 *
 * @method Session|null one($db = null)
 * @method Session[] all($db = null)
 */
class SessionQuery extends ActiveQuery
{
    /**
     * Filter by user ID.
     */
    public function byUser(int $userId): self
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    /**
     * Filter by session ID.
     */
    public function bySessionId(string $sessionId): self
    {
        return $this->andWhere(['session_id' => $sessionId]);
    }

    /**
     * Order by last activity descending.
     */
    public function latestFirst(): self
    {
        return $this->orderBy(['last_activity_at' => SORT_DESC]);
    }

    /**
     * Filter sessions active within the given time period.
     */
    public function activeWithin(int $seconds): self
    {
        return $this->andWhere(['>=', 'last_activity_at', date('Y-m-d H:i:s', time() - $seconds)]);
    }
}
