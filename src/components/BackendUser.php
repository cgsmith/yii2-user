<?php

declare(strict_types=1);

namespace cgsmith\user\components;

use Yii;
use yii\web\User;

/**
 * Backend user component with separate session handling.
 *
 * This component allows separate authentication states for frontend and backend.
 * Users can be logged in to the frontend without being logged in to the backend,
 * and vice versa.
 *
 * Usage in config:
 * ```php
 * 'components' => [
 *     'backendUser' => [
 *         'class' => \cgsmith\user\components\BackendUser::class,
 *         'identityClass' => 'app\models\User',
 *         'enableAutoLogin' => true,
 *         'identityCookie' => ['name' => '_backendIdentity', 'httpOnly' => true],
 *     ],
 * ],
 * ```
 */
class BackendUser extends User
{
    /**
     * @var string the session key used to store the backend user ID
     */
    public $idParam = '__backendId';

    /**
     * @var string the session key used to store the backend auth key
     */
    public $authKeyParam = '__backendAuthKey';

    /**
     * @var string the session key used to store the backend auth timeout
     */
    public $authTimeoutParam = '__backendAuthTimeout';

    /**
     * @var string the cookie name for backend auto login
     */
    public $identityCookie = ['name' => '_backendIdentity', 'httpOnly' => true];

    /**
     * Initializes the backend session if session separation is enabled.
     */
    public function init(): void
    {
        parent::init();

        $module = Yii::$app->getModule('user');
        if ($module !== null && $module->enableSessionSeparation) {
            $this->switchToBackendSession();
        }
    }

    /**
     * Switch to the backend session.
     */
    protected function switchToBackendSession(): void
    {
        $module = Yii::$app->getModule('user');
        if ($module === null) {
            return;
        }

        $session = Yii::$app->session;
        if (!$session->isActive) {
            $session->setName($module->backendSessionName);
        }
    }

    /**
     * Get the return URL for backend.
     */
    public function getReturnUrl($defaultUrl = null)
    {
        $url = Yii::$app->session->get($this->returnUrlParam, $defaultUrl);
        if (is_array($url)) {
            if (isset($url[0])) {
                return Yii::$app->urlManager->createUrl($url);
            }
            return Yii::$app->urlManager->createUrl(['']);
        }

        return $url === null ? Yii::$app->homeUrl : $url;
    }
}
