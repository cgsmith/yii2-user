<?php

declare(strict_types=1);

namespace cgsmith\user\models\query;

use cgsmith\user\models\SocialAccount;
use yii\db\ActiveQuery;

/**
 * Query class for SocialAccount model.
 *
 * @method SocialAccount|null one($db = null)
 * @method SocialAccount[] all($db = null)
 */
class SocialAccountQuery extends ActiveQuery
{
    /**
     * Filter by user ID.
     */
    public function byUser(int $userId): self
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    /**
     * Filter by provider.
     */
    public function byProvider(string $provider): self
    {
        return $this->andWhere(['provider' => $provider]);
    }

    /**
     * Filter by provider ID.
     */
    public function byProviderId(string $providerId): self
    {
        return $this->andWhere(['provider_id' => $providerId]);
    }

    /**
     * Filter connected accounts only.
     */
    public function connected(): self
    {
        return $this->andWhere(['not', ['user_id' => null]]);
    }

    /**
     * Filter unconnected accounts only.
     */
    public function unconnected(): self
    {
        return $this->andWhere(['user_id' => null]);
    }
}
