<?php

declare(strict_types=1);

namespace cgsmith\user\models\query;

use cgsmith\user\models\User;
use yii\db\ActiveQuery;

/**
 * User query class.
 *
 * @method User|null one($db = null)
 * @method User[] all($db = null)
 */
class UserQuery extends ActiveQuery
{
    /**
     * Filter by active status (not blocked, not soft deleted).
     */
    public function active(): static
    {
        return $this
            ->andWhere(['!=', 'status', User::STATUS_BLOCKED])
            ->andWhere(['gdpr_deleted_at' => null]);
    }

    /**
     * Filter by confirmed users.
     */
    public function confirmed(): static
    {
        return $this->andWhere(['not', ['email_confirmed_at' => null]]);
    }

    /**
     * Filter by unconfirmed users.
     */
    public function unconfirmed(): static
    {
        return $this->andWhere(['email_confirmed_at' => null]);
    }

    /**
     * Filter by blocked users.
     */
    public function blocked(): static
    {
        return $this->andWhere(['status' => User::STATUS_BLOCKED]);
    }

    /**
     * Filter by pending users.
     */
    public function pending(): static
    {
        return $this->andWhere(['status' => User::STATUS_PENDING]);
    }

    /**
     * Filter users that can log in.
     */
    public function canLogin(): static
    {
        return $this
            ->active()
            ->confirmed();
    }

    /**
     * Filter by email.
     */
    public function byEmail(string $email): static
    {
        return $this->andWhere(['email' => $email]);
    }

    /**
     * Filter by username.
     */
    public function byUsername(string $username): static
    {
        return $this->andWhere(['username' => $username]);
    }
}
