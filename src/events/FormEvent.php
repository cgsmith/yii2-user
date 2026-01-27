<?php

declare(strict_types=1);

namespace cgsmith\user\events;

use yii\base\Event;
use yii\base\Model;

/**
 * Form-related event.
 */
class FormEvent extends Event
{
    /**
     * The form model associated with this event.
     */
    public ?Model $form = null;
}
