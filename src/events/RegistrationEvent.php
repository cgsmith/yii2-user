<?php

declare(strict_types=1);

namespace cgsmith\user\events;

use cgsmith\user\models\RegistrationForm;
use cgsmith\user\models\Token;
use cgsmith\user\models\User;
use yii\base\Event;

/**
 * Registration-related event.
 */
class RegistrationEvent extends Event
{
    /**
     * The user associated with this event.
     */
    public ?User $user = null;

    /**
     * The registration form.
     */
    public ?RegistrationForm $form = null;

    /**
     * The confirmation token (if applicable).
     */
    public ?Token $token = null;
}
