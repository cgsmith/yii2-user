<?php

declare(strict_types=1);

namespace cgsmith\user\events;

use cgsmith\user\models\User;
use yii\base\Event;

/**
 * GDPR-related event.
 */
class GdprEvent extends Event
{
    public const TYPE_CONSENT = 'consent';
    public const TYPE_WITHDRAW = 'withdraw';
    public const TYPE_EXPORT = 'export';
    public const TYPE_DELETE = 'delete';

    public ?User $user = null;
    public ?string $type = null;
    public ?string $consentVersion = null;
    public bool $marketingConsent = false;
}
