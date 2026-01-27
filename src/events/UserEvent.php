<?php

declare(strict_types=1);

namespace cgsmith\user\events;

use cgsmith\user\models\User;
use yii\base\Event;

/**
 * User-related event.
 */
class UserEvent extends Event
{
    /**
     * The user associated with this event.
     */
    public ?User $user = null;
}
