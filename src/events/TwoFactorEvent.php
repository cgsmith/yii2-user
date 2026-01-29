<?php

declare(strict_types=1);

namespace cgsmith\user\events;

use cgsmith\user\models\User;
use yii\base\Event;

/**
 * Two-factor authentication event.
 */
class TwoFactorEvent extends Event
{
    public const TYPE_ENABLED = 'enabled';
    public const TYPE_DISABLED = 'disabled';
    public const TYPE_VERIFIED = 'verified';
    public const TYPE_BACKUP_USED = 'backup_used';

    public ?User $user = null;
    public ?string $type = null;
}
