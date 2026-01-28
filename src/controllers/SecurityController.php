<?php

declare(strict_types=1);

namespace cgsmith\user\controllers;

use cgsmith\user\events\FormEvent;
use cgsmith\user\models\LoginForm;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use cgsmith\user\services\SessionService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Security controller for login/logout.
 */
class SecurityController extends Controller
{
    public const EVENT_BEFORE_LOGIN = 'beforeLogin';
    public const EVENT_AFTER_LOGIN = 'afterLogin';
    public const EVENT_BEFORE_LOGOUT = 'beforeLogout';
    public const EVENT_AFTER_LOGOUT = 'afterLogout';

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['login'], 'roles' => ['?']],
                    ['allow' => true, 'actions' => ['login', 'logout'], 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Display login page and handle login.
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        /** @var Module $module */
        $module = $this->module;

        /** @var LoginForm $model */
        $model = $module->createModel('LoginForm');

        // Handle AJAX validation
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return $this->asJson(\yii\widgets\ActiveForm::validate($model));
        }

        // Trigger before login event
        $event = new FormEvent(['form' => $model]);
        $module->trigger(self::EVENT_BEFORE_LOGIN, $event);

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // Track session
            if ($module->enableSessionHistory) {
                /** @var SessionService $sessionService */
                $sessionService = Yii::$container->get(SessionService::class);
                $sessionService->trackSession(Yii::$app->user->identity);
            }

            // Trigger after login event
            $event = new FormEvent(['form' => $model]);
            $module->trigger(self::EVENT_AFTER_LOGIN, $event);

            return $this->goBack();
        }

        return $this->render('login', [
            'model' => $model,
            'module' => $module,
        ]);
    }

    /**
     * Logout user.
     */
    public function actionLogout(): Response
    {
        /** @var Module $module */
        $module = $this->module;

        /** @var User|null $user */
        $user = Yii::$app->user->identity;

        // Trigger before logout event
        $event = new FormEvent(['form' => null]);
        $module->trigger(self::EVENT_BEFORE_LOGOUT, $event);

        // Remove session tracking
        if ($module->enableSessionHistory && $user !== null) {
            /** @var SessionService $sessionService */
            $sessionService = Yii::$container->get(SessionService::class);
            $sessionService->removeCurrentSession($user);
        }

        Yii::$app->user->logout();

        // Trigger after logout event
        $event = new FormEvent(['form' => null]);
        $module->trigger(self::EVENT_AFTER_LOGOUT, $event);

        return $this->goHome();
    }
}
