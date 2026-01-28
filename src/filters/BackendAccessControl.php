<?php

declare(strict_types=1);

namespace cgsmith\user\filters;

use Yii;
use yii\filters\AccessControl;
use yii\web\User;

/**
 * Access control filter for backend with session separation support.
 *
 * This filter uses the backendUser component instead of the default user component
 * when session separation is enabled.
 *
 * Usage in controller:
 * ```php
 * public function behaviors(): array
 * {
 *     return [
 *         'access' => [
 *             'class' => BackendAccessControl::class,
 *             'rules' => [
 *                 ['allow' => true, 'roles' => ['@']],
 *             ],
 *         ],
 *     ];
 * }
 * ```
 */
class BackendAccessControl extends AccessControl
{
    /**
     * @var User|string|null the user component to use for access checking.
     * If null, will use 'backendUser' if available, otherwise 'user'.
     */
    public $user = null;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        if ($this->user === null) {
            $module = Yii::$app->getModule('user');

            if ($module !== null && $module->enableSessionSeparation && Yii::$app->has('backendUser')) {
                $this->user = Yii::$app->get('backendUser');
            } else {
                $this->user = Yii::$app->user;
            }
        }

        parent::init();
    }

    /**
     * Check if the current user can access the action.
     */
    protected function isActive($action): bool
    {
        if ($this->user instanceof User) {
            return parent::isActive($action);
        }

        if (is_string($this->user)) {
            $this->user = Yii::$app->get($this->user);
        }

        return parent::isActive($action);
    }
}
