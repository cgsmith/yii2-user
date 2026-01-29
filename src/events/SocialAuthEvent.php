<?php

declare(strict_types=1);

namespace cgsmith\user\events;

use cgsmith\user\models\SocialAccount;
use cgsmith\user\models\User;
use yii\authclient\ClientInterface;
use yii\base\Event;

/**
 * Social authentication event.
 */
class SocialAuthEvent extends Event
{
    public const TYPE_LOGIN = 'login';
    public const TYPE_REGISTER = 'register';
    public const TYPE_CONNECT = 'connect';
    public const TYPE_DISCONNECT = 'disconnect';

    public ?User $user = null;
    public ?SocialAccount $account = null;
    public ?ClientInterface $client = null;
    public ?string $type = null;
}
